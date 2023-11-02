<?php

namespace Datashaman\OpenSearch\Model\Tests;

use OpenSearch\Client;
use Datashaman\OpenSearch\Model\OpenSearchModel;
use Datashaman\OpenSearch\Model\SearchRequest;
use Mockery as m;

class SearchRequestTestModel
{
    use OpenSearchModel;
    protected static $opensearch;

    public static $indexName = 'foo';
}

class SearchRequestQueryBuilder
{
    public function toArray()
    {
        return ['foo' => 'bar'];
    }
}

/**
 * @group passing
 */
class SearchRequestTest extends TestCase
{
    public function testSimpleQuery()
    {
        $client = SearchRequestTestModel::opensearch()->client(
            m::mock(Client::class)
                ->shouldReceive('search')
                ->with([
                    'index' => 'foo',
                    'type' => 'bar',
                    'body' => [
                        'query' => [
                            'query_string' => [
                                'query' => 'foo',
                            ],
                        ],
                    ],
                ])
                ->mock()
        );

        $search = new SearchRequest(SearchRequestTestModel::class, 'foo');
        $search->execute();
    }

    public function testArray()
    {
        $client = SearchRequestTestModel::opensearch()->client(
            m::mock(Client::class)
                ->shouldReceive('search')
                ->with([
                    'index' => 'foo',
                    'type' => 'bar',
                    'body' => [
                        'foo' => 'bar',
                    ],
                ])
                ->mock()
        );

        $search = new SearchRequest(SearchRequestTestModel::class, ['foo' => 'bar']);
        $search->execute();
    }

    public function testJsonString()
    {
        $client = SearchRequestTestModel::opensearch()->client(
            m::mock(Client::class)
                ->shouldReceive('search')
                ->with([
                    'index' => 'foo',
                    'type' => 'bar',
                    'body' => '{"foo":"bar"}',
                ])
                ->mock()
        );

        $search = new SearchRequest(SearchRequestTestModel::class, '{"foo":"bar"}');
        $search->execute();
    }

    public function testToArray()
    {
        $client = SearchRequestTestModel::opensearch()->client(
            m::mock(Client::class)
                ->shouldReceive('search')
                ->with([
                    'index' => 'foo',
                    'type' => 'bar',
                    'body' => [
                        'foo' => 'bar',
                    ],
                ])
                ->mock()
        );

        $builder = new SearchRequestQueryBuilder();
        $search = new SearchRequest(SearchRequestTestModel::class, $builder);
        $search->execute();
    }

    public function testPassOptionsToClient()
    {
        $client = SearchRequestTestModel::opensearch()->client(
            m::mock(Client::class)
                ->shouldReceive('search')
                ->with([
                    'index' => 'foo',
                    'type' => 'bar',
                    'body' => [
                        'query' => [
                            'query_string' => [
                                'query' => 'foo',
                            ],
                        ],
                    ],
                    'from' => 33,
                    'size' => 33,
                ])
                ->mock()
        );

        $search = new SearchRequest(SearchRequestTestModel::class, 'foo', ['from' => 33, 'size' => 33]);
        $search->execute();
    }
}
