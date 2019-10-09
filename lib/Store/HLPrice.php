<?php

namespace Tio\Import\Store;

use Tio\Import\Adapters\HighLoadBlock;

class HLPrice extends HighLoadBlock
{
    protected static function getStoreId(): int
    {
        return 22;
    }
}