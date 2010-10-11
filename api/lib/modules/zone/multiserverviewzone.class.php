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
 * @subpackage Zone
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Zone
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class MultiServerViewZone extends ZoneModule implements Views {

	private $modules = array();

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

	public function getFeatures() {
		$features = array();
		foreach ($this->modules as $module) {
			$tmpFeatures = $module->module->getFeatures();
			
		}
		return $features;
	}

	public static function getInstance($config) {
		return new MultiServerViewZone($config['views']);
	}

	public function getRecordById(Zone $zone, $recordid) {

	}

	public function listRecordsByFilter(Zone $zone, array $filter = array()) {

	}

	public function listViews() {
		$result = array();
		foreach ($this->modules as $module) {
			$result[$module->sysname] = $module->sysname; 
		}
		return $result;
	}

	public function listZones() {
		$zones = array();
		foreach ($this->modules as $moduleIndex => $module) {
			$tmpZones = $module->module->listZones();
			foreach ($tmpZones as $tmpZone) {
				$zones[$tmpZone->getName()] = $tmpZone;
			}
		}
		return $zones;
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {

	}

	public function recordDelete(Zone $zone, $recordid) {

	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {

	}

	public function zoneCreate(Zone $zone) {

	}

	public function zoneDelete(Zone $zone) {

	}

	public function zoneExists(Zone $zone) {

	}

}

?>