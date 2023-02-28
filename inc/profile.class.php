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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginStockmanagementProfile extends CommonDBTM {

	static $rightname = "profile";

	static $all_profile_rights = array(
		'plugin_stockmanagement_config',
		'plugin_stockmanagement_dashboard',
	);

	static function canCreate() {
		if (isset($_SESSION["profile"])) {
			return ($_SESSION["profile"]['stockmanagement'] == 'w');
		}
		return false;
	}

	static function canView() {
		if (isset($_SESSION["profile"])) {
			return ($_SESSION["profile"]['stockmanagement'] == 'w' || $_SESSION["profile"]['stockmanagement'] == 'r');
		}
		return false;
	}

	static function createAdminAccess($ID) {
		$myProf = new self();
		if (!$myProf->getFromDB($ID)) {
			$myProf->add(array('id' => $ID, 'right' => 'w'));
		}
	}

	public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
		if ($item->getType() == 'Profile') {
			return PluginStockmanagementConfig::getTypeName(0);
		}
		return '';
	}

	static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0){
		if ($item->getType() == 'Profile') {
			$id = $item->getID();
			$prof = new self();

			//In case there's no right for this profile, create it
            foreach (self::getAllRights() as $right) {
               self::addRight($id, [$right['field'] => 0]);
            }
			$prof->showForm($id);
		}

		return true;
	}

	

	/**
	 * Add right for current session if no id current session is selected
	 *
	 * @param $profiles_id
	 * @param $right_value
	 */
	static function addRight($profiles_id, $right_value){
		$profileRight = new ProfileRight();
		foreach ($right_value as $right => $value) {
			if (!countElementsInTable('glpi_profilerights', ['profiles_id' => $profiles_id, 'name' => $right])) {
				$myright['profiles_id'] = $profiles_id;
				$myright['name']        = $right;
				$myright['rights']      = $value;
				$profileRight->add($myright);
				//Add right to the current session
				$_SESSION['glpiactiveprofile'][$right] = $value;
			}
		}
	}

	static function changeProfile() {
		$prof = new self();
		if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
			foreach (self::$all_profile_rights as $profile_name) {
				$_SESSION[$profile_name] = $prof->fields;
			}	
		} else {
			foreach (self::$all_profile_rights as $profile_name) {
				unset($_SESSION[$profile_name]);
			}
		}
   }


	/**
	 *  Create form for search
	 *
	 * @param int $profiles_id
	 * @param bool|true $openform
	 * @param bool|true $closeform
	 */
	function showForm($profiles_id = 0, $openform = true, $closeform = true){
		global $DB, $CFG_GLPI;

		if (!Session::haveRight("profile",READ)) {
         	return false;
      	}

		echo "<div class='firstbloc'>";
		if (($canedit = Session::haveRight('profile', CREATE)) && $openform) {
			$profile = new Profile();

			echo "<form method='post' action='" . $profile->getFormURL() . "'>";
		}
		$profile = new Profile();
		$profile->getFromDB($profiles_id);

		$config_right = $this->getAllRights();
		$profile->displayRightsChoiceMatrix($config_right, array(
			'default_class' => 'tab_bg_2',
			'title' => __('General')
		));

		if ($canedit && $closeform) {
			echo "<div class='center'>";
			echo Html::hidden('id', array('value' => $profiles_id));
			echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
			echo "</div>";
			Html::closeForm();
		}
		echo "</div>";
	}

	static function getAllRights(){
		$rights = array(
			array(
				'itemtype' => 'PluginStockmanagementConfig',
				'label' => __('Setup'),
				'field' => 'plugin_stockmanagement_config',
				'rights' => [READ=>__('Read'), CREATE => __('Update')],
				'default' => 31
			),
			array(
				'itemtype' => 'PluginStockmanagementDashboard',
				'label' => __('Dashboard'),
				'field' => 'plugin_stockmanagement_dashboard',
				'rights' => [READ=>__('Read')],
				'default' => 1
			),
		);

		return $rights;
	}
}
