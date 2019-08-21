<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!$USER->isAdmin())
    return;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$module_id = "tio.import";
$groups = require __DIR__ . '/install/options.php';

$request = Application::getInstance()->getContext()->getRequest();

if (check_bitrix_sessid() && $request->isPost() && $request->get('step') == 'register') {

    foreach ($groups as $group) {
        foreach ($group['OPTIONS'] as $option) {
            Option::set($module_id, $option['NAME'], $request->get($option['NAME']) ?? $option['DEFAULT'] ?? '');
        }
    }

    $_SESSION[$module_id] = 'Y';
    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($module_id) . "&lang=" . urlencode(LANGUAGE_ID));
}

require_once __DIR__ . '/install/step.php';
