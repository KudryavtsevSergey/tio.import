<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$classes = [
    /*    "travelsoft\\bx24customizer\\EventsHandlers" => "lib/EventsHandlers.php",
        "travelsoft\\bx24customizer\\Tools" => "lib/Tools.php",
        "travelsoft\\bx24customizer\\Cache" => "lib/Cache.php",
        "travelsoft\\bx24customizer\\mastertour\\Gateway" => "lib/mastertour/Gateway.php",
        "travelsoft\\bx24customizer\\Fields" => "lib/Fields.php",

        "travelsoft\\bx24customizer\\stores\\Country" => "lib/stores/Country.php",
        "travelsoft\\bx24customizer\\stores\\Resort" => "lib/stores/Resort.php",
        "travelsoft\\bx24customizer\\stores\\Food" => "lib/stores/Food.php",
        "travelsoft\\bx24customizer\\stores\\AdvertisingSources" => "lib/stores/AdvertisingSources.php",
        "travelsoft\\bx24customizer\\stores\\TourType" => "lib/stores/TourType.php",

        "travelsoft\\bx24customizer\\adapters\\Highloadblock" => "lib/adapters/Highloadblock.php",
        "travelsoft\\bx24customizer\\adapters\\Iblock" => "lib/adapters/Iblock.php",
        "travelsoft\\bx24customizer\\adapters\\Store" => "lib/adapters/Store.php",

        "travelsoft\\bx24customizer\\traits\\MasterTourRelatedStores" => "lib/traits/MasterTourRelatedStores.php",*/
];

CModule::AddAutoloadClasses("tio.import", $classes);
