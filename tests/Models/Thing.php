<?php

namespace Datashaman\OpenSearch\Model\Tests\Models;

use Datashaman\OpenSearch\Model\OpenSearchModel;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Thing extends Eloquent
{
    use OpenSearchModel;
    protected static $opensearch;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function perPage()
    {
        return 33;
    }

    public function scopeOnline($query)
    {
        return $query->whereStatus('online');
    }

    public static function enrich($chunk)
    {
        return $chunk->map(function ($thing) {
            $thing->title .= '!';

            return $thing;
        });
    }
}
