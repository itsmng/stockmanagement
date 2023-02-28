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

class PluginStockmanagementNotification extends CommonDBTM {

	/**
	* @var boolean activate the history for the plugin
	*/
	public $dohistory = true;

	/**
	* Return the localized name of the current Type (Pluginstockmanagement)
	*
	* @see CommonGLPI::getTypeName()
	* @param string $nb
	* @return string name of the plugin
	*/
	static function getTypeName($nb = 0) {
		return __("Stock management", 'stockmanagement');
	}

	/**
	* Install stockmanagement notifications.
	*
	* @return array 'success' => true on success
	*/
	static function install($migration) {
		global $DB;

		$template = new NotificationTemplate();
		$found_template = $template->find(['itemtype' => 'PluginStockmanagementNotification']);

		if (empty($found_template)) {
			$template_id = $template->add([
				'name'                     => __('Recurring notification for Stock Management', 'stockmanagement'),
				'comment'                  => "",
				'itemtype'                 => __CLASS__,
			]);

			$content_html = "\n<p>".addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".</p>\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n";

			$translation = new NotificationTemplateTranslation();
			$translation->add([
				'notificationtemplates_id' => $template_id,
				'language'                 => "",
				'subject'                  => __("Stock management", 'stockmanagement'),
				'content_text'             => addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n",
				'content_html'             => $content_html
			]);

			$notification = new Notification();
			$notification_id = $notification->add([
				'name'                     => __('Recurring notification for Stock Management', 'stockmanagement'),
				'comment'                  => "",
				'entities_id'              => 0,
				'is_recursive'             => 1,
				'is_active'                => 1,
				'itemtype'                 => __CLASS__,
				'event'                    => 'sendAlertThreshold',
			]);

			$n_n_template = new Notification_NotificationTemplate();
			$n_n_template->add([
				'notifications_id'         => $notification_id,
				'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
				'notificationtemplates_id' => $template_id,
			]);

			$DB->query('INSERT INTO glpi_notificationtargets (items_id, type, notifications_id) VALUES (1, 1, ' . $notification_id . ');');

			$template_id = $template->add([
				'name'                     => __('Notification update for Stock Management', 'stockmanagement'),
				'comment'                  => "",
				'itemtype'                 => __CLASS__,
			]);

			$content_html = "\n<p>".addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".</p>\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n";

			$translation = new NotificationTemplateTranslation();
			$translation->add([
				'notificationtemplates_id' => $template_id,
				'language'                 => "",
				'subject'                  => __("Stock management", 'stockmanagement'),
				'content_text'             => addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n",
				'content_html'             => $content_html
			]);

			$notification = new Notification();
			$notification_id = $notification->add([
				'name'                     => __('Notification update for Stock Management', 'stockmanagement'),
				'comment'                  => "",
				'entities_id'              => 0,
				'is_recursive'             => 1,
				'is_active'                => 1,
				'itemtype'                 => __CLASS__,
				'event'                    => 'sendAlertThresholdUpdate',
			]);

			$n_n_template = new Notification_NotificationTemplate();
			$n_n_template->add([
				'notifications_id'         => $notification_id,
				'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
				'notificationtemplates_id' => $template_id,
			]);

			$DB->query('INSERT INTO glpi_notificationtargets (items_id, type, notifications_id) VALUES (1, 1, ' . $notification_id . ');');
		}

		return ['success' => true];
	}

	/**
	* Remove stockmanagement notifications from GLPI.
	*
	* @return array 'success' => true on success
	*/
	static function uninstall() {
		global $DB;

		$queries = [];

		// Remove NotificationTargets and Notifications
		$notification = new Notification();
		$result = $notification->find(['itemtype' => 'PluginStockmanagementNotification']);
		foreach ($result as $row) {
			$notification_id = $row['id'];
			$queries[] = "DELETE FROM glpi_notificationtargets WHERE notifications_id = " . $notification_id;
			$queries[] = "DELETE FROM glpi_notifications WHERE id = " . $notification_id;
		}

		// Remove NotificationTemplateTranslations and NotificationTemplates
		$template = new NotificationTemplate();
		$result = $template->find(['itemtype' => 'PluginStockmanagementNotification']);
		foreach ($result as $row) {
			$template_id = $row['id'];
			$queries[] = "DELETE FROM glpi_notificationtemplatetranslations WHERE notificationtemplates_id = " . $template_id;
			$queries[] = "DELETE FROM glpi_notificationtemplates WHERE id = " . $template_id;
		}

		foreach ($queries as $query) {
			$DB->query($query);
		}

		return ['success' => true];
	}

	/**
	* Give localized information about 1 task
	*
	* @param $name of the task
	*
	* @return array of strings
	*/
	static function cronInfo($name) {
		switch ($name) {
			case 'SendAlertMorning' :
				return ['description' => __('Notification for Stock Management', 'stockmanagement')];
			case 'SendAlertAfternoon' :
				return ['description' => __('Notification for Stock Management', 'stockmanagement')];
		}
		return [];
	}

	/**
	* @param $mailing_options
	*/
	static function send($mailing_options, $additional_options) {
		$dashboard = new PluginStockmanagementDashboard();

		$state  = $dashboard->getState();
		$data   = $dashboard->getAllMachines($state['STATE_ID']);
		$data   = $dashboard->verifSeuil($data);
		$data   = $dashboard->refreshTableDashboard($data);

		if(isset($data['NOTIFICATION'])) {
			$mail = new PluginStockmanagementNotificationMail();
			$mail->sendNotification($mailing_options);
		}
	}

	/**
	* Execute 1 task manage by the plugin
	*
	* @param CronTask $task Object of CronTask class for log / stat
	*
	* @return interger
	*    >0 : done
	*    <0 : to be run again (not finished)
	*     0 : nothing to do
	*/
	static function cronSendAlertMorning($task) {
		$task->log(__("Notification(s) sent !", 'stockmanagement'));
		PluginStockmanagementNotificationEvent::raiseEvent('sendAlertThreshold', new self(), $task->fields);
		return 1;
	}

	/**
	* Execute 1 task manage by the plugin
	*
	* @param CronTask $task Object of CronTask class for log / stat
	*
	* @return interger
	*    >0 : done
	*    <0 : to be run again (not finished)
	*     0 : nothing to do
	*/
	static function cronSendAlertAfternoon($task) {
		$task->log(__("Notification(s) sent !", 'stockmanagement'));
		PluginStockmanagementNotificationEvent::raiseEvent('sendAlertThreshold', new self(), $task->fields);
		return 1;
	}

	static function sendAlertUpdate() {
		PluginStockmanagementNotificationEvent::raiseEvent('sendAlertThresholdUpdate', new self());
		return 1;
	}
}
