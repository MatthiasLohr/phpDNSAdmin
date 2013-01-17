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
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: status');
		return new stdClass();
	}

	public function rrtypes($type = null) {
		$result = new stdClass();
		if ($type === null) {
			// list all ResourceRecord types
			$result->rrtypes = ResourceRecord::listTypes();
			$result->success = is_array($result->rrtypes);
		}
		else {
			$className = ResourceRecord::getClassByType($type);
			if ($className !== null) {
				$result->success = true;
				$rrtype = new stdClass();
				$rrtype->type = $type;
				$rrtype->fields = array();
				$fields = call_user_func(array($className,'listFields'));
				foreach ($fields as $fieldName => $simpleType) {
					$tmp = new stdClass();
					$tmp->name = $fieldName;
					$tmp->simpletype = $simpleType;
					$rrtype->fields[] = $tmp;
				}
				$result->rrtype = $rrtype;
			}
			else {
				$result->success = false;
			}
		}
		return $result;
	}

	public function servers($sysname = null) {
		// check for login
		$servers = new stdClass();
		$autologin = AutologinManager::getInstance();
		if ($autologin->getUser() === null) throw new AuthenticationException('Please log in first!');
		// work request
		$zonemanager = ZoneManager::getInstance();
		if ($sysname === null) {
			// list all servers
			foreach($zonemanager->listModules() as $module) {
				$sysname = $module->sysname;
				$server = new stdClass();
				$server->name = $module->name;
				$servers->$sysname = $server;
			}
		}
		else {
			$zoneModule = $zonemanager->getModuleBySysname($sysname);
			if ($zoneModule === null) {
				throw new NoSuchServerException('No server with this sysname found!');
			}
			else {
				$serverRouter = new ServerRouter($zoneModule);
				return $serverRouter->track($this->routingPath);
			}
		}
		$result = new stdClass();
		$result->servers = $servers;
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
							$result->loggedIn = true;
							$result->success = true;
						}
						else {
							$result->loggedIn = false;
							$result->success = false;
						}
					}
					catch (NoSuchUserException $e) {
						$result->loggedIn = false;
						$result->success = false;
					}
				}
			case 'GET':
				$user = $autologin->getUser();
				if ($user === null) {
					$result->loggedIn = false;
					$result->success = true;
				}
				else {
					$result->loggedIn = true;
					$result->username = $user->getUsername();
					$result->success = true;
				}
				break;
		}
		return $result;
	}
}

?>