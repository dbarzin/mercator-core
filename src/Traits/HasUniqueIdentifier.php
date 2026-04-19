<?php

namespace Mercator\Core\Traits;

trait HasUniqueIdentifier
{
    public function getPrefix(): string
    {
        return self::$prefix;
    }

    public function getUID(): string
    {
        return self::$prefix . $this->id;
    }

}
