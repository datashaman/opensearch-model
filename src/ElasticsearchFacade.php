<?php

namespace Datashaman\OpenSearch\Model;

use Illuminate\Support\Facades\Facade;

class OpenSearchFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticsearch';
    }
}
