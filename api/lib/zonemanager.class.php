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
class ZoneManager {

	/** @var ZoneManager instance of zone manager */
	private static $instance = null;

	/** @var ZoneModule[] array with loaded zone modules */
	private $modules = array();

	/** @var array array with module information */
	private $moduleInfo = array();

	/**
	 * Load zone modules
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
			$moduleFile = API_ROOT.'/lib/modules/zone/'.strtolower($moduleName).'.class.php';
			if (!file_exists($moduleFile)) throw new ModuleConfigException('Missing module file '.$moduleFile.'!');
			require_once($moduleFile);
			$this->modules[$moduleIndex] = new stdClass();
			$this->modules[$moduleIndex]->module = call_user_func(array($moduleName,'getInstance'),$localConfig);
			if ($this->modules[$moduleIndex]->module === null) {
				unset($this->modules[$moduleIndex]);
			}
			else {
				if (preg_match('/^([a-zA-Z0-9]+)$/',$localConfig['_sysname'])) {
					$this->modules[$moduleIndex]->sysname = $localConfig['_sysname'];
				}
				else {
					$this->modules[$moduleIndex]->sysname = $moduleIndex;
				}
				$this->modules[$moduleIndex]->name = (isset($localConfig['_name'])?$localConfig['_name']:'Server '.$moduleIndex);
			}
		}
	}

	/**
	 * Return the ZoneManager object
	 *
	 * @return ZoneManager the ZoneManager object
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Return module instance by sysname
	 *
	 * @param string $sysname
	 * @return ZoneModule module instance
	 */
	public function getModuleBySysname($sysname) {
		foreach ($this->modules as $moduleIndex => $module) {
			if ($module->sysname == $sysname) {
				return $module->module;
			}
		}
		return null;
	}

	/**
	 * Init ZoneManager and create the object
	 *
	 * @param array $configuration global module configuration
	 * @return ZoneManager the ZoneManager object
	 */
	public static function initialize($configuration) {
		self::$instance = new ZoneManager($configuration);
		return self::$instance;
	}

	/**
	 * Return a list of all loaded zone modules
	 *
	 * @return array array of stdClass objects with module information
	 */
	public function listModules() {
		$result = array();
		foreach ($this->modules as $moduleIndex => $module) {
			$tmp = new stdClass();
			$tmp->sysname = $module->sysname;
			$tmp->name = $module->name;
			$result[] = $tmp;
		}
		return $result;
	}
}

?>