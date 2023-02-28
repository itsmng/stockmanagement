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

class PluginStockmanagementNotificationTargetNotification extends NotificationTarget {

	var $additionalData;

	function getEvents() {
		return [
			'sendAlertThreshold' => __('Stock management', 'stockmanagement'),
			'sendAlertThresholdUpdate' => __('Stock management update', 'stockmanagement')
		];
	}

	function getTags() {
		$this->addTagToList([
			'tag'   => 'stockmanagement.listtype',
			'label' => __('List'),
			'value' => true
		]);
		$this->addTagToList([
			'tag'   => 'stockmanagement.listmanufacturer',
			'label' => __('List'),
			'value' => true
		]);

		asort($this->tag_descriptions);
	}

	function addDataForTemplate($event, $options = []) {
		$listtype = "";
		$listmanufacturer = "";
		$dashboard = new PluginStockmanagementDashboard();

		$state  = $dashboard->getState();

		$data   = $dashboard->getAllMachines($state['STATE_ID']);
		$data   = $dashboard->verifSeuil($data);

		foreach($data as $type => $values) {
			foreach($values as $key => $value) {
				if(isset($value['NOTIF'])) {
					if($type == "TYPE") {
						$listtype .= __("Type")." : ".$value['name']."\n".__("Fixed threshold", "stockmanagement")." : ".$value['ALERT_SEUIL']."\n\n".__("Number in stock", "stockmanagement")." : ".$value['NB']."\n\n";
					} else {
						$listmanufacturer .= __("Manufacturer")." : ".$value['marque']."\n".__("Model")." : ".$value['model']."\n".__("Fixed threshold", "stockmanagement")." : ".$value['ALERT_SEUIL']."\n\n".__("Number in stock", "stockmanagement")." : ".$value['NB']."\n\n";
					}
				}
			}
		}

		$this->data['##lang.stockmanagement.listtype##'] = __('List per type', 'stockmanagement');
		$this->data['##stockmanagement.listtype##']      = $listtype;
		$this->data['##lang.stockmanagement.listmanufacturer##'] = __('List per manufacturer and model', 'stockmanagement');
		$this->data['##stockmanagement.listmanufacturer##']      = $listmanufacturer;
	}
}
