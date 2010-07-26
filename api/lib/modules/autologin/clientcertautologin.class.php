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
 * @subpackage Autologin
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Autologin
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class SessionAutologin extends AutologinModule {

	private $fieldname = 'username';

	protected function __construct($config) {
		if (isset($config['fieldname'])) {
			$this->fieldname = $config['fieldname'];
		}
		if (isset($config['sessionname'])) {
			session_name($config['sessionname']);
		}
		session_start();
	}

	public static function getInstance($config) {
		return new SessionAutologin($config);
	}

	public function getUser() {
		if (isset($_SESSION[$this->fieldname])) {
			return new User($_SESSION[$this->fieldname]);
		}
		else {
			return null;
		}
	}

	public function notifyLogin(User $user) {
		$_SESSION[$this->fieldname] = $user->getUsername();
		return true;
	}

	public function notifyLogout() {
		unset($_SESSION[$this->fieldname]);
		return true;
	}

}

?>