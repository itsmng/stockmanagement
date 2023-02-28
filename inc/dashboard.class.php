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

class PluginStockmanagementDashboard extends CommonDBTM {

    static $rightname = 'plugin_stockmanagement_dashboard';

    public $data = [];

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (get_class($item) == 'Central') {
            return [1 => __("Stock management", 'stockmanagement')];
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        switch ($tabnum) {
            case 1 : // all
                PluginStockmanagementDashboard::showDashBoard();
                break;
        }
        return true;
    }

  
    public static function showDashBoard() {

        if(Session::haveRight("plugin_stockmanagement_dashboard", READ)){
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

            if(is_array($searchParams)) {
                foreach($searchParams as $param) {
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
    
            if(!is_array($machines)) {
                echo "<tr class='tab_bg_1'>";
                echo "<td >" . $machines . "</td>";
                echo "</tr>";
            } else {
                
                foreach($machines as $values) {
                    if(isset($values['TYPE'])) {
                        $i++;
                        echo "<tr class='tab_bg_1' id='search_replace_type$i'>";
                        echo "<td >" . $values['TYPE'] . "</td>";
                        echo "<td >" . $values['NB'] . "</td>";
                        if($values['NB'] <= $values['SEUIL']) {
                            echo "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . $values['SEUIL'] . "</td>";
                        } else {
                            echo "<td >" . $values['SEUIL'] . "</td>";
                        }
                        

                        if($values['NOTIF'] == null) {
                            $values['NOTIF'] = __("No current notification", "stockmanagement");
                        } else {
                            $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
                        }

                        echo "<td >" . $values['NOTIF'] . "</td>";
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
    
            if(!is_array($machines)) {
                echo "<tr class='tab_bg_1'>";
                echo "<td >" . $machines . "</td>";
                echo "</tr>";
            } else {
                foreach($machines as $values) {
                    if(isset($values['MARQUE'])) {
                        $i++;
                        echo "<tr class='tab_bg_1' id='search_replace_marque$i'>";
                        echo "<td >" . $values['MARQUE'] . "</td>";
                        echo "<td >" . $values['MODEL'] . "</td>";
                        echo "<td >" . $values['NB'] . "</td>";
                        if($values['NB'] <= $values['SEUIL']) {
                            echo "<td  style='font-weight:bold;background-color:#ff4d4d;'>" . $values['SEUIL'] . "</td>";
                        } else {
                            echo "<td >" . $values['SEUIL'] . "</td>";
                        }

                        if($values['NOTIF'] == null) {
                            $values['NOTIF'] = __("No current notification", "stockmanagement");
                        } else {
                            $values['NOTIF'] = sprintf(__('Notification sent on : %1$s', "stockmanagement"), $values['NOTIF']);
                        }

                        echo "<td >" . $values['NOTIF'] . "</td>";
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

    public static function getState() {
        global $DB;

        $sql = "SELECT s.STATE_ID, n.name FROM glpi_plugin_stockmanagement_states s LEFT JOIN glpi_states n ON s.STATE_ID = n.id";
        $result = $DB->query($sql);
        foreach($result as $state) {
            return $state;
        }
    }

    public function getAllMachines($state) {
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

        foreach($materialTable as $name => $table) {
            $model = "glpi_".strtolower($name)."models";
            $model_id = strtolower($name)."models_id";
            $type = "glpi_".strtolower($name)."types";
            $type_id = strtolower($name)."types_id";

            $query1 = " SELECT count(t.name) as NB, t.name, s.ALERT_SEUIL
                        FROM $table c
                        LEFT JOIN $type t ON t.id = c.$type_id
                        LEFT JOIN glpi_plugin_stockmanagement_configs s ON s.TYPE_ID = c.$type_id
                        WHERE c.states_id = $state AND s.CLASS_TYPE = '$name' AND s.TYPE = 'TYPE' AND c.is_template = 0
                        GROUP BY s.TYPE_ID";
            $result1 = $DB->query($query1);

            if($result1) foreach($result1 as $value) {
                $machine["TYPE"][] = $value;
            }

            $query2 = "  SELECT count(CONCAT(f.name, m.name)) as NB, f.name as marque, m.name as model, s.ALERT_SEUIL 
                        FROM $table c 
                        LEFT JOIN glpi_manufacturers f ON f.id = c.manufacturers_id 
                        LEFT JOIN $model m ON m.id = c.$model_id
                        LEFT JOIN glpi_plugin_stockmanagement_configs s ON s.MODEL_ID = c.$model_id
                        WHERE c.states_id = $state AND s.CLASS_TYPE = '$name' AND s.TYPE = 'MARQUE' AND c.is_template = 0
                        GROUP BY CONCAT(f.name, m.name)";
            $result2 = $DB->query($query2);
            
            if($result2) foreach($result2 as $value) {
                $machine["MARQUE"][] = $value;
            }
        } 

        return $machine;
    }

    private static function retrieveDashboardData() {
        global $DB;
        $data = null;

        $query = "SELECT * FROM `glpi_plugin_stockmanagement_dashboard` ORDER BY TYPE, MARQUE, MODEL";
        $result = $DB->query($query);

        if($result->num_rows == 0) {
            $data = __("No data available", "stockmanagement");
        } else {
            foreach($result as $value) {
                $data[] = $value;  
            }
        }

        return $data;
    }

    public function refreshTableDashboard($datas) {
        global $DB;

        $this->cleanDashboard();

        foreach($datas as $type => $values) {
            foreach($values as $key => $data) {
                if($type == "TYPE") {
                    $typeName   = $data['name'];
                    $nb     = $data['NB'];
                    $seuil  = $data['ALERT_SEUIL'];

                    if(isset($data['NOTIF'])) {
                        $notif = $data['NOTIF'];
                    } else {
                        $notif = null;
                    }

                    $query = "SELECT id FROM glpi_plugin_stockmanagement_dashboard WHERE TYPE = '$typeName'";
                    $result = $DB->query($query);

                    if($result->num_rows == 0) {
                        $query = "INSERT INTO glpi_plugin_stockmanagement_dashboard (TYPE, NB, SEUIL) VALUES ('$typeName', $nb, $seuil)";
                        if($notif != null) {
                            $datas['NOTIFICATION'] = true;
                        }
                        $result = $DB->query($query);
                    } else {
                        if($notif == null) {
                            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NB = $nb, SEUIL = $seuil WHERE TYPE = '$typeName'";
                        } else {
                            $verif = self::verifIfNotifAlreadySend($type, null, null, $notif);
                            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NB = $nb, SEUIL = $seuil WHERE TYPE = '$typeName'";
                            if($verif == false) {
                                $datas['NOTIFICATION'] = true;
                            } else {
                                unset($datas['NOTIF']);
                            }
                        }
                        $result = $DB->query($query);
                    }
                } else {
                    $marque = $data['marque'];
                    $model  = $data['model'];
                    $nb     = $data['NB'];
                    $seuil  = $data['ALERT_SEUIL'];

                    if(isset($data['NOTIF'])) {
                        $notif = $data['NOTIF'];
                    } else {
                        $notif = null;
                    }

                    $query = "SELECT id FROM glpi_plugin_stockmanagement_dashboard WHERE MARQUE = '$marque' AND MODEl = '$model'";
                    $result = $DB->query($query);

                    if($result->num_rows == 0) {
                        $query = "INSERT INTO glpi_plugin_stockmanagement_dashboard (MARQUE, MODEL, NB, SEUIL) VALUES ('$marque', '$model', $nb, $seuil)";
                        if($notif != null) {
                            $datas['NOTIFICATION'] = true;
                        }
                        $result = $DB->query($query);
                    } else {
                        if($notif == null) {
                            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NB = $nb, SEUIL = $seuil WHERE MARQUE = '$marque' AND MODEl = '$model'";
                        } else {
                            $verif = self::verifIfNotifAlreadySend(null, $marque, $model, $notif);
                            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NB = $nb, SEUIL = $seuil WHERE MARQUE = '$marque' AND MODEl = '$model'";
                            if($verif == false) {
                                $datas['NOTIFICATION'] = true;
                            } else {
                                unset($datas['NOTIF']);
                            }
                        }
                        $result = $DB->query($query);
                    }
                }
            }
        }
        return $datas;
    }

    private function cleanDashboard() {
        global $DB;

        $query = "DELETE FROM glpi_plugin_stockmanagement_dashboard";
        $result = $DB->query($query);
    }

    private function verifIfNotifAlreadySend($type, $model, $marque, $date) {
        global $DB;

        if($type != null) {
            $query = "SELECT id FROM glpi_plugin_stockmanagement_dashboard WHERE TYPE = '$type' AND NOTIF = '$date'";
            $result = $DB->query($query);
        } else {
            $query = "SELECT id FROM glpi_plugin_stockmanagement_dashboard WHERE MARQUE = '$marque' AND MODEL = '$model' AND NOTIF = '$date'";
            $result = $DB->query($query);
        }

        if($result->num_rows == 0) {
            return false;
        }

        return true;
    }

    public function verifSeuil($datas) {
        foreach($datas as $keys => $type) {
            foreach($type as $key => $data) {
                if(intval($data['NB']) <= intval($data['ALERT_SEUIL'])) {
                    $datas[$keys][$key]['NOTIF'] = date("Y-m-d H:i:s");
                }
            }
        }

        return $datas;
    }

    public function updateNotif($type, $marque, $model, $notif) {
        global $DB;

        if($type != null) {
            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NOTIF = '$notif' WHERE TYPE = '$type'";
            $result = $DB->query($query);
        } else {
            $query = "UPDATE glpi_plugin_stockmanagement_dashboard SET NOTIF = '$notif' WHERE MARQUE = '$marque' AND MODEL = '$model'";
            $result = $DB->query($query);
        }
    }
}