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

function reloadType() {
	var searchType = $('select[name=search_type] option:selected').val();
	var ivalue = $('#ivalue').val();

	var RegexUrl = /^(.*)front\/.*\.php/;
	var RegexUrlRes = RegexUrl.exec(window.location.pathname);
	var root_glpi = RegexUrlRes[1];
	var php_file = root_glpi + "plugins/stockmanagement/ajax/reload.php?type=" + searchType;
	console.log(php_file);
	$.ajax({
		type: 'GET',
		url: php_file,
		timeout: 3000,
		error: function () {
			alert('Error');
		},
		success: function (data) {
			for (var i = 1; i <= ivalue; i++) {
				$('#search_replace_type'+i).remove();
				$('#search_replace_type').replaceWith(data);
			}
		}
	});
}

function reloadMarque() {
	var searchMarque = $('select[name=search_marque] option:selected').val();
	var searchModel = $('select[name=search_model] option:selected').val();
	var ivalue = $('#ivalue').val();

	var RegexUrl = /^(.*)front\/.*\.php/;
	var RegexUrlRes = RegexUrl.exec(window.location.pathname);
	var root_glpi = RegexUrlRes[1];
	var php_file = root_glpi + "plugins/stockmanagement/ajax/reload.php?marque=" + searchMarque + "&model=" + searchModel;
	console.log(php_file);
	$.ajax({
		type: 'GET',
		url: php_file,
		timeout: 3000,
		error: function () {
			alert('Error');
		},
		success: function (data) {
			for (var i = 1; i <= ivalue; i++) {
				$('#search_replace_marque'+i).remove();
				$('#search_replace_marque').replaceWith(data);
			}
		}
	});
}

function reset() {
	location.reload();
}