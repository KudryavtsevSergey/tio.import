<?
use Tio\Import\Tools;

require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule("tio.import");

(new Tools())->importBelOrientir();
