<?php

namespace Datashaman\OpenSearch\Model;

trait Serializing
{
    public function toIndexedArray()
    {
        return $this->toArray();
    }
}
