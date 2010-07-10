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

abstract class AuthenticationModule {

	abstract public static function getInstance($config);
	
	public function listUsers() {
		throw new NotSupportedException("Can't list users!");
	}
	
	abstract public function userAdd(User $user, $password = null);
	abstract public function userCheckPassword(User $user,$password);
	abstract public function userDelete(User $user);
	abstract public function userExists(User $user);
	abstract public function userSetPassword(User $user, $password);

}

?>