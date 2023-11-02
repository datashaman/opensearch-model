<?php

namespace Datashaman\OpenSearch\Model;

class OpenSearch extends GetOrSet
{
    use Importing;
    use Indexing;
    use Searching;

    protected $class;

    public function __construct($class)
    {
        $name = preg_replace('/([A-Z])/', ' \1', class_basename($class));

        $attributes = [
            'client' => app('opensearch'),
            'documentType' => isset($class::$documentType) ? $class::$documentType : str_slug($name),
            'indexName' => isset($class::$indexName) ? $class::$indexName : str_slug(str_plural($name)),
        ];

        parent::__construct($attributes);

        $this->class = $class;
    }
}

trait OpenSearchModel
{
    use Serializing;

    public static function resetOpenSearch()
    {
        static::$opensearch = null;
    }

    public static function opensearch()
    {
        $args = func_get_args();

        if (count($args) == 0) {
            if (empty(static::$opensearch)) {
                static::$opensearch = new OpenSearch(static::class);
            }

            return static::$opensearch;
        }

        static::$opensearch = $args[0];

        return static::$opensearch;
    }

    public static function search($query, $options = [])
    {
        return static::opensearch()->search($query, $options);
    }

    public static function mappings($options = [], callable $callable = null)
    {
        return static::opensearch()->mappings($options, $callable);
    }

    public static function settings($settings = [], callable $callable = null)
    {
        return static::opensearch()->settings($settings, $callable);
    }

    public static function indexName()
    {
        $result = static::getOrSet('indexName', func_get_args());

        return $result;
    }

    public static function documentType()
    {
        return static::getOrSet('documentType', func_get_args());
    }

    public static function getDocument($primaryKey, $options = [])
    {
        $options = static::instanceOptions($primaryKey, $options);

        return static::opensearch()->client()->get($options);
    }

    public function indexDocument($options = [])
    {
        $options = static::instanceOptions($this->id, $options);
        $options['body'] = $this->toIndexedArray();

        return static::opensearch()->client()->index($options);
    }

    public function deleteDocument($options = [])
    {
        $options = static::instanceOptions($this->id, $options);

        return static::opensearch()->client()->delete($options);
    }

    public function updateDocument($options = [])
    {
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return $this->indexDocument($options);
        }

        $doc = array_only($this->toIndexedArray(), array_keys($dirty));
        $options = static::instanceOptions($this->id);
        $options['body'] = compact('doc');

        return static::opensearch()->client()->update($options);
    }

    public function updateDocumentAttributes($doc, $options = [])
    {
        $options = array_merge($options, static::instanceOptions($this->id));
        $options['body'] = compact('doc');

        return static::opensearch()->client()->update($options);
    }

    protected static function getOrSet($name, $args)
    {
        if (count($args) == 0) {
            return static::opensearch()->$name();
        }

        return static::opensearch()->$name($args[0]);
    }

    public static function instanceOptions($id, $options = [])
    {
        $options = array_merge([
            'index' => static::indexName(),
        ], $options);

        if (! empty($id)) {
            $options['id'] = $id;
        }

        return $options;
    }

    public function records($response, $options = [], callable $callable = null)
    {
        $ids = $response->ids();

        $class = $this->class;
        $builder = $class::whereIn('id', $ids);

        if (array_has($options, 'with')) {
            call_user_func_array([$builder, 'with'], $options['with']);
        }

        if (is_callable($callable)) {
            call_user_func($callable, $builder);
        }

        if (empty($builder->getQuery()->orders)) {
            /*
            # Only MySQL can use this, unfortunately.
            $idStrings = $ids->map(function ($id) { return "'$id'"; })->implode(', ');
            $records = $records->orderByRaw("find_in_set(id, $idStrings)")->get();
            */

            return $builder->get()->sortBy(function ($record) use ($ids) {
                return $ids->search($record->id);
            })->values();
        }

        return $builder->get();
    }

    public function findInChunks($options = [], callable $callable = null)
    {
        $query = array_pull($options, 'query');
        $scope = array_pull($options, 'scope');
        $preprocess = array_pull($options, 'preprocess');
        $chunkSize = array_pull($options, 'chunkSize', 1000);

        $class = $this->class;
        $builder = empty($scope) ? (new $class)->newQuery() : $class::$scope();

        if (! empty($query)) {
            call_user_func($query, $builder);
        }

        $builder->chunk($chunkSize, function ($chunk) use ($preprocess, $callable, $class) {
            if (! empty($preprocess)) {
                $chunk = call_user_func([$class, $preprocess], $chunk);
            }

            call_user_func($callable, $chunk);
        });
    }
}
