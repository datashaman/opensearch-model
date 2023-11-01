<?php

namespace Datashaman\OpenSearch\Model\Tests;

use Datashaman\OpenSearch\Model\OpenSearch;
use Datashaman\OpenSearch\Model\Tests\TestCase;
use Datashaman\OpenSearch\Model\Tests\Models\Thing;
use Mockery as m;

class OpenSearchModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createThings();
    }

    public function testRecordsWith()
    {
        $elastic = m::mock(OpenSearch::class, [Thing::class], [
            'client' => m::mock([
                'search' => [
                    'hits' => [
                        'total' => 1,
                        'hits' => [[
                            '_id' => 1,
                            '_source' => [
                                'id' => 1,
                                'title' => 'Existing Thing',
                            ],
                        ]],
                    ],
                ],
            ]),
            'indexName' => 'things',
            'documentType' => 'thing',
        ])->shouldDeferMissing();

        Thing::opensearch($elastic);

        $response = Thing::search('*');
        $records = $response->records(['with' => ['category']]);

        $this->assertTrue($records[0]->relationLoaded('category'));
    }

    public function testReorderRecordsBasedOnHits()
    {
        $elastic = m::mock(OpenSearch::class, [Thing::class], [
            'client' => m::mock([
                'search' => [
                    'hits' => [
                        'total' => 1,
                        'hits' => [[
                            '_id' => 2,
                            '_source' => [
                                'id' => 2,
                                'title' => 'Thing Thing',
                            ],
                        ], [
                            '_id' => 1,
                            '_source' => [
                                'id' => 1,
                                'title' => 'Existing Thing',
                            ],
                        ]],
                    ],
                ],
            ]),
            'indexName' => 'things',
            'documentType' => 'thing',
        ])->shouldDeferMissing();

        Thing::opensearch($elastic);

        $thing = Thing::create([
            'title' => 'Thing Thing',
            'category_id' => 1,
        ]);

        $this->assertEquals([1, 2, 3], Thing::query()->pluck('id')->all());

        $response = Thing::search('Thing');
        $this->assertEquals([2, 1], $response->getCollection()->map(function ($r) {
            return $r->id;
        })->all());

        $records = $response->records();
        $this->assertEquals([2, 1], $records->map(function ($r) {
            return $r->id;
        })->all());
    }

    public function testNotReorderWhenOrderingIsPresent()
    {
        $elastic = m::mock(OpenSearch::class, [Thing::class], [
            'client' => m::mock([
                'search' => [
                    'hits' => [
                        'total' => 1,
                        'hits' => [[
                            '_id' => 2,
                            '_source' => [
                                'id' => 2,
                                'title' => 'Thing Thing',
                            ],
                        ], [
                            '_id' => 1,
                            '_source' => [
                                'id' => 1,
                                'title' => 'Existing Thing',
                            ],
                        ]],
                    ],
                ],
            ]),
            'indexName' => 'things',
            'documentType' => 'thing',
        ])->shouldDeferMissing();

        Thing::opensearch($elastic);

        $thing = Thing::create([
            'title' => 'Thing Thing',
            'category_id' => 1,
        ]);

        $this->assertEquals([1, 2, 3], Thing::query()->pluck('id')->all());

        $response = Thing::search('Thing');
        $this->assertEquals([2, 1], $response->ids()->all());

        $records = $response->records([], function ($query) {
            $query->orderBy('id');
        });

        $this->assertEquals([1, 2], $records->map(function ($r) {
            return $r->id;
        })->all());
    }

    public function testLimitToASpecificScope()
    {
        Thing::opensearch()->findInChunks(['scope' => 'online'], function ($chunk) {
            $this->assertCount(1, $chunk);
            $this->assertEquals('online', $chunk[0]->status);
        });
    }

    public function testLimitToASpecificQuery()
    {
        Thing::opensearch()->findInChunks(['query' => function ($q) {
            $q->whereStatus('online');
        }], function ($chunk) {
            $this->assertCount(1, $chunk);
            $this->assertEquals('online', $chunk[0]->status);
        });
    }

    public function testPreprocessIfProvided()
    {
        Thing::opensearch()->findInChunks(['preprocess' => 'enrich'], function ($chunk) {
            $chunk->each(function ($thing) {
                $this->assertEquals('!', substr($thing->title, -1));
            });
        });
    }
}
