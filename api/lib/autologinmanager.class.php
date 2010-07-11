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
class AutologinManager {

	private static $instance = null;

	private $modules = array();

	private $user = null;

	/**
	 * Load autologin modules
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
			$moduleFile = API_ROOT.'/lib/modules/autologin/'.strtolower($moduleName).'.class.php';
			if (!file_exists($moduleFile)) throw new ModuleConfigException('Missing module file '.$moduleFile.'!');
			require_once($moduleFile);
			$this->modules[$moduleIndex] = call_user_func(array($moduleName,'getInstance'),$localConfig);
			if ($this->modules[$moduleIndex] === null) unset($this->modules[$moduleIndex]);
		}
	}

	/**
	 * Return the AutologinManager object
	 *
	 * @return AutologinManager the AutologinManager object
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Get user who is logged in from this session
	 *
	 * @return User the user
	 */

	public function getUser() {
		if ($this->user !== null) return $this->user;
		$user = null;
		$foundIndex = null;
		// check for autologin
		foreach ($this->modules as $moduleIndex => $module) {
			$tmpUser = $module->getUser();
			if ($tmpUser instanceof User) {
				$user = $tmpUser;
				$foundIndex = $moduleIndex;
				break;
			}
		}
		// notifiy all other modules
		if ($user !== null) {
			foreach ($this->modules as $moduleIndex => $module) {
				if ($moduleIndex == $foundIndex) continue;
				$module->notifyLogin($user);
			}
		}
		// return
		$this->user = $user;
		return $user;
	}

	/**
	 * Init AuthorizationManager and create the object
	 *
	 * @param array $configuration global module configuration
	 * @return AuthorizationManager the AuthorizationManager object
	 */
	public static function initialize($configuration) {
		self::$instance = new AutologinManager($configuration);
		return self::$instance;
	}

	/**
	 * Tell the modules that a user has been logged in
	 *
	 * @param User $user the user who is logged in
	 */
	public function notifyLogin(User $user) {
		$this->user = $user;
		foreach ($this->modules as $moduleIndex => $module) {
			$module->notifyLogin($user);
		}
	}

	/**
	 * Tell the modules that a user has been logged out
	 *
	 * @param User $user the user who is loggedout
	 */
	public function notifyLogout() {
		$this->user = null;
		foreach ($this->modules as $moduleIndex => $module) {
			$module->notifyLogout();
		}
	}
}

?>