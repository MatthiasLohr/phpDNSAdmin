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

class AuthenticationManager {

	private static $instance = null;

	private $modules = array();
	private $usermap = array();

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

	public static function getInstance() {
		return self::$instance;
	}

	public static function initialize($configuration) {
		self::$instance = new AuthenticationManager($configuration);
		return self::$instance;
	}

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

	public function userCheckPassword(User $user,$password) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex === null) throw new NoSuchUserException('User '.$user->getUsername().' is unknown!');
		return $this->modules[$moduleIndex]->userCheckPassword($user,$password);
	}

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

	public function userExists(User $user) {
		$result = $this->userFind($user);
		return ($result !== null);
	}

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

	public function userSetPassword(User $user, $password) {
		$moduleIndex = $this->userFind($user);
		if ($moduleIndex === null) throw new NoSuchUserException('User '.$user->getUsername().' is unknown!');
		$this->modules[$moduleIndex]->userSetPassword($user,$password);
	}

	
}

?>