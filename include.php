<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$classes = [
    "Tio\\Import\\Gateway\\BusGateway" => "lib/Gateway/BusGateway.php",
    "Tio\\Import\\Gateway\\BelOrientirGateway" => "lib/Gateway/BelOrientirGateway.php",
    "Tio\\Import\\Tools" => "lib/Tools.php",
    "Tio\\Import\\Adapters\\CacheAdapter" => "lib/Adapters/CacheAdapter.php",
    "Tio\\Import\\Import\\Import" => "lib/Import/Import.php",
    "Tio\\Import\\Import\\BelOrientirImport" => "lib/Import/BelOrientirImport.php",
    "Tio\\Import\\Export\\Export" => "lib/Export/Export.php",
    "Tio\\Import\\Export\\IBlockExport" => "lib/Export/IBlockExport.php",

    "Tio\\Import\\Adapters\\Block" => "lib/Adapters/Block.php",
    "Tio\\Import\\Adapters\\HighLoadBlock" => "lib/Adapters/HighLoadBlock.php",
    "Tio\\Import\\Adapters\\IBlock" => "lib/Adapters/IBlock.php",

    "Tio\\Import\\Store\\BusTour" => "lib/Store/BusTour.php",
    "Tio\\Import\\Store\\HLPrice" => "lib/Store/HLPrice.php",
];

CModule::AddAutoloadClasses("tio.import", $classes);
