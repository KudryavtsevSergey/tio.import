<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$request = Application::getInstance()->getContext()->getRequest();

$module_id = $request->get('mid');

$groups = require __DIR__ . '/options.php';

if (isset($_SESSION[$module_id]) && $_SESSION[$module_id] == 'Y') {
    unset($_SESSION[$module_id]);
    echo CAdminMessage::ShowNote("Успешно обновлено");
} ?>

<form method="POST">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="id" value="<?= $module_id ?>">
    <input type="hidden" name="step" value="register">

    <div class="adm-detail-content-wrap">
        <div class="adm-detail-content">
            <div class="adm-detail-title">Настройка параметров модуля <?= $module_id; ?></div>
            <div class="adm-detail-content-item-block">
                <table class="adm-detail-content-table edit-table">
                    <tbody>
                    <? foreach ($groups as $group): ?>

                        <tr class="heading">
                            <td colspan="2"><b><?= $group['GROUP']; ?></b></td>
                        </tr>
                        <? foreach ($group['OPTIONS'] as $option): ?>
                            <tr>
                                <td class="adm-detail-content-cell-l">
                                    <label for="<?= $option['NAME'] ?>">
                                        <?= $option['NAME']; ?>:
                                    </label>
                                </td>
                                <td class="adm-detail-content-cell-r">
                                    <? switch ($option['TYPE']):
                                        case 'TEXT': ?>

                                            <input type="text" size="30" maxlength="255" id="<?= $option['NAME'] ?>"
                                                   value="<?= $option['VALUE'] ?>" name="<?= $option['NAME'] ?>"/>

                                            <? break;
                                        case 'SELECT': ?>

                                            <select id="<?= $option['NAME'] ?>" name="<?= $option['NAME'] ?>"
                                                    class="typeselect">
                                                <? foreach ($option['VALUES'] as $value => $text): ?>
                                                    <option
                                                            value="<?= $value ?>"
                                                            <? if ($option['VALUE'] == $value): ?>selected<? endif; ?>
                                                    ><?= $text; ?></option>
                                                <? endforeach; ?>
                                            </select>

                                            <? break;
                                    endswitch; ?>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="left: 0;">
            <div class="adm-detail-content-btns">
                <input type="submit" value="Сохранить" title="Сохранить" class="adm-btn-save">
            </div>
        </div>
    </div>
</form>
