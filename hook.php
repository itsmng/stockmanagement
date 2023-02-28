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

function plugin_stockmanagement_install() {
    global $DB;

    $migration = new Migration(180);

    if (!$DB->tableExists('glpi_plugin_stockmanagement_configs')) {
		$query = "CREATE TABLE `glpi_plugin_stockmanagement_configs` (
			`id`              INT(11) NOT NULL AUTO_INCREMENT,
			`CONFIG_ID`       INT(2) NOT NULL DEFAULT 1,
			`TYPE_ID`         INT(11) DEFAULT NULL,
			`MARQUE_ID`       INT(11) DEFAULT  NULL,
			`MODEL_ID`        INT(11) DEFAULT NULL,
			`TYPE`            varchar(255) NOT NULL,
			`CLASS_TYPE`      varchar(255) DEFAULT NULL,
			`ALERT_SEUIL`     INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
    }

    if (!$DB->tableExists('glpi_plugin_stockmanagement_states')) {
		$query = "CREATE TABLE `glpi_plugin_stockmanagement_states` (
			`id`              INT(11) NOT NULL AUTO_INCREMENT,
			`STATE_ID`        INT(11) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
    }

    if (!$DB->tableExists('glpi_plugin_stockmanagement_dashboard')) {
		$query = "CREATE TABLE `glpi_plugin_stockmanagement_dashboard` (
			`id`              INT(11) NOT NULL AUTO_INCREMENT,
			`TYPE`            varchar(255) DEFAULT NULL,
			`MARQUE`           varchar(255) DEFAULT NULL,
			`MODEL`           varchar(255) DEFAULT NULL,
			`NB`              INT(11) NOT NULL,
			`SEUIL`           INT(11) NOT NULL,
			`NOTIF`           DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
    }

	if (!$DB->tableExists('glpi_plugin_stockmanagement_notifications')) {
		$query = "CREATE TABLE `glpi_plugin_stockmanagement_notifications` (
			`id`              INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
    }

    if (!$DB->tableExists("glpi_plugin_stockmanagement_profiles")) {  
        $query2 = "CREATE TABLE `glpi_plugin_stockmanagement_profiles` (
			`id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
			`right` char(1) collate utf8_unicode_ci default NULL,
			PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query2, $DB->error());

        include_once(GLPI_ROOT."/plugins/stockmanagement/inc/profile.class.php");
        PluginStockmanagementProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
        
        foreach (PluginStockmanagementProfile::getAllRights() as $right) {
            PluginStockmanagementProfile::addRight($_SESSION['glpiactiveprofile']['id'], [$right['field'] => $right['default']]);
        }
    } else $DB->queryOrDie("ALTER TABLE `glpi_plugin_stockmanagement_profiles` ENGINE = InnoDB", $DB->error());

    // No autoload when plugin is not activated
    require 'inc/config.class.php';
    $plugin_config = new PluginStockmanagementConfig();
    $plugin_config->install($migration);

    // == Install notifications
	require_once "inc/notification.class.php";
	PluginStockmanagementNotification::install($migration);
	CronTask::Register('PluginStockmanagementNotification', 'SendAlertMorning', DAY_TIMESTAMP);
	CronTask::Register('PluginStockmanagementNotification', 'SendAlertAfternoon', DAY_TIMESTAMP);

    $migration->executeMigration();

    return true;
}


function plugin_stockmanagement_uninstall() {
    global $DB;

    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_stockmanagement_configs`");
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_stockmanagement_states`");
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_stockmanagement_dashboard`");
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_stockmanagement_notifications`");
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_stockmanagement_profiles`");

    // No autoload when plugin is not activated
    require 'inc/config.class.php';
    $plugin_config = new PluginStockmanagementConfig();
    $plugin_config->uninstall();

    require_once "inc/notification.class.php";
    PluginStockmanagementNotification::uninstall();

    foreach (PluginStockmanagementProfile::getAllRights() as $right) {
		$query = "DELETE FROM `glpi_profilerights` WHERE `name` = '".$right['field']."'";
		$DB->query($query);

		if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
			unset($_SESSION['glpiactiveprofile'][$right['field']]);
		}
    }

    return true;
}

 
 