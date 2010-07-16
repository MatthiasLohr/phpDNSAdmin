<?php

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

?>
<html>
	<head>
		<title>phpDNSAdmin</title>
		<link rel="stylesheet" href="css/jquery/jquery.css" type="text/css" />
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.2.min.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		<script type="text/javascript" src="js/jquery.jstree.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="js/pda.system.js"></script>
	</head>
	<body>
		<div id="mainPanel">
			<ul>
				<li><a href="#zoneAdminPanel">zone</a></li>
				<li><a href="#userAdminPanel">user administration</a></li>
				<li><a onclick="javascript:logout();" href="#">logout</a></li>
			</ul>

			<div id="zoneAdminPanel">
				<div id="zoneSelector" style="padding: 3px;">

				</div>
				
			</div>
			<div id="userAdminPanel">

			</div>
		</div>
		<!-- dialogs -->
		<div id="loadingDialog" title="Loading...">
			<div class="ui-autocomplete-loading">Loading, please wait...</div>
		</div>

		<div id="loginDialog" title="Login" style="display: none;">
			<div id="loginErrorText" style="padding: 0pt 0.7em; visibility: hidden;" class="ui-state-error ui-corner-all">
				<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
				<strong>Error:</strong> Invalid login credentials!</p>
			</div>
			<p>Please enter your username/password combination to log in:</p>
			<p>
				username: <input type="text" id="usernameInput" name="username" /><br />
				password: <input type="password" id="passwordInput" name="password" />
			</p>
		</div>

		<div id="zoneProperties">

		</div>

		<div id="recordProperties">

		</div>
	</body>
</html>