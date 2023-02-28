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

function plugin_init_stockmanagement() {
   global $PLUGIN_HOOKS;

   //register class
   $PLUGIN_HOOKS['change_profile']['stockmanagement'] = array(PluginStockmanagementProfile::class,'changeProfile');
   Plugin::registerClass(PluginStockmanagementProfile::class, array('addtabon' => 'Profile'));
   Plugin::registerClass(PluginStockmanagementConfig::class, ['addtabon' => 'Config']);

   if(Session::haveRight("plugin_stockmanagement_dashboard", READ)) {
      Plugin::registerClass(PluginStockmanagementDashboard::class, ['addtabon' => ['Central']]);
   }

   Plugin::registerClass('PluginStockmanagementNotification', ['notificationtemplates_types' => true]);

   if(Session::haveRight("plugin_stockmanagement_config", CREATE)) {
      $PLUGIN_HOOKS['menu_toadd']['stockmanagement'] = array('tools' => 'PluginStockmanagementConfig');
   }

   $PLUGIN_HOOKS['add_javascript']['stockmanagement'] = array("js/function.js");

   // CSRF Compliant do not touch
   $PLUGIN_HOOKS['csrf_compliant']['stockmanagement'] = true;
}

/**
 * @return array
 */
function plugin_version_stockmanagement() {
	return array(
		'name'           => __('Stock management', 'stockmanagement'),
		'version'        => '1.0',
		'license'        => 'AGPLv3+',
		'author'         => 'ITSM Dev Team',
		'homepage'       => 'https://github.com/itsmng/stockmanagement',
		'minGlpiVersion' => '9.4'
	);
}

/**
 * @return bool
 */
function plugin_stockmanagement_check_prerequisites() {
	if (version_compare(ITSM_VERSION,'1.0','lt')) {
		echo "This plugin requires ITSM >= 1.0";
		return false;
	}
	return true;
}


/**
 * @param bool $verbose
 * @return bool
 */
function plugin_stockmanagement_check_config($verbose=false) {
   	return true;
}