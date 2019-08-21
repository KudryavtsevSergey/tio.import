<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;

$module_id = "tio.import";
$options = Option::getForModule($module_id);
$groups = [
    /*[
        'GROUP' => 'BASE',
        'OPTIONS' => [
            ['NAME' => 'OPTION_NAME1', 'TYPE' => 'TEXT', 'DEFAULT' => 'default_text'],
            ['NAME' => 'OPTION_NAME2', 'TYPE' => 'SELECT', 'VALUES' => [
                'val1' => 'text1',
                'val2' => 'text2',
                'val3' => 'text3'
            ]],
        ]
    ],
    [
        'GROUP' => 'ADDITIONS',
        'OPTIONS' => [
            ['NAME' => 'OPTION_NAME3', 'TYPE' => 'TEXT', 'DEFAULT' => ''],
        ]
    ]*/
];

$groups = array_map(function ($group) use ($options) {
    $group['OPTIONS'] = array_map(function ($option) use ($options) {
        $option['VALUE'] = $options[$option['NAME']] ?? $option['DEFAULT'] ?? '';

        return $option;
    }, $group['OPTIONS']);

    return $group;
}, $groups);

return $groups;
