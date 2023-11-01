<?php

namespace Datashaman\OpenSearch\Model;

use OpenSearch\ClientBuilder;

class ClientFactory
{
    public static function make($app)
    {
        $config = array_get(
            $app['config'],
            'elasticsearch',
            [
                'hosts' => '127.0.0.1:9200',
            ]
        );

        return ClientBuilder::fromConfig($config, true);
    }
}
