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
class AuthenticationManager {

	private static $instance = null;

	private $modules = array();
	private $usermap = array();

	/**
	 * Load authentication modules
	 *
	 * @param array $moduleConfig global module configuration
	 * @throws ModuleConfigException if no config exists
	 * @throws ModuleConfigException if the config is not properly written
	 * @throws ModuleConfigException if the module file dows not exist
	 */
	protected function __construct($moduleConfig) {
		if (!is_array($moduleConfig)) throw new ModuleConfigException('No module configuration found!');
		$moduleCount = count($moduleConfig);
		for ($moduleIndex = 0; $moduleIndex < $moduleCount; $moduleIndex++) {
			$localConfig = $moduleConfig[$moduleIndex];
			if (!isset($localConfig['_module'])) throw new ModuleConfigException('Found module config without _module definition!');
			$moduleName = $localConfig['_module'];
			unset($localConfig['_module']);
			$moduleFile = API_ROOT.'/lib/modules/authentication/'.strtolower($moduleName).'.class.php';
			if (!file_exists($moduleFile)) throw new ModuleConfigException('Missing module file '.$moduleFile.'!');
			require_once($moduleFile);
			$this->modules[$moduleIndex] = call_user_func(array($moduleName,'getInstance'),$localConfig);
			if ($this->modules[$moduleIndex] === null) unset($this->modules[$moduleIndex]);
		}
		$this->listUsers();
	}


	/**
	 * Return the AuthenticationManager object
	 *
	 * @return AuthenticationManager the AuthenticationManager object
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Init AuthenticationManager and create the object
	 *
	 * @param array $configuration global module configuration
	 * @return AuthenticationManager the AuthenticationManager object
	 */
	public static function initialize($configuration) {
		self::$instance = new AuthenticationManager($configuration);
		return self::$instance;
	}

	/**
	 * List all registered users
	 *
	 * @return User[]
	 */
	public function listUsers() {
		$userList = array();
		$this->usermap = array();
		foreach ($this->modules as $moduleIndex => $module) {
			try {
				$tmpList = $module->listUsers();
				foreach ($tmpList as $user) {
					if (!isset($this->usermap[$user->getUsername()])) {
						$this->usermap[$user->getUsername()] = $moduleIndex;
						$userList[] = $user;
					}
				}
			}
			catch (NotSupportedException $e) {}
		}
		return $userList;
	}

	/**
	 * Add a user to the first module that accepts him
	 *
	 * @param User $user user to add
	 * @param string $pasword unencrypted password for the new user, optional but needed if the user should be able to login
	 * @return bool true on success, false otherwise
	 */

	public function userAdd(User $user, $password = null) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex !== null) return false;
		foreach ($this->modules as $moduleIndex => $module) {
			try {
				if ($module->userAdd($user,$password)) {
					$this->usermap[$user->getUsername()] = $moduleIndex;
					return true;
				}
				else {
					return false;
				}
			}
			catch (ReadOnlyException $e) {}
		}
		return false;
	}

	/**
	 * Check a user's login data
	 *
	 * @param User $user user to check
	 * @param string $pasword unencrypted password
	 * @return bool true if the user can login with the given data, false otherwise
	 * @throws NoSuchUserException if the user is not registered
	 */
	public function userCheckPassword(User $user,$password) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex === null) throw new NoSuchUserException('User '.$user->getUsername().' is unknown!');
		return $this->modules[$moduleIndex]->userCheckPassword($user,$password);
	}

	/**
	 * Remove a user
	 *
	 * @param User $user user to delete
	 * @return bool true on success, false otherwise
	 * @throws NoSuchUserException if the user is not registered
	 */
	public function userDelete(User $user) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex === null) throw new NoSuchUserException('User '.$user->getUsername().' is unknown!');
		if ($this->modules[$moduleIndex]->userDelete($user)) {
			unset($this->usermap[$user->getUsername()]);
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Check if a user exists
	 *
	 * @param User $user user to delete
	 * @return bool true if user exists, false otherwise
	 */
	public function userExists(User $user) {
		$result = $this->userFind($user);
		return ($result !== null);
	}

	/**
	 * Find a user
	 *
	 * @param User $user the user to look for
	 * @return integer the ID of the module that stores the user's data or null if the user does not exist
	 */
	private function userFind(User $user) {
		if (isset($this->usermap[$user->getUsername()])) {
			return $this->usermap[$user->getUsername()];
		}
		foreach ($this->modules as $moduleIndex => $module) {
			if ($module->userExists($user)) {
				$this->usermap[$user->getUsername()] = $moduleIndex;
				return $moduleIndex;
			}
		}
		return null;
	}

	/**
	 * Add a user to the list
	 *
	 * @param User $user user to modify
	 * @param string $pasword new unencrypted password for the user, not null
	 * @return bool true on success, false otherwise
	 */
	public function userSetPassword(User $user, $password) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex === null) throw new NoSuchUserException('User '.$user->getUsername().' is unknown!');
		$this->modules[$moduleIndex]->userSetPassword($user,$password);
	}

	
}

?>