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
    static $rightname         = 'plugin_stockmanagement_config';

	private $materialType = [
		Computer::class => "Computer",
		Monitor::class  => "Monitor",
		NetworkEquipment::class => "Network device",
		Peripheral::class => "Peripheral",
		Printer::class  => "Printer",
		Phone::class  => "Phone"
    ];

    static function getTypeName($nb=0) {
		return __("Stock management", 'stockmanagement');
    }
   
    static function getMenuContent() {
      
		$menu = array();
		//Menu entry in config
		$menu['title'] = self::getTypeName(2);
		$menu['page'] = "/plugins/stockmanagement/front/config.form.php";
		$menu['links']['search'] = "/plugins/stockmanagement/front/config.form.php";
		$menu['links']['add'] = '/plugins/stockmanagement/front/config.form.php';
		$menu['icon']  = "fa-fw fas fa-chart-bar";

		return $menu;
    }
   
    public function showForm($ID, $options = array()) {
		global $DB;

		$this->initForm($options);
		$state = $this->getState();
		$this->showFormHeader($options);

		// Get config
		$status = $this->getStatusConfig();

		echo "<div class='center' id='tabsbody'>";
		echo "<tr class='tab_bg_1'>";
		echo "<td>".__("Stock status", "stockmanagement")."</td><td>";
		Dropdown::showFromArray('stock_status', $state, ['value' => $status]);
		echo "</td></tr>";
		echo "<tr><th colspan='4'>" . __("Type") . "</th></tr>";
		foreach($this->materialType as $class => $title) {
			$typeName = $class."Type";
			$type = new $typeName();
			$allType = $type->find();
			$config = $this->getConfig("TYPE");
			if(!empty($allType)) {
				echo "<tr><th colspan='4'>" . __($title) . "</th></tr>";
				foreach($allType as $infos) {
					if($config == 0 || $config[$class][$infos['id']] == null) {
						$num = 0;
					} else {
						$num = $config[$class][$infos['id']];
					}
					echo "<tr class='tab_bg_1'><td>".$infos['name']."</td>";
					echo "<td>";
					echo __("Alert threshold", "stockmanagement").' : <input type="number" id="seuil_'.$class.'_TYPE_'.$infos['id'].'" name="seuil_'.$class.'_TYPE_'.$infos['id'].'" value="'.$num.'" min="0">';
					echo "</td></tr>";
				}
			}
		}

		echo "<tr><th colspan='4'>" . __("Manufacturer")." - ".__("Model") . "</th></tr>";
		foreach($this->materialType as $class => $title) {
			$list = $this->getModelAndMarque($class);
			$config = $this->getConfig("MARQUE");

			if(!empty($list)) {
				echo "<tr><th colspan='4'>" . __($title) . "</th></tr>";
				foreach($list as $infos) {
					if(isset($infos['MARQUE_NAME']) && isset($infos['MODEL_NAME'])) {
						if($config == 0 || !isset($config[$class][$infos['MARQUE_ID'].$infos['MODEL_ID']])) {
							$num = 0;
						} else {
							$num = $config[$class][$infos['MARQUE_ID'].$infos['MODEL_ID']];
						}
						echo "<tr class='tab_bg_1'><td>".$infos['MARQUE_NAME']." - ".$infos['MODEL_NAME']."</td>";
						echo "<td>";
						echo __("Alert threshold", "stockmanagement").' : <input type="number" id="seuil_'.$class.'_MARQUE_'.$infos['MARQUE_ID'].'_'.$infos['MODEL_ID'].'" name="seuil_'.$class.'_MARQUE_'.$infos['MARQUE_ID'].'_'.$infos['MODEL_ID'].'" value="'.$num.'" min="0">';
						echo "</td></tr>";
					}
				}
			}
		}
		
		echo "</div>";

		$this->showFormButtons($options);
	
		return true;
	}

	private function getModelAndMarque($class) {
		$manufacturers = new Manufacturer();
		$manufacturers = $manufacturers->find();
		$modelName = $class."Model";
		$classEquipment = new $class();
		$modelClass = new $modelName();

		$list = [];
		foreach($manufacturers as $key => $value) {
			$equipment = $classEquipment->find(["manufacturers_id" => $manufacturers[$key]['id']]);
			foreach ($equipment as $id => $values) {
				$list[$equipment[$id][strtolower($class).'models_id']]['MARQUE_ID'] = $manufacturers[$key]['id'];
				$list[$equipment[$id][strtolower($class).'models_id']]['MARQUE_NAME'] = $manufacturers[$key]['name'];
			}
		}

		if(!empty($list)) {
			foreach($list as $model => $marque) {
				$modelArray = $modelClass->find(["id" => $model]);
				if(empty($modelArray)) unset($list[$model]);
				foreach($modelArray as $num => $name) {
					$list[$model]['MODEL_ID'] = $modelArray[$num]['id'];
					$list[$model]['MODEL_NAME'] = $modelArray[$num]['name'];
				}
			}
		}

		return $list;
	}

    public function getSearchOptions() {
		$tab = array();
		
		return $tab;
    }

    public function install(Migration $mig) { 	
      	return true;
	}

    public function uninstall() {
		return true;
    }

    private function getState() {
		$allState = [];
		$state = new State();
		$states = $state->find();
		foreach($states as $list) {
			$allState[$list['id']] = $list['name'];
		}

		return $allState;
    }

    public function updateConfig($idConfig, $post) {
		global $DB;

		$status = null;
		$seuil = [];
		foreach($post as $key => $value) {
			if($key == "stock_status") {
				$status = $value;
			} elseif(strpos($key, "seuil_") !== false) {
				$keys = explode("_", $key);
				if($keys[2] == "TYPE") {
					$seuil[$keys[2]][$keys[1]][$keys[3]] = $value;
				} else {
					$seuil[$keys[2]][$keys[1]][$keys[3]][$keys[4]] = $value;
				}
			}
		}

		// Insert / Update status stock
		
		$this->insertUpdateState($status);
		
		

		foreach($seuil as $type => $infos) {
			if($type == "TYPE") {
				foreach($infos as $class => $info) {
					foreach($info as $id => $nbseuil) {
						$sqlVerif = "SELECT id FROM glpi_plugin_stockmanagement_configs WHERE TYPE_ID = $id AND CLASS_TYPE = '$class'";
						$result = $DB->query($sqlVerif);

						if($result->num_rows == 0 && $nbseuil != 0) {
							$sqlInsert = "INSERT INTO glpi_plugin_stockmanagement_configs (CONFIG_ID, TYPE_ID, CLASS_TYPE, ALERT_SEUIL, TYPE)
										VALUES ($idConfig, $id, '$class', $nbseuil, '$type')";
							$result = $DB->query($sqlInsert);
						} elseif($result->num_rows != 0 && $nbseuil == 0) {
							$sqlInsert = "DELETE FROM glpi_plugin_stockmanagement_configs
										WHERE TYPE_ID = $id AND CLASS_TYPE = '$class'";
							$result = $DB->query($sqlInsert);
						} else {
							$sqlInsert = "UPDATE glpi_plugin_stockmanagement_configs
										SET ALERT_SEUIL = $nbseuil WHERE TYPE_ID = $id AND CLASS_TYPE = '$class'";
							$result = $DB->query($sqlInsert);
						}
					}
				}
			} else {
				foreach($infos as $class => $values) {
					foreach($values as $marque => $model) {
						foreach($model as $id => $nbseuil) {
							$sqlVerif = "SELECT id FROM glpi_plugin_stockmanagement_configs WHERE MARQUE_ID = $marque AND MODEL_ID = $id AND CLASS_TYPE = '$class'";
							$result = $DB->query($sqlVerif);

							if($result->num_rows == 0 && $nbseuil != 0) {
								$sqlInsert = "INSERT INTO glpi_plugin_stockmanagement_configs (CONFIG_ID, MARQUE_ID, MODEL_ID, CLASS_TYPE, ALERT_SEUIL, TYPE)
											VALUES ($idConfig, $marque, $id, '$class', $nbseuil, '$type')";
								$result = $DB->query($sqlInsert);
							} elseif($result->num_rows != 0 && $nbseuil == 0) {
								$sqlInsert = "DELETE FROM glpi_plugin_stockmanagement_configs
											WHERE MARQUE_ID = $marque AND MODEL_ID = $id AND CLASS_TYPE = '$class'";
								$result = $DB->query($sqlInsert);
							} else {
								$sqlInsert = "UPDATE glpi_plugin_stockmanagement_configs
											SET ALERT_SEUIL = $nbseuil WHERE MARQUE_ID = $marque AND MODEL_ID = $id AND CLASS_TYPE = '$class'";
								$result = $DB->query($sqlInsert);
							}	
						}
					}
				}
			}
		}

		PluginStockmanagementNotification::sendAlertUpdate();
	}

    private function insertUpdateState($status) {
		global $DB;

		$sqlVerif = "SELECT STATE_ID FROM glpi_plugin_stockmanagement_states WHERE id = 1";
		$result = $DB->query($sqlVerif);

		if($result->num_rows == 0) {
			$sqlInsert = "INSERT INTO glpi_plugin_stockmanagement_states (STATE_ID) VALUES ($status)";
			$result = $DB->query($sqlInsert);
		} else {
			$sqlInsert = "UPDATE glpi_plugin_stockmanagement_states SET STATE_ID = $status WHERE id = 1";
			$result = $DB->query($sqlInsert);
		}
    }

    public function getStatusConfig() {
		global $DB;

		$sql = "SELECT STATE_ID FROM glpi_plugin_stockmanagement_states WHERE id = 1";
		$result = $DB->query($sql);
		if($result->num_rows != 0) {
			foreach($result as $status) {
				return $status['STATE_ID'];
			}
		} else {
			return 0;
		}
    }

    public function getConfig($type) {
		global $DB;
		$config = [];

		$sql = "SELECT * FROM glpi_plugin_stockmanagement_configs WHERE CONFIG_ID = 1 AND TYPE = '$type'";
		$result = $DB->query($sql);

		if($result->num_rows != 0) {
			foreach($result as $infos) {
				if($type == "TYPE") {
					$config[$infos['CLASS_TYPE']][$infos['TYPE_ID']] = $infos['ALERT_SEUIL'];
				} else {
					$config[$infos['CLASS_TYPE']][$infos['MARQUE_ID'].$infos['MODEL_ID']] = $infos['ALERT_SEUIL'];
				}
			}
			return $config;
		} else {
			return 0;
		}
    }

}
