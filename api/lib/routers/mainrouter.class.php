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

/**
 * @package phpDNSAdmin
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class MainRouter extends RequestRouter {

	public function __default() {
		header('Location: status');
		exit;
	}

	public function rrtypes($type = null) {
		if ($type === null) {
			// list all ResourceRecord types
			return ResourceRecord::listTypes();
		}
		else {
			$result = new stdClass();
			$className = ResourceRecord::getClassByType($type);
			if ($className !== null) {
				$result->type = $type;
				$result->fields = call_user_func(array($className,'listFields'));
			}
			return $result;
		}
	}

	public function servers($sysname = null) {
		// check for login
		$result = new stdClass();
		$autologin = AutologinManager::getInstance();
		if ($autologin->getUser() === null) {
			$result->error = 'Please log in first!';
			return $result;
		}
		// work request
		$zonemanager = ZoneManager::getInstance();
		if ($sysname === null) {
			// list all servers
			foreach($zonemanager->listModules() as $module) {
				$sysname = $module->sysname;
				$server = new stdClass();
				$server->name = $module->name;
				$result->$sysname = $server;
			}
		}
		else {
			$zoneModule = $zonemanager->getModuleBySysname($sysname);
			if ($zoneModule === null) {
				$result->error = 'No server with this sysname found!';
			}
			else {
				$serverRouter = new ServerRouter($zoneModule);
				return $serverRouter->track($this->routingPath);
			}
		}
		return $result;
	}

	public function simpletypes($type) {
		$result = new stdClass();
		if (!class_exists($type) || !is_subclass_of($type,'Simpletype')) {
			$result->error = $type.' is no Simpletype!';
			return $result;
		}
		if (RequestRouter::getRequestType() == 'POST') {
			$data = RequestRouter::getRequestData();
			if ($data === null || !isset($data['value'])) return $result;
			if (is_array($data['value'])) {
				$result->value = array();
				foreach ($data['value'] as $key => $value) {
					$result->value[$key] = new stdClass();
					$typeInstance = new $type($value);
					$result->value[$key]->valid = $typeInstance->isValid();
				}
			}
			else {
				$typeInstance = new $type($data['value']);
				$result->valid = $typeInstance->isValid();
			}
		}
		return $result;
	}

	public function status() {
		$result = new stdClass();
		$autologin = AutologinManager::getInstance();
		$authentication = AuthenticationManager::getInstance();
		switch (RequestRouter::getRequestType()) {
			case 'POST':
				$data = RequestRouter::getRequestData();
				if (isset($data['username']) && strlen($data['username']) == 0) {
					$autologin->notifyLogout();
				}
				elseif (isset($data['username']) && isset($data['password'])) {
					$user = new User($data['username']);
					try {
						if ($authentication->userCheckPassword($user,$data['password'])) {
							$autologin->notifyLogin($user);
						}
					}
					catch (NoSuchUserException $e) {}
				}
			case 'GET':
				$user = $autologin->getUser();
				if ($user === null) {
					$result->loggedIn = false;
				}
				else {
					$result->loggedIn = true;
					$result->username = $user->getUsername();
				}
				break;
		}
		return $result;
	}
}

?>