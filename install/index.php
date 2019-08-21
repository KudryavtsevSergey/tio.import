<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\HttpRequest;

Loc::loadMessages(__FILE__);

class Tio_Import extends CModule
{
    public $MODULE_NAME = 'Модуль импорта';
    public $MODULE_DESCRIPTION = 'Модуль импорта';
    //public $MODULE_VERSION;
    //public $MODULE_VERSION_DATE;
    public $MODULE_ID = "tio.import";
    //var $MODULE_SORT = 10000;
    //var $SHOW_SUPER_ADMIN_GROUP_RIGHTS;
    public $MODULE_GROUP_RIGHTS = "N";
    public $PARTNER_NAME = "travelsoft";
    public $PARTNER_URI = "";

    protected $namespaceFolder = "travelsoft";
    protected $components = [
        //"currencyswitch"
    ];

    function __construct()
    {
        $arModuleVersion = include_once __DIR__ . "/version.php";
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
    }

    public function DoInstall()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        try {
            if (check_bitrix_sessid() && $request->isPost() && $request->get('step') == 'register') {
                RegisterModule($this->MODULE_ID);
                $this->setOptions($request);
                $this->copyFiles();
            } else {
                global $APPLICATION;
                $APPLICATION->IncludeAdminFile("Установка модуля {$this->MODULE_ID}", __DIR__ . "/step.php");
            }
        } catch (Exception $ex) {
            $this->DoUninstall();
        }
    }

    public function DoUninstall()
    {
        $this->unsetOptions();
        $this->deleteFiles();
        UnRegisterModule($this->MODULE_ID);

        global $APPLICATION;
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля {$this->MODULE_ID}", __DIR__ . "/unstep.php");
    }

    private function copyFiles()
    {
        array_walk($this->components, function (string $component) {
            CopyDirFiles("{$_SERVER["DOCUMENT_ROOT"]}/local/modules/{$this->MODULE_ID}/install/components/{$this->namespaceFolder}/{$component}", "{$_SERVER["DOCUMENT_ROOT"]}/local/components/{$this->namespaceFolder}/{$component}", true, true);
        });
    }

    private function deleteFiles()
    {
        array_walk($this->components, function (string $component) {
            DeleteDirFilesEx("{$_SERVER["DOCUMENT_ROOT"]}/local/components/{$this->namespaceFolder}/{$component}");
        });

        if (!glob("{$_SERVER["DOCUMENT_ROOT"]}/local/components/{$this->namespaceFolder}/*")) {
            DeleteDirFilesEx("{$_SERVER["DOCUMENT_ROOT"]}/local/components/{$this->namespaceFolder}");
        }
    }

    private function getOptions()
    {
        $groups = require __DIR__ . '/options.php';

        if (empty($groups))
            return [];

        $options = array_column($groups, 'OPTIONS');

        if (empty($options))
            return [];

        return call_user_func_array('array_merge', $options);
    }

    /**
     * @param HttpRequest $request
     */
    private function setOptions(HttpRequest $request)
    {
        $options = $this->getOptions();

        array_walk($options, function (array $option) use ($request) {
            $value = $request->get($option['NAME']) ?? $option['DEFAULT'] ?? '';

            $this->setOption($option['NAME'], $value);
        });
    }

    private function unsetOptions()
    {
        $options = $this->getOptions();

        array_walk($options, function (array $option) {
            $this->unsetOption($option['NAME']);
        });
    }

    private function setOption($name, $value)
    {
        Option::set($this->MODULE_ID, $name, $value);
    }

    /**
     * @param $name
     * @throws ArgumentNullException
     */
    private function unsetOption($name)
    {
        Option::delete($this->MODULE_ID, ['name' => $name]);
    }
}
