<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight("plugin_stockmanagement_dashboard", READ);

if (isset($_GET['type']) && $_GET['type'] != '0') {
    $data = [];
    $html = "";
    $data = getDataType((string) $_GET['type']);

    if ($data != []) {
        foreach ($data as $values) {
            $html .= "<tr class='tab_bg_1'>";
            $html .= "<td >" . Html::entities_deep($values['TYPE']) . "</td>";
            $html .= "<td >" . Html::entities_deep($values['NB']) . "</td>";

            if ($values['NB'] <= $values['SEUIL']) {
                $html .= "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . Html::entities_deep($values['SEUIL']) . "</td>";
            } else {
                $html .= "<td >" . Html::entities_deep($values['SEUIL']) . "</td>";
            }

            if ($values['NOTIF'] == null) {
                $values['NOTIF'] = __("No current notification", "stockmanagement");
            } else {
                $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
            }

            $html .= "<td >" . Html::entities_deep($values['NOTIF']) . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr class='tab_bg_1'>";
        $html .= "<td >".__("No data available", "stockmanagement")."</td>";
        $html .= "</tr>";
    }

    echo $html;
}

if ((isset($_GET['marque']) || isset($_GET['model']))) {
    $marque = $_GET['marque'] ?? '0';
    $model = $_GET['model'] ?? '0';
    if ($marque == '0' && $model == '0') {
        return;
    }
    $data = [];
    $html = "";
    $data = getDataMarque((string) $marque, (string) $model);

    if ($data != []) {
        foreach ($data as $values) {
            $html .= "<tr class='tab_bg_1'>";
            $html .= "<td >" . Html::entities_deep($values['MARQUE']) . "</td>";
            $html .= "<td >" . Html::entities_deep($values['MODEL']) . "</td>";
            $html .= "<td >" . Html::entities_deep($values['NB']) . "</td>";
            if ($values['NB'] <= $values['SEUIL']) {
                $html .= "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . Html::entities_deep($values['SEUIL']) . "</td>";
            } else {
                $html .= "<td >" . Html::entities_deep($values['SEUIL']) . "</td>";
            }

            if ($values['NOTIF'] == null) {
                $values['NOTIF'] = __("No current notification", "stockmanagement");
            } else {
                $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
            }

            $html .= "<td >" . Html::entities_deep($values['NOTIF']) . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr class='tab_bg_1'>";
        $html .= "<td >".__("No data available", "stockmanagement")."</td>";
        $html .= "</tr>";
    }

    echo $html;
}

function getDataType($type)
{
    global $DB;
    $data = [];

    $result = $DB->request([
        'FROM'  => 'glpi_plugin_stockmanagement_dashboard',
        'WHERE' => ['TYPE' => quotedStockmanagementValue($type)],
    ]);

    foreach ($result as $datas) {
        $data[] = $datas;
    }

    return $data;
}

function getDataMarque($marque, $model)
{
    global $DB;
    $data = [];

    $where = [];

    if ($marque != '0') {
        $where['MARQUE'] = quotedStockmanagementValue($marque);
    }

    if ($model != '0') {
        $where['MODEL'] = quotedStockmanagementValue($model);
    }

    if ($where === []) {
        return $data;
    }

    $result = $DB->request([
        'FROM'  => 'glpi_plugin_stockmanagement_dashboard',
        'WHERE' => $where,
    ]);

    foreach ($result as $datas) {
        $data[] = $datas;
    }

    return $data;
}

function quotedStockmanagementValue($value)
{
    global $DB;

    return new QueryExpression($DB->quote((string) $value));
}
