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
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class User {

	/** @var string username */
	private $username = null;

	/**
	 * Constructor. Create a new user instance
	 *
	 * @param string $username
	 */
	public function __construct($username) {
		$this->username = $username;
	}

	/**
	 * Return the username
	 *
	 * @return string username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Is the user admin?
	 *
	 * @return boolean yes/no
	 */
	public function isAdmin() {
		$authorization = AuthorizationManager::getInstance();
	}

}

?>