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

class PluginStockmanagementDashboard extends CommonDBTM
{
    public static $rightname = 'plugin_stockmanagement_dashboard';

    private const DASHBOARD_TABLE = 'glpi_plugin_stockmanagement_dashboard';
    private const CONFIG_TABLE = 'glpi_plugin_stockmanagement_configs';
    private const STATE_TABLE = 'glpi_plugin_stockmanagement_states';

    public $data = [];

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (get_class($item) == 'Central') {
            return [1 => __("Stock management", 'stockmanagement')];
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($tabnum) {
            case 1: // all
                PluginStockmanagementDashboard::showDashBoard();
                break;
        }
        return true;
    }


    public static function showDashBoard()
    {

        if (Session::haveRight("plugin_stockmanagement_dashboard", READ)) {
            $searchType = [
                0   => '-----'
            ];
            $searchMarque = [
                0   => '-----'
            ];
            $searchModel = [
                0   => '-----'
            ];
            $i = 0;

            $state          = self::getState();
            $searchParams   = self::retrieveDashboardData();

            if (is_array($searchParams)) {
                foreach ($searchParams as $param) {
                    if ($param['TYPE'] != null) {
                        $searchType[$param['TYPE']] = $param['TYPE'];

                    }
                    if ($param['MARQUE'] != null) {
                        $searchMarque[$param['MARQUE']] = $param['MARQUE'];
                    }
                    if ($param['MODEL'] != null) {
                        $searchModel[$param['MODEL']] = $param['MODEL'];
                    }
                }
            }

            echo "<div >";
            echo "<b>".__("Stock per type", "stockmanagement")."</b>";
            echo "</div><br/>";

            echo "<div >";
            echo "<table class='tab_cadre'><tr class='tab_bg_2'>";
            echo "<td> Type : ";
            Dropdown::showFromArray('search_type', $searchType, ['value' => 0]);
            echo "</td>";
            echo "<td>";
            echo "<input type='submit' class='submit' name='submit' value=\"" . __('Search') . "\" onClick='reloadType();'>";
            echo "</td>";
            echo "<td>";
            echo "<input type='submit' class='submit' name='reset' value=\"" . __('Reset') . "\" onClick='reset();'>";
            echo "</td>";
            echo "</tr></table>";
            Html::closeForm();
            echo "</div>";

            $machines   = self::retrieveDashboardData();

            // TYPE
            echo "<div >";
            echo "<table class='tab_cadrehov'>";

            // Fields header
            echo "<tr>";
            echo "<th>" . __("Material type", "stockmanagement") . "</th>";
            echo "<th>" . sprintf(__('Number in : %1$s', "stockmanagement"), $state['name']). "</th>";
            echo "<th>" . __("Alert threshold", "stockmanagement") . "</th>";
            echo "<th>" . __("Notification sending", "stockmanagement") . "</th>";
            echo "</tr>";

            if (!is_array($machines)) {
                echo "<tr class='tab_bg_1'>";
                echo "<td >" . $machines . "</td>";
                echo "</tr>";
            } else {

                foreach ($machines as $values) {
                    if (isset($values['TYPE'])) {
                        $i++;
                        echo "<tr class='tab_bg_1' id='search_replace_type$i'>";
                        echo "<td >" . Html::entities_deep($values['TYPE']) . "</td>";
                        echo "<td >" . Html::entities_deep($values['NB']) . "</td>";
                        if ($values['NB'] <= $values['SEUIL']) {
                            echo "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . Html::entities_deep($values['SEUIL']) . "</td>";
                        } else {
                            echo "<td >" . Html::entities_deep($values['SEUIL']) . "</td>";
                        }


                        if ($values['NOTIF'] == null) {
                            $values['NOTIF'] = __("No current notification", "stockmanagement");
                        } else {
                            $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
                        }

                        echo "<td >" . Html::entities_deep($values['NOTIF']) . "</td>";
                        echo "</tr>";
                    }
                }
            }
            echo "<tr id='search_replace_type'></tr>";
            echo "</table></div>";

            echo "<br/><br/>";

            echo "<div >";
            echo "<b>".__("Stock per manufacturer and model", "stockmanagement")."</b>";
            echo "</div><br/>";

            echo "<div >";
            echo "<table class='tab_cadre'><tr class='tab_bg_2'>";
            echo "<td> ".__("Manufacturer")." : ";
            Dropdown::showFromArray('search_marque', $searchMarque, ['value' => 0]);
            echo "</td>";
            echo "<td> ".__("Model")." : ";
            Dropdown::showFromArray('search_model', $searchModel, ['value' => 0]);
            echo "</td>";
            echo "<td>";
            echo "<input type='submit' class='submit' name='submit' value=\"" . __('Search') . "\" onClick='reloadMarque();'>";
            echo "</td>";
            echo "<td>";
            echo "<input type='submit' class='submit' name='reset' value=\"" . __('Reset') . "\" onClick='reset();'>";
            echo "</td>";
            echo "</tr></table>";
            Html::closeForm();
            echo "</div>";
            // MANUFACTURER/MODEL
            echo "<div >";
            echo "<table class='tab_cadrehov'>";

            // Fields header
            echo "<tr>";
            echo "<th>" . __("Manufacturer") . "</th>";
            echo "<th>" . __("Model") . "</th>";
            echo "<th>" . sprintf(__('Number in : %1$s', "stockmanagement"), $state['name']). "</th>";
            echo "<th>" . __("Alert threshold", "stockmanagement") . "</th>";
            echo "<th>" . __("Notification sending", "stockmanagement") . "</th>";
            echo "</tr>";

            if (!is_array($machines)) {
                echo "<tr class='tab_bg_1'>";
                echo "<td >" . $machines . "</td>";
                echo "</tr>";
            } else {
                foreach ($machines as $values) {
                    if (isset($values['MARQUE'])) {
                        $i++;
                        echo "<tr class='tab_bg_1' id='search_replace_marque$i'>";
                        echo "<td >" . Html::entities_deep($values['MARQUE']) . "</td>";
                        echo "<td >" . Html::entities_deep($values['MODEL']) . "</td>";
                        echo "<td >" . Html::entities_deep($values['NB']) . "</td>";
                        if ($values['NB'] <= $values['SEUIL']) {
                            echo "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . Html::entities_deep($values['SEUIL']) . "</td>";
                        } else {
                            echo "<td >" . Html::entities_deep($values['SEUIL']) . "</td>";
                        }

                        if ($values['NOTIF'] == null) {
                            $values['NOTIF'] = __("No current notification", "stockmanagement");
                        } else {
                            $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
                        }

                        echo "<td >" . Html::entities_deep($values['NOTIF']) . "</td>";
                        echo "</tr>";
                    }
                }
            }
            echo "<tr id='search_replace_marque'></tr>";
            echo "</table></div>";
            echo "<input type='hidden' value='$i' id='ivalue'/>";
        } else {
            echo __("You don't have the required rights", "stockmanagement");
        }
    }

    public static function getState()
    {
        global $DB;

        $result = $DB->request([
            'SELECT'    => [
                self::STATE_TABLE . '.STATE_ID',
                'glpi_states.name AS name',
            ],
            'FROM'      => self::STATE_TABLE,
            'LEFT JOIN' => [
                'glpi_states' => [
                    'ON' => [
                        self::STATE_TABLE => 'STATE_ID',
                        'glpi_states'     => 'id',
                    ],
                ],
            ],
            'LIMIT'     => 1,
        ]);
        foreach ($result as $state) {
            return $state;
        }

        return ['STATE_ID' => 0, 'name' => Dropdown::EMPTY_VALUE];
    }

    public function getAllMachines($state)
    {
        global $DB;
        $materialTable = [
            Computer::class         => 'glpi_computers',
            Monitor::class          => 'glpi_monitors',
            NetworkEquipment::class => 'glpi_networkequipments',
            Peripheral::class       => "glpi_peripherals",
            Printer::class          => "glpi_printers",
            Phone::class            => 'glpi_phones'
          ];

        $machine = [];

        foreach ($materialTable as $name => $table) {
            $model = "glpi_".strtolower($name)."models";
            $model_id = strtolower($name)."models_id";
            $type = "glpi_".strtolower($name)."types";
            $type_id = strtolower($name)."types_id";

            $result1 = $DB->request([
                'SELECT'    => [
                    'COUNT' => "$table.id AS NB",
                    "$type.name AS name",
                    self::CONFIG_TABLE . '.ALERT_SEUIL',
                ],
                'FROM'      => self::CONFIG_TABLE,
                'LEFT JOIN' => [
                    $type => [
                        'ON' => [
                            $type => 'id',
                            self::CONFIG_TABLE => 'TYPE_ID',
                        ],
                    ],
                    $table => [
                        'ON' => [
                            $table             => $type_id,
                            self::CONFIG_TABLE => 'TYPE_ID',
                            [
                                'AND' => [
                                    "$table.states_id"   => (int) $state,
                                    "$table.is_template" => 0,
                                ],
                            ],
                        ],
                    ],
                ],
                'WHERE'     => [
                    self::CONFIG_TABLE . '.CLASS_TYPE' => self::quotedValue($name),
                    self::CONFIG_TABLE . '.TYPE'       => self::quotedValue('TYPE'),
                ],
                'GROUPBY'   => [
                    self::CONFIG_TABLE . '.TYPE_ID',
                    "$type.name",
                    self::CONFIG_TABLE . '.ALERT_SEUIL',
                ],
            ]);

            foreach ($result1 as $value) {
                $machine["TYPE"][] = $value;
            }

            $result2 = $DB->request([
                'SELECT'    => [
                    'COUNT' => "$table.id AS NB",
                    'glpi_manufacturers.name AS marque',
                    "$model.name AS model",
                    self::CONFIG_TABLE . '.ALERT_SEUIL',
                ],
                'FROM'      => self::CONFIG_TABLE,
                'LEFT JOIN' => [
                    'glpi_manufacturers' => [
                        'ON' => [
                            'glpi_manufacturers' => 'id',
                            self::CONFIG_TABLE   => 'MARQUE_ID',
                        ],
                    ],
                    $model => [
                        'ON' => [
                            $model => 'id',
                            self::CONFIG_TABLE => 'MODEL_ID',
                        ],
                    ],
                    $table => [
                        'ON' => [
                            $table             => $model_id,
                            self::CONFIG_TABLE => 'MODEL_ID',
                            [
                                'AND' => [
                                    "$table.manufacturers_id" => new QueryExpression($DB->quoteName(self::CONFIG_TABLE . '.MARQUE_ID')),
                                    "$table.states_id"        => (int) $state,
                                    "$table.is_template"      => 0,
                                ],
                            ],
                        ],
                    ],
                ],
                'WHERE'     => [
                    self::CONFIG_TABLE . '.CLASS_TYPE' => self::quotedValue($name),
                    self::CONFIG_TABLE . '.TYPE'       => self::quotedValue('MARQUE'),
                ],
                'GROUPBY'   => [
                    self::CONFIG_TABLE . '.MARQUE_ID',
                    self::CONFIG_TABLE . '.MODEL_ID',
                    'glpi_manufacturers.name',
                    "$model.name",
                    self::CONFIG_TABLE . '.ALERT_SEUIL',
                ],
            ]);

            foreach ($result2 as $value) {
                $machine["MARQUE"][] = $value;
            }
        }

        return $machine;
    }

    private static function retrieveDashboardData()
    {
        global $DB;
        $data = null;

        $result = $DB->request([
            'FROM'    => self::DASHBOARD_TABLE,
            'ORDERBY' => ['TYPE', 'MARQUE', 'MODEL'],
        ]);

        if ($result->count() == 0) {
            $data = __("No data available", "stockmanagement");
        } else {
            foreach ($result as $value) {
                $data[] = $value;
            }
        }

        return $data;
    }

    public function refreshTableDashboard($datas)
    {
        global $DB;

        $previous_notifications = $this->getPreviousNotifications();
        $this->cleanDashboard();

        foreach ($datas as $type => $values) {
            if ($type === 'NOTIFICATION') {
                continue;
            }
            foreach ($values as $key => $data) {
                if ($type == "TYPE") {
                    $typeName   = $data['name'];
                    $nb     = (int) $data['NB'];
                    $seuil  = (int) $data['ALERT_SEUIL'];
                    $notif_key = self::getTypeNotificationKey($typeName);

                    if (isset($data['NOTIF'])) {
                        if (isset($previous_notifications[$notif_key])) {
                            $notif = $previous_notifications[$notif_key];
                            unset($datas[$type][$key]['NOTIF']);
                        } else {
                            $notif = null;
                            $datas['NOTIFICATION'] = true;
                        }
                    } else {
                        $notif = null;
                    }

                    $DB->insert(self::DASHBOARD_TABLE, [
                        'TYPE'  => self::quotedValue($typeName),
                        'NB'    => $nb,
                        'SEUIL' => $seuil,
                        'NOTIF' => $notif === null ? null : self::quotedValue($notif),
                    ]);
                } else {
                    $marque = $data['marque'];
                    $model  = $data['model'];
                    $nb     = (int) $data['NB'];
                    $seuil  = (int) $data['ALERT_SEUIL'];
                    $notif_key = self::getManufacturerModelNotificationKey($marque, $model);

                    if (isset($data['NOTIF'])) {
                        if (isset($previous_notifications[$notif_key])) {
                            $notif = $previous_notifications[$notif_key];
                            unset($datas[$type][$key]['NOTIF']);
                        } else {
                            $notif = null;
                            $datas['NOTIFICATION'] = true;
                        }
                    } else {
                        $notif = null;
                    }

                    $DB->insert(self::DASHBOARD_TABLE, [
                        'MARQUE' => self::quotedValue($marque),
                        'MODEL'  => self::quotedValue($model),
                        'NB'     => $nb,
                        'SEUIL'  => $seuil,
                        'NOTIF'  => $notif === null ? null : self::quotedValue($notif),
                    ]);
                }
            }
        }
        return $datas;
    }

    private function cleanDashboard()
    {
        global $DB;

        $DB->delete(self::DASHBOARD_TABLE, [new QueryExpression('1 = 1')]);
    }

    public function verifSeuil($datas)
    {
        foreach ($datas as $keys => $type) {
            foreach ($type as $key => $data) {
                if (intval($data['NB']) <= intval($data['ALERT_SEUIL'])) {
                    $datas[$keys][$key]['NOTIF'] = date("Y-m-d H:i:s");
                }
            }
        }

        return $datas;
    }

    public function updateNotif($type, $marque, $model, $notif)
    {
        global $DB;

        if ($type != null) {
            $DB->update(
                self::DASHBOARD_TABLE,
                ['NOTIF' => self::quotedValue($notif)],
                ['TYPE' => self::quotedValue($type)]
            );
        } else {
            $DB->update(
                self::DASHBOARD_TABLE,
                ['NOTIF' => self::quotedValue($notif)],
                [
                    'MARQUE' => self::quotedValue($marque),
                    'MODEL'  => self::quotedValue($model),
                ]
            );
        }
    }

    private function getPreviousNotifications()
    {
        global $DB;

        $notifications = [];
        $iterator = $DB->request([
            'SELECT' => ['TYPE', 'MARQUE', 'MODEL', 'NOTIF'],
            'FROM'   => self::DASHBOARD_TABLE,
            'WHERE'  => [
                'NOT' => ['NOTIF' => null],
            ],
        ]);

        foreach ($iterator as $row) {
            if ($row['TYPE'] !== null) {
                $notifications[self::getTypeNotificationKey($row['TYPE'])] = $row['NOTIF'];
            } else {
                $notifications[self::getManufacturerModelNotificationKey($row['MARQUE'], $row['MODEL'])] = $row['NOTIF'];
            }
        }

        return $notifications;
    }

    private static function getTypeNotificationKey($type)
    {
        return 'TYPE:' . $type;
    }

    private static function getManufacturerModelNotificationKey($manufacturer, $model)
    {
        return 'MARQUE:' . $manufacturer . ':' . $model;
    }

    private static function quotedValue($value)
    {
        global $DB;

        return new QueryExpression($DB->quote((string) $value));
    }
}
