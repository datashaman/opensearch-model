<?php

namespace Datashaman\OpenSearch\Model\Tests;

use Datashaman\OpenSearch\Model\OpenSearchModel;
use OpenSearch\Client;

class ProxyTestModel
{
    use OpenSearchModel;
    protected static $opensearch;
}

class ProxyTestModelWithProperties
{
    use OpenSearchModel;
    protected static $opensearch;

    public static $indexName = 'foo';
}

class ProxyTest extends TestCase
{
    public function testGetClient()
    {
        $this->assertInstanceOf(Client::class, ProxyTestModel::opensearch()->client());
    }

    public function testSetClient()
    {
        ProxyTestModel::opensearch()->client('foobar');
        $this->assertSame('foobar', ProxyTestModel::opensearch()->client());
    }

    public function testGetDocumentType()
    {
        $this->assertEquals('proxy-test-model', ProxyTestModel::documentType());
    }

    public function testSetDocumentType()
    {
        ProxyTestModel::documentType('thingybob');
        $this->assertEquals('thingybob', ProxyTestModel::documentType());
    }

    public function testGetIndexName()
    {
        $this->assertEquals('proxy-test-models', ProxyTestModel::indexName());
    }

    public function testSetIndexName()
    {
        ProxyTestModel::indexName('thingybobs');
        $this->assertEquals('thingybobs', ProxyTestModel::indexName());
    }

    public function testGetIndexNameWithProperty()
    {
        $this->assertEquals('foo', ProxyTestModelWithProperties::indexName());
    }

    public function testGetDocumentTypeWithProperty()
    {
        $this->assertEquals('bar', ProxyTestModelWithProperties::documentType());
    }
}
