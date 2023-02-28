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

/**
 *  NotificationMailing class extends phpmail and implements the NotificationInterface
**/
class PluginStockmanagementNotificationMail extends NotificationMailing {

	/**
	* @param $options   array
	**/
	function sendNotification($options = []) {
		
		$mmail = new GLPIMailer();
		$mmail->AddCustomHeader("Auto-Submitted: auto-generated");
		// For exchange
		$mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");

		$mmail->SetFrom($options['from'], $options['fromname'], false);

		if ($options['replyto']) {
			$mmail->AddReplyTo($options['replyto'], $options['replytoname']);
		}
		$mmail->Subject  = $options['subject'];

		if (empty($options['content_html'])) {
			$mmail->isHTML(false);
			$mmail->Body = $options['content_text'];
		} else {
			$mmail->isHTML(true);
			$mmail->Body    = $options['content_html'];
			$mmail->AltBody = $options['content_text'];
		}

		$mmail->AddAddress($options['to'], $options['toname']);

		if (!empty($options['messageid'])) {
			$mmail->MessageID = "<".$options['messageid'].">";
		}

		$messageerror = __('Error in sending the email');

		if (!$mmail->Send()) {
			$senderror = true;
			Session::addMessageAfterRedirect($messageerror."<br>".$mmail->ErrorInfo, true);
		} else {
			//TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
			Toolbox::logInFile("mail", sprintf(__('%1$s: %2$s'), sprintf(__('An email was sent to %s'), $options['to']), $options['subject']."\n"));
			
			$dashboard = new PluginStockmanagementDashboard();

			$state  = $dashboard->getState();
			$data   = $dashboard->getAllMachines($state['STATE_ID']);
			$data   = $dashboard->verifSeuil($data);
			
			foreach($data as $type => $values) {
				foreach($values as $keu => $value) {
					if(isset($value['NOTIF'])) {
						if($type == "TYPE") {
							$dashboard->updateNotif( $value['name'], null, null, $value['NOTIF']);
						} else {
							$dashboard->updateNotif( null, $value['marque'], $value['model'], $value['NOTIF']);
						}
					}
				}
			}
		}

		$mmail->ClearAddresses();
		return true;
	}

}

