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

include ("../../../inc/includes.php");

$plugin = new Plugin();
if (!$plugin->isInstalled('stockmanagement') || !$plugin->isActivated('stockmanagement')) {
	global $CFG_GLPI;
	echo '<div class=\'center\'><br><br><img src=\'' . $CFG_GLPI['root_doc'] . '/pics/warning.png\' alt=\'warning\'><br><br>';
	echo '<b>' . __("Plugin not installed or activated", "stockmanagement") . '</b></div>';
}

$config = new PluginStockmanagementConfig();

if (isset($_POST["add"])) {
	Html::header(__("Stock management", "stockmanagement"), $_SERVER['PHP_SELF'], "tools", "PluginStockmanagementConfig","stockmanagement");
	Session::checkRight("plugin_stockmanagement_config", CREATE);
	$config->updateConfig(1, $_POST);

	Session::addMessageAfterRedirect(__("Configuration saved with success !", "stockmanagement"), true);
	Html::back();
} else {
	Html::header(__("Stock management", "stockmanagement"), $_SERVER['PHP_SELF'], "tools", "PluginStockmanagementConfig","stockmanagement");
	$config->showForm(1);
	Html::footer();
}
