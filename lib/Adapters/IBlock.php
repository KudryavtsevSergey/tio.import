<?php

namespace Tio\Import\Adapters;

use _CIBElement;
use CIBlockElement;
use CModule;

CModule::IncludeModule('iblock');

abstract class IBlock extends Block
{
    public static function get(array $query = [], callable $callback = null): array
    {
        $arOrder = $query['order'] ?? ["SORT" => "ASC"];

        $arFilter = $query["filter"] ?? [];
        $arFilter["IBLOCK_ID"] = static::getStoreId();

        $arNav = $query['nav'] ?? false;

        $arSelect = $query['select'] ?? [];

        $dbList = CIBlockElement::GetList($arOrder, $arFilter, null, $arNav, $arSelect);

        $callback = !is_null($callback) ? $callback : function (_CIBElement $dbElement) {
            $arFields = $dbElement->GetFields();
            $arProperties = $dbElement->GetProperties();

            return array_merge($arFields, ["PROPERTIES" => $arProperties]);
        };

        $result = [];
        while ($dbElement = $dbList->GetNextElement()) {
            $result[] = $callback($dbElement);
        }

        return $result;
    }

    public static function update(int $id, array $arFields): bool
    {
        $ob = new CIBlockElement;

        return boolval($ob->Update($id, $arFields));
    }

    public static function updateProperties(int $id, array $arFields)
    {
        CIBlockElement::SetPropertyValuesEx($id, static::getStoreId(), $arFields);
    }

    public static function add(array $arFields): int
    {
        $ob = new CIBlockElement;
        $arFields['IBLOCK_ID'] = static::getStoreId();
        return (int)$ob->Add($arFields);
    }

    public static function delete(int $id): bool
    {
        $ob = new CIBlockElement;
        return boolval($ob->Delete($id));
    }
}
