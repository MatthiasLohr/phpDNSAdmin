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
abstract class AutologinModule {

	/**
	 * @var User currently logged in user
	 */
	protected $user = null;

	/**
	 * Instantiate module with given config
	 *
	 * @param $config configuration values
	 * @return AutologinModule module instance
	 */
	abstract public static function getInstance($config);

	/**
	 *
	 * @return User user who is currently logged in or null if nobody is logged in
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set the user who is logged in
	 *
	 * @param User the user
	 * @return true (always)
	 */
	public function notifyLogin(User $user) {
		$this->user = $user;
		return true;
	}

	/**
	 * No user is logged in, so set the corresponding variable to null
	 *
	 * @param User the user
	 * @return true (always)
	 */
	public function notifyLogout() {
		$this->user = null;
		return true;
	}

}

?>