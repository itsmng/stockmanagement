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

class PluginStockmanagementConfig extends CommonDBTM
{
    public static $rightname         = 'plugin_stockmanagement_config';

    private const CONFIG_TABLE = 'glpi_plugin_stockmanagement_configs';
    private const STATE_TABLE = 'glpi_plugin_stockmanagement_states';

    private $materialType = [
        Computer::class => "Computer",
        Monitor::class  => "Monitor",
        NetworkEquipment::class => "Network device",
        Peripheral::class => "Peripheral",
        Printer::class  => "Printer",
        Phone::class  => "Phone"
    ];

    public static function getTypeName($nb = 0)
    {
        return __("Stock management", 'stockmanagement');
    }

    public static function getMenuContent()
    {

        $menu = array();
        //Menu entry in config
        $menu['title'] = self::getTypeName(2);
        $menu['page'] = "/plugins/stockmanagement/front/config.form.php";
        $menu['links']['search'] = "/plugins/stockmanagement/front/config.form.php";
        $menu['links']['add'] = '/plugins/stockmanagement/front/config.form.php';
        $menu['icon']  = "fa-fw fas fa-chart-bar";

        return $menu;
    }

    public function showForm($ID, $options = array())
    {
        global $DB;

        $state = $this->getState();
        $status = $this->getStatusConfig();
        $form = [
            'action' => $this->getFormURL(),
            'itemtype' => $this->getType(),
            'content' => [
                '' => [
                    'visible' => true,
                    'inputs' => [
                        __("Stock status", "stockmanagement") => [
                            'name' => 'stock_status',
                            'type' => 'select',
                            'value' => $status,
                            'values' => $state,
                        ],
                    ],
                ],
            ],
        ];
        foreach ($this->materialType as $class => $title) {
            $typeName = $class."Type";
            $type = new $typeName();
            $allType = $type->find();
            $config = $this->getConfig("TYPE");
            if (!empty($allType)) {
                $form['content'][__($title) . " - (" . __("Type") . ")"] = [
                    'visible' => true,
                    'inputs' => [],
                ];
                foreach ($allType as $infos) {
                    if ($config == 0 || !isset($config[$class][$infos['id']])) {
                        $num = 0;
                    } else {
                        $num = $config[$class][$infos['id']];
                    }
                    $form['content'][__($title) . " - (" . __("Type") . ")"]['inputs'][$infos['name']] = [
                        'name' => 'seuil_'.$class.'_TYPE_'.$infos['id'],
                        'type' => 'number',
                        'value' => $num,
                        'min' => 0,
                        'before' => __("Alert threshold", "stockmanagement"),
                        'col_lg' => 6,
                    ];
                }
            }
        }

        foreach ($this->materialType as $class => $title) {
            $list = $this->getModelAndMarque($class);
            $config = $this->getConfig("MARQUE");

            if (!empty($list)) {
                $form['content'][__($title) . " - (" . __("Manufacturer") . " - " . __("Model") . ")"] = [
                    'visible' => true,
                    'inputs' => [],
                ];
                foreach ($list as $infos) {
                    if (isset($infos['MARQUE_NAME']) && isset($infos['MODEL_NAME'])) {
                        $config_key = self::getManufacturerModelConfigKey($infos['MARQUE_ID'], $infos['MODEL_ID']);
                        if ($config == 0 || !isset($config[$class][$config_key])) {
                            $num = 0;
                        } else {
                            $num = $config[$class][$config_key];
                        }
                        $form['content'][__($title) . " - (" . __("Manufacturer") . " - " . __("Model") . ")"]['inputs'][$infos['MARQUE_NAME']." - ".$infos['MODEL_NAME']] = [
                            'name' => 'seuil_'.$class.'_MARQUE_'.$infos['MARQUE_ID'].'_'.$infos['MODEL_ID'],
                            'type' => 'number',
                            'value' => $num,
                            'min' => 0,
                            'before' => __("Alert threshold", "stockmanagement"),
                            'col_lg' => 6,
                        ];
                    }
                }
            }
        }

        renderTwigForm($form, '', $options);
        return true;
    }

    private function getModelAndMarque($class)
    {
        $manufacturers = new Manufacturer();
        $manufacturers = $manufacturers->find();
        $modelName = $class."Model";
        $classEquipment = new $class();
        $modelClass = new $modelName();

        $list = [];
        foreach ($manufacturers as $key => $value) {
            $equipment = $classEquipment->find(["manufacturers_id" => $manufacturers[$key]['id']]);
            foreach ($equipment as $id => $values) {
                $model_id = (int) $equipment[$id][strtolower($class).'models_id'];
                $manufacturer_id = (int) $manufacturers[$key]['id'];
                if ($model_id === 0) {
                    continue;
                }
                $list[self::getManufacturerModelConfigKey($manufacturer_id, $model_id)] = [
                    'MARQUE_ID'   => $manufacturer_id,
                    'MARQUE_NAME' => $manufacturers[$key]['name'],
                    'MODEL_ID'    => $model_id,
                ];
            }
        }

        if (!empty($list)) {
            foreach ($list as $key => $marque) {
                $modelArray = $modelClass->find(["id" => $marque['MODEL_ID']]);
                if (empty($modelArray)) {
                    unset($list[$key]);
                }
                foreach ($modelArray as $num => $name) {
                    $list[$key]['MODEL_ID'] = $modelArray[$num]['id'];
                    $list[$key]['MODEL_NAME'] = $modelArray[$num]['name'];
                }
            }
        }

        return $list;
    }

    public function getSearchOptions()
    {
        $tab = array();

        return $tab;
    }

    public function install(Migration $mig)
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    private function getState()
    {
        $allState = [];
        $state = new State();
        $states = $state->find();
        foreach ($states as $list) {
            $allState[$list['id']] = $list['name'];
        }

        return $allState;
    }

    public function updateConfig($idConfig, $post)
    {
        global $DB;

        $status = null;
        $seuil = [];
        foreach ($post as $key => $value) {
            if ($key == "stock_status") {
                $status = (int) $value;
            } elseif (strpos($key, "seuil_") !== false) {
                $keys = explode("_", $key);
                if (
                    count($keys) < 4
                    || !isset($this->materialType[$keys[1]])
                    || !in_array($keys[2], ['TYPE', 'MARQUE'], true)
                ) {
                    continue;
                }

                if ($keys[2] == "TYPE") {
                    $seuil[$keys[2]][$keys[1]][(int) $keys[3]] = max(0, (int) $value);
                } elseif (isset($keys[4])) {
                    $seuil[$keys[2]][$keys[1]][(int) $keys[3]][(int) $keys[4]] = max(0, (int) $value);
                }
            }
        }

        // Insert / Update status stock

        $this->insertUpdateState($status);



        foreach ($seuil as $type => $infos) {
            if ($type == "TYPE") {
                foreach ($infos as $class => $info) {
                    foreach ($info as $id => $nbseuil) {
                        $where = [
                            'TYPE_ID'    => (int) $id,
                            'CLASS_TYPE' => self::quotedValue($class),
                            'TYPE'       => self::quotedValue($type),
                        ];
                        $exists = self::configExists($where);

                        if (!$exists && $nbseuil != 0) {
                            $DB->insert(self::CONFIG_TABLE, [
                                'CONFIG_ID'   => (int) $idConfig,
                                'TYPE_ID'     => (int) $id,
                                'CLASS_TYPE'  => self::quotedValue($class),
                                'ALERT_SEUIL' => (int) $nbseuil,
                                'TYPE'        => self::quotedValue($type),
                            ]);
                        } elseif ($exists && $nbseuil == 0) {
                            $DB->delete(self::CONFIG_TABLE, $where);
                        } else {
                            $DB->update(self::CONFIG_TABLE, ['ALERT_SEUIL' => (int) $nbseuil], $where);
                        }
                    }
                }
            } else {
                foreach ($infos as $class => $values) {
                    foreach ($values as $marque => $model) {
                        foreach ($model as $id => $nbseuil) {
                            $where = [
                                'MARQUE_ID'  => (int) $marque,
                                'MODEL_ID'   => (int) $id,
                                'CLASS_TYPE' => self::quotedValue($class),
                                'TYPE'       => self::quotedValue($type),
                            ];
                            $exists = self::configExists($where);

                            if (!$exists && $nbseuil != 0) {
                                $DB->insert(self::CONFIG_TABLE, [
                                    'CONFIG_ID'   => (int) $idConfig,
                                    'MARQUE_ID'   => (int) $marque,
                                    'MODEL_ID'    => (int) $id,
                                    'CLASS_TYPE'  => self::quotedValue($class),
                                    'ALERT_SEUIL' => (int) $nbseuil,
                                    'TYPE'        => self::quotedValue($type),
                                ]);
                            } elseif ($exists && $nbseuil == 0) {
                                $DB->delete(self::CONFIG_TABLE, $where);
                            } else {
                                $DB->update(self::CONFIG_TABLE, ['ALERT_SEUIL' => (int) $nbseuil], $where);
                            }
                        }
                    }
                }
            }
        }

        PluginStockmanagementNotification::sendAlertUpdate();
    }

    private function insertUpdateState($status)
    {
        global $DB;

        $status = (int) $status;
        $iterator = $DB->request([
            'SELECT' => ['STATE_ID'],
            'FROM'   => self::STATE_TABLE,
            'WHERE'  => ['id' => 1],
            'LIMIT'  => 1,
        ]);

        if ($iterator->count() == 0) {
            $DB->insert(self::STATE_TABLE, ['STATE_ID' => $status]);
        } else {
            $DB->update(self::STATE_TABLE, ['STATE_ID' => $status], ['id' => 1]);
        }
    }

    public function getStatusConfig()
    {
        global $DB;

        $result = $DB->request([
            'SELECT' => ['STATE_ID'],
            'FROM'   => self::STATE_TABLE,
            'WHERE'  => ['id' => 1],
            'LIMIT'  => 1,
        ]);
        foreach ($result as $status) {
            return $status['STATE_ID'];
        }

        return 0;
    }

    public function getConfig($type)
    {
        global $DB;
        $config = [];

        $result = $DB->request([
            'FROM'  => self::CONFIG_TABLE,
            'WHERE' => [
                'CONFIG_ID' => 1,
                'TYPE'      => self::quotedValue($type),
            ],
        ]);

        foreach ($result as $infos) {
            if ($type == "TYPE") {
                $config[$infos['CLASS_TYPE']][$infos['TYPE_ID']] = $infos['ALERT_SEUIL'];
            } else {
                $config[$infos['CLASS_TYPE']][self::getManufacturerModelConfigKey($infos['MARQUE_ID'], $infos['MODEL_ID'])] = $infos['ALERT_SEUIL'];
            }
        }

        return $config ?: 0;
    }

    private static function getManufacturerModelConfigKey($manufacturer_id, $model_id)
    {
        return (int) $manufacturer_id . ':' . (int) $model_id;
    }

    private static function configExists(array $where)
    {
        global $DB;

        return $DB->request([
            'SELECT' => ['id'],
            'FROM'   => self::CONFIG_TABLE,
            'WHERE'  => $where,
            'LIMIT'  => 1,
        ])->count() > 0;
    }

    private static function quotedValue($value)
    {
        global $DB;

        return new QueryExpression($DB->quote((string) $value));
    }

}
