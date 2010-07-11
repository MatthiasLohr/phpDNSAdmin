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

	public function rrtypes($type = null) {
		if ($type === null) {
			// list all ResourceRecord types
			return ResourceRecord::listTypes();
		}
		else {
			$result = new stdClass();
			$className = ResourceRecord::getTypeClassName($type);
			if ($className !== null) {
				$result->type = $type;
				$record = new $className('@','',86400);
				$result->fields = $record->listFields();
			}
			return $result;
		}
	}

	public function servers($serverId = null) {
		// check for login
		$result = new stdClass();
		$autologin = AutologinManager::getInstance();
		if ($autologin->getUser() === null) {
			$result->error = 'Please log in first!';
			return $result;
		}
		// work request
		if ($serverId === null) {

		}
		else {

		}
		return $result;
	}

	public function status() {
		$result = new stdClass();
		$autologin = AutologinManager::getInstance();
		$authentication = AuthenticationManager::getInstance();
		switch (RequestRouter::getRequestType()) {
			case 'POST':
				$data = RequestRouter::getJsonData();
				if ($data !== null) {
					if (isset($data->username) && isset($data->password)) {
						$user = new User($data->username);
						if ($authentication->userCheckPassword($user, $data->password)) {
							$autologin->notifyLogin($user);
						}
					}
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