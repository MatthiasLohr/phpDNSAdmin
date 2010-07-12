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

define('API_ROOT',dirname(__FILE__));
require_once(API_ROOT.'/lib/autoload.inc.php');
error_reporting(E_ALL | E_NOTICE);

// load config
if (file_exists(API_ROOT.'/config.inc.php')) {
	Configuration::load(API_ROOT.'/config.inc.php');
}
else {
	die('config.inc.php not found!');
}

$configuration = Configuration::getInstance();

// initialize module managers
AuthenticationManager::initialize($configuration->getAuthenticationConfig());
AuthorizationManager::initialize($configuration->getAuthorizationConfig());
AutologinManager::initialize($configuration->getAutologinConfig());
ZoneManager::initialize($configuration->getZoneConfig());

// prepare path and start script execution
try {
	$router = new MainRouter();
	if (isset($_GET['pda_request_path']) && strlen($_GET['pda_request_path']) > 0) {
		if (substr($_GET['pda_request_path'],-1) == '/') {
			$_GET['pda_request_path'] = substr($_GET['pda_request_path'],0,-1);
		}
		$pdaPath = explode('/',$_GET['pda_request_path']);
	}
	else {
		$pdaPath = array();
	}
	$result = $router->track($pdaPath);
	header('Content-type: text/plain');
	echo(json_encode($result));
}
catch (RequestRoutingException $e) {
	$result = new stdClass();
	$result->error = 'Routing error! '.$e->getMessage();
	echo(json_encode($result));
}

?>