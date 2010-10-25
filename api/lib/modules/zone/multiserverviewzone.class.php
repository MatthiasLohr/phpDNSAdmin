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

	/** @var array */
	private $modules = array();
	/** @var PDO */
	private $db;
	/** @var string */
	private $table = 'pda_records';

	protected function __construct($config) {
		// load modules
		$moduleConfig = $config['views'];
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
		// connect to cache db
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);
		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO '.$this->db->quote($config['search_path']));
		}
	}

	public function getFeatures() {
		$features = array(
			'dnssec' => false,
			'rrtypes' => null
		);
		foreach ($this->modules as $module) {
			$tmpFeatures = $module->module->getFeatures();
			// merge rrtypes
			if ($features['rrtypes'] === null) {
				$features['rrtypes'] = $tmpFeatures['rrtypes'];
			}
			else {
				$features['rrtypes'] = array_intersect($features['rrtypes'],$tmpFeatures['rrtypes']);
			}
		}
		return $features;
	}

	public static function getInstance($config) {
		return new MultiServerViewZone($config);
	}

	public function getRecordById(Zone $zone, $recordid) {
		$stm = $this->db->query('SELECT name,type,content,ttl,prio FROM '.$this->table.' WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($zone->getName()));
		$row = $stm->fetch();
		if (!$row) return null;
		$record = ResourceRecord::getInstance($row['type'],$row['name'],$row['content'],$row['ttl'],$row['prio']);
		$views = array();
		foreach ($this->modules as $module) {
			$views[$module->sysname] = ($this->moduleFindRecord($module->module,$zone,$record)?false:true);
		}
		$record->setViewinfo($views);
		return $record;
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array()) {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id,name,type,content,ttl,prio FROM '.$this->table.' WHERE zone = ' . $this->db->quote($zone->getName());
		// apply filters
		if (isset($filter['id'])) {
			$query .= ' AND id = ' . $this->db->quote($filter['id']);
		}
		if (isset($filter['name'])) {
			$query .= ' AND name = ' . $this->db->quote($filter['name'].'.'.$zone->getName());
		}
		if (isset($filter['type'])) {
			$query .= ' AND type = ' . $this->db->quote($filter['type']);
		}
		if (isset($filter['content'])) {
			$query .= ' AND content = ' . $this->db->quote($filter['content']);
		}
		if (isset($filter['ttl'])) {
			$query .= ' AND ttl = ' . $this->db->quote($filter['ttl']);
		}
		// execute query
		$result = array();
		$stm = $this->db->query($query);
		while ($row = $stm->fetch()) {
			$record = ResourceRecord::getInstance(
				$row['type'],
				$row['name'],
				$row['content'],
				$row['ttl'],
				$row['prio']
			);
			$views = array();
			foreach ($this->modules as $module) {
				$views[$module->sysname] = ($this->moduleFindRecord($module->module,$zone,$record)?false:true);
			}
			$record->setViewinfo($views);
			$result[$row['id']] = $record;
		}
		return $result;
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

	private function moduleFindRecord(ZoneModule $module, Zone $zone, ResourceRecord $record) {
		$filter = array();
		$filter['name'] = $record->getName();
		$filter['type'] = $record->getType();
		$filter['content'] = $record->getContentString();
		$filter['ttl'] = $record->getTTL();
		$list = $module->listRecordsByFilter($zone,$filter);
		if (count($list) == 0) {
			return null;
		}
		return key($list);
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$views = $record->getViewinfo();
		$success = false;
		foreach ($this->modules as $module) {
			if ($views === null || (isset($views[$module->sysname]) && $views[$module->sysname])) {
				$success = $module->module->recordAdd($zone,$record) || $success;
			}
		}
		if ($success) {
			$this->db->query('INSERT INTO '.$this->table.' (zone,name,type,content,ttl,prio) VALUES ('
				.$this->db->quote($zone->getName()).','
				.$this->db->quote($record->getName()).','
				.$this->db->quote($record->getType()).','
				.$this->db->quote($record->getContentString()).','
				.$this->db->quote($record->getTTL()).','
				.($record->fieldExists('priority')?$this->db->quote($record->getField('priority')):'NULL').')');
			return true;
		}
		return false;
	}

	public function recordDelete(Zone $zone, $recordid) {
		$record = $this->getRecordById($zone,$recordid);
		if ($record === null) return false;
		foreach ($this->modules as $module) {
			$id = $this->moduleFindRecord($module->module,$zone,$record);
			if ($id !== null) $module->module->recordDelete($zone,$id);
		}
		$this->db->query('DELETE FROM '.$this->table.' WHERE id = '.$this->db->quote($recordid));
		return true;
	}

	public function recordSetViews(Zone $zone, $recordid, array $views) {
		$record = $this->getRecordById($zone,$recordid);
		if ($record === null) return false;
		foreach ($this->modules as $module) {
			if (isset($views[$module->sysname])) {
				$mRecordid = $this->moduleFindRecord($module->module,$zone,$record);
				if ($views[$module->sysname] && $mRecordid === null) {
					$module->module->recordAdd($zone,$record);
				}
				elseif (!$views[$module->sysname] && $mRecordid !== null) {
					$module->module->recordDelete($zone,$mRecordid);
				}
			}
		}
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$oldRecord = $this->getRecordById($zone,$recordid);
		if ($oldRecord === null) return false;
		$views = $oldRecord->getViewinfo();
		foreach ($this->modules as $module) {
			if (isset($views[$module->sysname]) && $views[$module->sysname]) {
				$mRecordid = $this->moduleFindRecord($module->module,$zone,$oldRecord);
				$module->module->recordUpdate($zone,$mRecordid);
			}
		}
		$this->db->query('UPDATE '.$this->table.' SET name = '.$this->db->quote($record->getName())
			.', type = '.$this->db->quote($record->getType())
			.', content = '.$this->db->quote($record->getContentString())
			.', ttl = '.$this->db->quote($record->getTTL())
			.', prio = '.($record->fieldExists('priority')?$this->db->quote($record->getField('priority')):'NULL')
			.' WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($zone->getName()));
		return true;
	}

	public function zoneCreate(Zone $zone) {
		if ($this->zoneExists($zone)) return false;
		foreach ($this->modules as $module) {
			$module->module->zoneCreate($zone);
		}
		return true;
	}

	public function zoneDelete(Zone $zone) {
		foreach ($this->modules as $module) {
			$module->module->zoneDelete($zone);
		}
		$this->db->query('DELETE FROM '.$this->table.' WHERE zone = '.$this->db->quote($zone->getName()));
		return true;
	}

	public function zoneExists(Zone $zone) {
		foreach ($this->modules as $module) {
			if ($module->module->zoneExists($zone)) return true;
		}
		return false;
	}

}

?>