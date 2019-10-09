<?php

namespace Tio\Import\Store;

use Tio\Import\Adapters\IBlock;

class BusTour extends IBlock
{
    protected static function getStoreId(): int
    {
        return 43;
    }
}