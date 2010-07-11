/*
 * This file is part of phpDNSAdmin.
 * (c) 2010 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
 *
 * phpDNSAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpDNSAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpDNSAdmin. If not, see <http://www.gnu.org/licenses/>.
 */

function login(username,password) {
	$.ajax({
		url: 'api/status',
		data: {
			username: username,
			password: password
		},
		type: 'POST',
		success: function(data) {
			if (data.loggedIn == true) {
				performLogin();
			}
			else {
				performLogout();
			}
		},
		error: function(request,errorType,exception) {

		}
	});
}

function logout() {
	$.ajax({
		url: 'api/status',
		type: 'POST',
		data: {
			username: ''
		},
		dataType: 'json',
		success: function(data) {
			$("#loadingDialog").dialog("option","beforeclose","");
			$("#loadingDialog").dialog("close");
			if (data.loggedIn == true) {
				performLogin();
			}
			else {
				performLogout();
			}
		},
		error: function(request,errorType,exception) {

		}
	});
}

function performLogin() {
	$("#loginDialog").dialog("close");
}

function performLogout() {
	$("#loginDialog").dialog("open");
	//$("#zoneSelector").jstree("create_node",$("#zoneSelector"),'inside',{data:'test234'});
}

function updateStatus() {
	$.ajax({
		url: 'api/status',
		type: 'GET',
		success: function(data) {
			$("#loadingDialog").dialog("option","beforeclose","");
			$("#loadingDialog").dialog("close");
			if (data.loggedIn == true) {
				performLogin();
			}
			else {
				performLogout();
			}
		},
		error: function(request,errorType,exception) {

		}
	});
}

// immediate execution
$(document).ready(function() {
	// set ajax default options
	$.ajaxSetup({
		type: 'GET',
		dataType: 'json'
	});
	//
	$("#loadingDialog").dialog({
		beforeclose: function() {return false},
		closeOnEscape: false,
		draggable: false,
		modal: true,
		resizable: false
	});
	updateStatus();
	$("#loginDialog").dialog({
		autoOpen: false,
		//beforeclose: function() {return false},
		buttons: {
			"Login": function() {
				username = $('#usernameInput').attr('value');
				password = $('#passwordInput').attr('value');
				$('#passwordInput').attr('value','')
				login(username,password);
			}
		},
		closeOnEscape: false,
		draggable: false,
		modal: true,
		resizable: false,
		width: '400px'
	});
	$("#mainPanel").tabs();
	$("#zoneSelector").jstree({
		core: {
			animation: 100
		},
		json_data: {
			data: [
				{
					data: 'com',
					children: ['google']
				},
				{
					data: 'net',
					children: [{data:'matthias-lohr',children: ['home']},'sourceforge']
				},
				{
					data: 'org'
				}
			]
		},
		plugins: ['json_data','ui','themeroller']
	});
});