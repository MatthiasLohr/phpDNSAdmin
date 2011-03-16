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
class LdapAuthentication extends AuthenticationModule {

	var $lc = null;

	var $binddn;
	var $password;
	var $basedn;
	var $filter;

	protected function __construct($config) {
		$this->lc = ldap_connect($config['server']);
		$this->binddn = $config['binddn'];
		$this->password = $config['password'];
		$this->basedn = $config['basedn'];
		$this->filter = $config['filter'];
	}

	public function  __destruct() {
	 ldap_unbind($this->lc);
	}

	public static function getInstance($config) {
		return new LdapAuthentication($config);
	}

	private function ldapBind($bindDN = '',$password = '') {
		if ($bindDN == '') { // anonymous bind
			$result = @ldap_bind($this->lc);
		}
		else {
			if ($password == '') return false;
			$result = @ldap_bind($this->lc,$bindDN,$password);
		}
		if ($result === true) {
			return true;
		}
		else {
			return false;
		}
	}

	private function ldapCountEntries($searchResource) {
		return ldap_count_entries($this->lc,$searchResource);
	}

	private function ldapEscapeFilter($string) {
		return str_replace(array('*','(',')'),array('\*','\(','\)'),$string);
	}

	private function ldapFirstEntry($searchResource) {
		return ldap_first_entry($this->lc,$searchResource);
	}

	private function ldapGetDN($entryResource) {
		return ldap_get_dn($this->lc,$entryResource);
	}

	private function ldapSearch($baseDN,$filter) {
		return ldap_search($this->lc,$baseDN,$filter);
	}

	public function userCheckPassword(User $user,$password) {
		if ($password == '') return false; // password must not be empty
		$this->ldapBind($this->binddn,$this->password);
		$sr = $this->ldapSearch($this->basedn,sprintf($this->filter,$this->ldapEscapeFilter($user->getUsername())));
		if ($this->ldapCountEntries($sr) > 0) {
			$ldapUser = $this->ldapFirstEntry($sr);
			// try to rebind with new user
			return $this->ldapBind($this->ldapGetDN($ldapUser),$password);
		}
		else {
			return false;
		}
	}

	public function userExists(User $user) {
		$this->ldapBind($this->binddn,$this->password);
		$sr = $this->ldapSearch($this->basedn,sprintf($this->filter,$this->ldapEscapeFilter($user->getUsername())));
		return ($this->ldapCountEntries($sr) > 0);
	}

}

?>