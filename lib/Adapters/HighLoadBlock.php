<?php

namespace Tio\Import\Adapters;

use Bitrix\Highloadblock\HighloadBlockTable;
use CModule;

CModule::IncludeModule('highloadblock');

abstract class HighLoadBlock extends Block
{
    public static function get(array $query = [], callable $callback = null): array
    {
        $table = static::getTable();
        $dbList = $table::getList($query);

        $callback = !is_null($callback) ? $callback : function ($res) {
            return $res;
        };

        $result = [];
        while ($res = $dbList->fetch()) {
            $result[] = $callback($res);
        }

        return $result;
    }

    public static function update(int $id, array $arFields): bool
    {
        $table = static::getTable();
        $result = boolval($table::update($id, $arFields));

        if ($result) {
            static::clearCache();
        }

        return $result;
    }

    public static function add(array $arFields): int
    {
        $table = static::getTable();

        $result = (int)$table::add($arFields)->getId();
        if ($result > 0) {
            static::clearCache();
        }

        return $result;
    }

    public static function delete(int $id): bool
    {
        $table = static::getTable();
        $result = boolval($table::delete($id));

        if ($result) {
            static::clearCache();
        }

        return $result;
    }

    protected static function getTable(): string
    {
        return HighloadBlockTable::compileEntity(HighloadBlockTable::getById(static::getStoreId())->fetch())->getDataClass();
    }

    protected static function clearCache()
    {
        CacheAdapter::clearByTag("highloadblock_" . static::getStoreId());
    }
}