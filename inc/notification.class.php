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

class PluginStockmanagementNotification extends CommonDBTM
{
    private static $pendingNotificationData = null;

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
    public static function getTypeName($nb = 0)
    {
        return __("Stock management", 'stockmanagement');
    }

    /**
    * Install stockmanagement notifications.
    *
    * @return array 'success' => true on success
    */
    public static function install($migration)
    {
        global $DB;

        $template = new NotificationTemplate();
        if (!empty($template->find(['itemtype' => __CLASS__]))) {
            return ['success' => true];
        }

        $content_text = addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n";
        $content_html = "\n<p>".addslashes(__("Some machines have exceeded the threshold", "stockmanagement")).".</p>\n ##stockmanagement.listtype##\n\n##stockmanagement.listmanufacturer##\n\n";

        $notifications = [
            'sendAlertThreshold' => __('Recurring notification for Stock Management', 'stockmanagement'),
            'sendAlertThresholdUpdate' => __('Notification update for Stock Management', 'stockmanagement'),
        ];

        foreach ($notifications as $event => $name) {
            $template_id = $template->add([
                'name'     => $name,
                'comment'  => "",
                'itemtype' => __CLASS__,
            ]);

            $translation = new NotificationTemplateTranslation();
            $translation->add([
                'notificationtemplates_id' => $template_id,
                'language'                 => "",
                'subject'                  => __("Stock management", 'stockmanagement'),
                'content_text'             => $content_text,
                'content_html'             => $content_html
            ]);

            $notification = new Notification();
            $notification_id = $notification->add([
                'name'         => $name,
                'comment'      => "",
                'entities_id'  => 0,
                'is_recursive' => 1,
                'is_active'    => 1,
                'itemtype'     => __CLASS__,
                'event'        => $event,
            ]);

            $n_n_template = new Notification_NotificationTemplate();
            $n_n_template->add([
                'notifications_id'         => $notification_id,
                'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
                'notificationtemplates_id' => $template_id,
            ]);

            $DB->insert('glpi_notificationtargets', [
                'items_id'         => Notification::GLOBAL_ADMINISTRATOR,
                'type'             => Notification::USER_TYPE,
                'notifications_id' => (int) $notification_id,
            ]);
        }

        return ['success' => true];
    }

    /**
    * Remove stockmanagement notifications from GLPI.
    *
    * @return array 'success' => true on success
    */
    public static function uninstall()
    {
        global $DB;

        // Remove NotificationTargets and Notifications
        $notification = new Notification();
        $result = $notification->find(['itemtype' => 'PluginStockmanagementNotification']);
        foreach ($result as $row) {
            $notification_id = (int) $row['id'];
            $DB->delete('glpi_notificationtargets', ['notifications_id' => $notification_id]);
            $DB->delete('glpi_notifications', ['id' => $notification_id]);
        }

        // Remove NotificationTemplateTranslations and NotificationTemplates
        $template = new NotificationTemplate();
        $result = $template->find(['itemtype' => 'PluginStockmanagementNotification']);
        foreach ($result as $row) {
            $template_id = (int) $row['id'];
            $DB->delete('glpi_notificationtemplatetranslations', ['notificationtemplates_id' => $template_id]);
            $DB->delete('glpi_notificationtemplates', ['id' => $template_id]);
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
    public static function cronInfo($name)
    {
        switch ($name) {
            case 'SendAlertMorning':
                return ['description' => __('Notification for Stock Management', 'stockmanagement')];
            case 'SendAlertAfternoon':
                return ['description' => __('Notification for Stock Management', 'stockmanagement')];
        }
        return [];
    }

    /**
    * @param $mailing_options
    */
    public static function send($mailing_options, $additional_options)
    {
        if (self::$pendingNotificationData === null) {
            self::$pendingNotificationData = self::refreshDashboardData();
        }

        if (isset(self::$pendingNotificationData['NOTIFICATION'])) {
            $mail = new PluginStockmanagementNotificationMail();
            $mail->sendNotification($mailing_options);
        }
    }

    /**
    * Execute 1 task manage by the plugin
    *
    * @param CronTask $task Object of CronTask class for log / stat
    *
    * @return integer
    *    >0 : done
    *    <0 : to be run again (not finished)
    *     0 : nothing to do
    */
    public static function cronSendAlertMorning($task)
    {
        self::$pendingNotificationData = self::refreshDashboardData();

        if (!isset(self::$pendingNotificationData['NOTIFICATION'])) {
            self::$pendingNotificationData = null;
            return 0;
        }

        $task->log(__("Notification(s) sent !", 'stockmanagement'));
        self::raiseAlertEvent('sendAlertThreshold', $task->fields);
        self::$pendingNotificationData = null;
        return 1;
    }

    /**
    * Execute 1 task manage by the plugin
    *
    * @param CronTask $task Object of CronTask class for log / stat
    *
    * @return integer
    *    >0 : done
    *    <0 : to be run again (not finished)
    *     0 : nothing to do
    */
    public static function cronSendAlertAfternoon($task)
    {
        self::$pendingNotificationData = self::refreshDashboardData();

        if (!isset(self::$pendingNotificationData['NOTIFICATION'])) {
            self::$pendingNotificationData = null;
            return 0;
        }

        $task->log(__("Notification(s) sent !", 'stockmanagement'));
        self::raiseAlertEvent('sendAlertThreshold', $task->fields);
        self::$pendingNotificationData = null;
        return 1;
    }

    public static function sendAlertUpdate()
    {
        self::$pendingNotificationData = self::refreshDashboardData();

        if (!isset(self::$pendingNotificationData['NOTIFICATION'])) {
            self::$pendingNotificationData = null;
            return 0;
        }

        self::raiseAlertEvent('sendAlertThresholdUpdate');
        self::$pendingNotificationData = null;
        return 1;
    }

    private static function refreshDashboardData()
    {
        $dashboard = new PluginStockmanagementDashboard();

        $state  = $dashboard->getState();
        $data   = $dashboard->getAllMachines($state['STATE_ID']);
        $data   = $dashboard->verifSeuil($data);

        return $dashboard->refreshTableDashboard($data);
    }

    private static function raiseAlertEvent($event, array $options = [])
    {
        foreach (self::getStockEntityIDs() as $entities_id) {
            PluginStockmanagementNotificationEvent::raiseEvent(
                $event,
                new self(),
                $options + ['entities_id' => $entities_id]
            );
        }
    }

    private static function getStockEntityIDs()
    {
        global $DB;

        $dashboard = new PluginStockmanagementDashboard();
        $state = (int) $dashboard->getState()['STATE_ID'];
        $entities = [];
        $tables = [
            'glpi_computers',
            'glpi_monitors',
            'glpi_networkequipments',
            'glpi_peripherals',
            'glpi_printers',
            'glpi_phones',
        ];

        foreach ($tables as $table) {
            $iterator = $DB->request([
                'SELECT'   => ['entities_id'],
                'DISTINCT' => true,
                'FROM'     => $table,
                'WHERE'    => [
                    'states_id'   => $state,
                    'is_template' => 0,
                ],
            ]);

            foreach ($iterator as $row) {
                $entities[(int) $row['entities_id']] = (int) $row['entities_id'];
            }
        }

        return $entities ?: [0];
    }
}
