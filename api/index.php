<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2013 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
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

// basic operation function
function executeApiRequest() {
	try {
		// load configuration
		if (!file_exists(API_ROOT.'/config.inc.php')) throw new Exception('No configuration file found!');
		Configuration::load(API_ROOT.'/config.inc.php');
		$configuration = Configuration::getInstance();
		// initialize module managers
		AuthenticationManager::initialize($configuration->getAuthenticationConfig());
		AuthorizationManager::initialize($configuration->getAuthorizationConfig());
		AutologinManager::initialize($configuration->getAutologinConfig());
		ZoneManager::initialize($configuration->getZoneConfig());
		// determine context
		if (isset($_GET['pda_request_path'])) {
			$context = $_GET['pda_request_path'];
		}
		else {
			$context = '';
		}
		// start command execution
		$mainRouter = new MainRouter();
		return $mainRouter->trackByURL($context);
	}
	catch (MethodNotAllowedException $e) {
		header('HTTP/1.0 405 ' . $e->getMessage());
		$returnValue = new stdClass();
		$returnValue->success = false;
		$returnValue->errorMessage = $e->getMessage();
		return $returnValue;
	}
	catch (NoSuchServerException $e) {
		header('HTTP/1.0 404 ' . $e->getMessage());
		$returnValue = new stdClass();
		$returnValue->success = false;
		$returnValue->errorMessage = $e->getMessage();
		return $returnValue;
	}
	catch (Exception $e) {
		header('HTTP/1.1 500 ' . $e->getMessage());
		$returnValue = new stdClass();
		$returnValue->success = false;
		$returnValue->errorMessage = $e->getMessage();
		return $returnValue;
	}
}

// call main function
header('Content-type: text/plain');
$output = executeApiRequest();
echo(json_encode($output));

?>
