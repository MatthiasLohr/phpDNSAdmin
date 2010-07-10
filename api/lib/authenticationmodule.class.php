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

abstract class AuthenticationModule {

	/**
	 * Create an instance of the module
	 *
	 * @param array $config module configuration
	 * @return AuthenticationModule working instance of the moodule (or null if configuration errors occured?)
	 */
	abstract public static function getInstance($config);

	/**
	 * List users handled by this module.
	 *
	 * @throws NotSupportedException Always because the coder was too lazy to fully remove this function from the APIs.
	 */
	public function listUsers() {
		throw new NotSupportedException("Can't list users!");
	}

	/**
	 * Add a user to the list
	 *
	 * @param User $user user to add
	 * @param string $pasword unencrypted password for the new user, optional but needed if the user should be able to login
	 * @return bool true on success, false otherwise
	 */
	
	
	abstract public function userAdd(User $user, $password = null);

	/**
	 * Check a user's login data
	 *
	 * @param User $user user to check
	 * @param string $pasword unencrypted password
	 * @return bool true if the user can login with the given data, false otherwise
	 */
	abstract public function userCheckPassword(User $user,$password);

	/**
	 * Remove a user from the module's database
	 *
	 * @param User $user user to delete
	 * @return bool true on success, false otherwise
	 */
	abstract public function userDelete(User $user);

	/**
	 * Check if a user exists
	 *
	 * @param User $user user to delete
	 * @return bool true if user exists, false otherwise
	 */
	abstract public function userExists(User $user);

	/**
	 * Add a user to the list
	 *
	 * @param User $user user to modify
	 * @param string $pasword new unencrypted password for the user, not null
	 * @return bool true on success, false otherwise
	 */
	abstract public function userSetPassword(User $user, $password);

}

?>