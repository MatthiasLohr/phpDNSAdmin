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

	/** @var resource LDAP connection resource */
	var $lc = null;

	/** @var string bindDN */
	var $binddn = '';
	/** @var string password for bindDN */
	var $password = '';
	/** @var string baseDN for users */
	var $basedn;
	/** @var string filter for users */
	var $filter;

	/** @var string[] array of allowed users */
	var $whitelist = null;
	/** @var string[] array of forbidden users */
	var $blacklist = array();

	protected function __construct($config) {
		if (!isset($config['server'])) throw new ModuleConfigException('You have to give me a LDAP server!');
		if (!isset($config['basedn'])) throw new ModuleConfigException('You have to give me a BaseDN!');
		if (!isset($config['filter'])) throw new ModuleConfigException('You have to give me a filter string!');

		$this->lc = ldap_connect($config['server']);

		if (isset($config['binddn'])) {
			$this->binddn = $config['binddn'];
			if (isset($config['password'])) $this->password = $config['password'];
		}

		$this->basedn = $config['basedn'];
		$this->filter = $config['filter'];

		if (isset($config['whitelist']) && is_array($config['whitelist'])) $this->whitelist = $config['whitelist'];
		if (isset($config['blacklist']) && is_array($config['blacklist'])) $this->blacklist = $config['blacklist'];
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
		if (is_array($this->whitelist) && !in_array($user->getUsername(),$this->whitelist)) return false;
		if (in_array($user->getUsername(),$this->blacklist)) return false;
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