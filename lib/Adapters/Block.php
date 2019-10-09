<?php

namespace Tio\Import\Adapters;

abstract class Block
{
    /**
     * @return int
     */
    protected abstract static function getStoreId(): int;

    public abstract static function get(array $query = [], callable $callback = null): array;

    public abstract static function update(int $id, array $arFields): bool;

    public abstract static function add(array $arFields): int;

    public abstract static function delete(int $id): bool;

    public static function find(int $id): ?array
    {
        $result = current(static::get(["filter" => ["ID" => $id]]));
        if (!is_array($result) || empty($result)) {
            return null;
        }

        return $result;
    }

    public static function first(array $query = [], callable $callback = null): ?array
    {
        $result = current(static::get($query, $callback));

        if (!is_array($result) || empty($result)) {
            return null;
        }

        return $result;
    }
}
