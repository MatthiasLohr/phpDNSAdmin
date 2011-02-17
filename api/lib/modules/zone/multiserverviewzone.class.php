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
	private $recordsSequence = 'mv_records_id_seq';
	/** @var string */
	private $tablePrefix = 'mv_';

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

		if (isset($config['records_sequence'])) $this->recordsSequence = $config['records_sequence'];

		if (isset($config['tableprefix'])) $this->tablePrefix = $config['tableprefix'];

		// connect to cache db
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);
		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO '.$this->db->quote($config['search_path']));
		}
	}

	public function countRecordsByFilter(Zone $zone, array $filter = array()) {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id FROM '.$this->tablePrefix.'records WHERE zone = ' . $this->db->quote($zone->getName());
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

		$stm = $this->db->query($query);
		return $stm->rowCount();
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
		// load record
		$stm = $this->db->query('SELECT name,type,content,ttl,prio FROM '.$this->tablePrefix.'records WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($zone->getName()));
		$row = $stm->fetch();
		if (!$row) return null;
		$record = ResourceRecord::getInstance($row['type'],$row['name'],$row['content'],$row['ttl'],$row['prio']);
		// load views
		$views = $this->listViews();
		$myviews = array();
		foreach ($views as $viewname) {
			$stm = $this->db->query('SELECT viewid FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($recordid).' AND viewname = '.$this->db->quote($viewname));
			$myviews[$viewname] = $stm->rowCount();
		}
		$record->setViewinfo($myviews);
		return $record;
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array(), $offset = 0, $limit = null) {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id FROM '.$this->tablePrefix.'records WHERE zone = ' . $this->db->quote($zone->getName());
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

		if($limit > 0) {
			$query .= ' LIMIT ' . $this->db->quote($limit) . ' OFFSET ' . $this->db->quote($offset);
		}
		// execute query
		$result = array();
		$stm = $this->db->query($query);
		while ($tmprecord = $stm->fetch()) {
			$record = $this->getRecordById($zone,$tmprecord['id']);
			$result[$tmprecord['id']] = $record;
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

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$this->db->query('INSERT INTO '.$this->tablePrefix.'records (zone,name,type,content,ttl,prio) VALUES ('
			.$this->db->quote($zone->getName()).','
			.$this->db->quote($record->getName()).','
			.$this->db->quote($record->getType()).','
			.$this->db->quote($record->getContentString()).','
			.$this->db->quote($record->getTTL()).','
			.($record->fieldExists('priority')?$this->db->quote($record->getField('priority')):'NULL').')');
		switch ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
			case 'pgsql':
				$recordid = $this->db->lastInsertId($this->recordsSequence);
				break;
			default:
				$recordid = $this->db->lastInsertId();
		}
		// insert views
		$myviews = $record->getViewinfo();
		foreach ($this->modules as $module) {
			if ($myviews === null || (isset($myviews[$module->sysname]) && $myviews[$module->sysname])) {
				$viewrecordid = $module->module->recordAdd($zone,$record);
				if ($viewrecordid) {
					$this->db->query('INSERT INTO '.$this->tablePrefix.'idmap (myid,viewname,viewid) VALUES ('.$this->db->quote($recordid).','.$this->db->quote($module->sysname).','.$this->db->quote($viewrecordid).')');
				}
			}
		}
		return $recordid;
	}

	public function recordDelete(Zone $zone, $recordid) {
		foreach ($this->modules as $module) {
			$stm = $this->db->query('SELECT viewid FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($recordid).' AND viewname = '.$this->db->quote($module->sysname));
			$tmprecord = $stm->fetch();
			if ($tmprecord) {
				$module->module->recordDelete($zone,$tmprecord['viewid']);
			}
		}
		$this->db->beginTransaction();
		$this->db->query('DELETE FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($recordid));
		$this->db->query('DELETE FROM '.$this->tablePrefix.'records WHERE id = '.$this->db->quote($recordid));
		$this->db->commit();
		return true;
	}

	private function recordGetViewid(Zone $zone, $recordid, $viewname) {
		$stm = $this->db->query('SELECT viewid FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($recordid).' AND viewname = '.$this->db->quote($viewname));
		$tmp = $stm->fetch();
		if (!$tmp) return null;
		return $tmp['viewid'];
	}

	public function recordSetViews(Zone $zone, $recordid, array $views) {
		$record = $this->getRecordById($zone,$recordid);
		foreach ($this->modules as $module) {
			$viewid = $this->recordGetViewid($zone,$recordid,$module->sysname);
			if ($viewid === null && isset($views[$module->sysname]) && $views[$module->sysname]) {
				$newid = $module->module->recordAdd($zone,$record);
				$this->db->query('INSERT INTO '.$this->tablePrefix.'idmap (myid,viewname,viewid) VALUES ('.$this->db->quote($recordid).','.$this->db->quote($module->sysname).','.$this->db->quote($newid).')');
			}
			elseif ($viewid !== null && (!isset($views[$module->sysname]) || !$views[$module->sysname])) {
				$module->module->recordDelete($zone,$viewid);
				$this->db->query('DELETE FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($recordid).' AND viewname = '.$this->db->quote($module->sysname).' AND viewid = '.$this->db->quote($viewid));
			}
		}
		return true;
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$this->db->query('UPDATE '.$this->tablePrefix.'records SET name = '.$this->db->quote($record->getName())
			.', type = '.$this->db->quote($record->getType())
			.', content = '.$this->db->quote($record->getContentString())
			.', ttl = '.$this->db->quote($record->getTTL())
			.', prio = '.($record->fieldExists('priority')?$this->db->quote($record->getField('priority')):'NULL')
			.' WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($zone->getName()));
		foreach ($this->modules as $module) {
			$id = $this->recordGetViewid($zone,$recordid,$module->sysname);
			if ($id !== null) $module->module->recordUpdate($zone,$id,$record);
		}
		$this->recordSetViews($zone,$recordid,$record->getViewinfo());
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
		$this->db->beginTransaction();
		$stm = $this->db->query('SELECT id FROM '.$this->tablePrefix.'records WHERE zone = '.$this->db->quote($zone->getName()));
		while ($tmprecord = $stm->fetch()) {
			$this->db->query('DELETE FROM '.$this->tablePrefix.'idmap WHERE myid = '.$this->db->quote($tmprecord['id']));
			$this->db->query('DELETE FROM '.$this->tablePrefix.'records WHERE id = '.$this->db->quote($tmprecord['id']));
		}
		$this->db->commit();
		foreach ($this->modules as $module) {
			$module->module->zoneDelete($zone);
		}
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