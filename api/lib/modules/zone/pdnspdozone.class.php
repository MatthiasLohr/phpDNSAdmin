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
 */
class PdnsPdoZone extends ZoneModule {

	private $db = null;
	private $zoneIds = array();

	protected function __construct($config) {
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);
		$this->listZones();
	}

	public static function getInstance($config) {
		return new PdnsPdoZone($config);
	}

	public function getRecordById(Zone $zone,$recordid) {
		$this->zoneAssureExistence($zone);
		$tmp = $this->listRecordsByFilter($zone,array('id' => $recordid));
		if (isset($tmp[$recordid])) {
			return $tmp[$recordid];
		}
		else {
			return null;
		}
	}

	private function hostnameLong2Short(Zone $zone,$hostname) {
		if ($hostname == $zone->getName()) {
			return '@';
		}
		else {
			return substr($hostname,0,-(strlen($zone->getName())+1));
		}
	}

	private function hostnameShort2Long(Zone $zone,$hostname) {
		if ($hostname == '@') {
			return $zone->getName();
		}
		else {
			return $hostname.'.'.$zone->getName();
		}
	}

	public function listRecords(Zone $zone) {
		$this->zoneAssureExistence($zone);
		return $this->listRecordsByFilter(array());
	}

	private function listRecordsByFilter(Zone $zone,array $filter) {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id,name,type,content,ttl,prio FROM records WHERE domain_id = '.$this->db->quote($this->zoneIds[$zone->getName()]);
		// apply filters
		if (isset($filter['id'])) {
			$query .= ' AND id = '.$this->db->quote($filter['id']);
		}
		if (isset($filter['name'])) {
			$query .= ' AND name = '.$this->db->quote($filter['name']);
		}
		if (isset($filter['type'])) {
			$query .= ' AND type = '.$this->db->quote($filter['type']);
		}
		// execute query
		$result = array();
		$stm = $this->db->query($query);
		while ($row = $stm->fetch()) {
			$result[$row['id']] = ResourceRecord::instantiate(
				$row['type'],$this->hostnameLong2Short($row['name']),
				$row['content'],
				$row['ttl'],
				$row['priority']
			);
		}
		return $result;
	}

	public function listRecordsByType(Zone $zone,$type) {
		$this->zoneAssureExistence($zone);
		$filter = array();
		$filter['type'] = $type;
		return $this->listRecordsByFilter($filter);
	}

	public function listZones() {
		$this->zoneIds = array();
		$result = array();
		$stm = $this->db->query('SELECT id,name FROM domains WHERE type = \'MASTER\'');
		while ($row = $stm->fetch()) {
			$zone = new Zone($row['name'],$this);
			$this->zoneIds[$zone->getName()] = $row['id'];
			$result[] = $zone;
		}
		return $result;
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$this->zoneAssureExistence($zone);

	}

	public function recordDelete(Zone $zone, $recordid) {
		$this->zoneAssureExistence($zone);

	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$this->zoneAssureExistence($zone);

	}

	public function zoneCreate($zonename) {
		$zone = new Zone($zonename,$this);
		if ($this->zoneExists($zone)) return false;
		$this->db->query('INSERT INTO domains (name,last_check,type,notified_serial) VALUES ('.$this->db->quote($zonename).',0,\'MASTER\',0)');
		
	}

	public function zoneDelete(Zone $zone) {
		$this->zoneAssureExistence($zone);
		$this->db->query('DELETE FROM records WHERE domain_id = '.$this->db->quote($this->zoneIds[$zone->getName()]));
		$this->db->query('DELETE FROM domains WHERE id = '.$this->db->quote($this->zoneIds[$zone->getName()]));
		unset($this->zoneIds[$zone->getName()]);
		return true;
	}

	public function zoneExists(Zone $zone) {
		return isset($this->zoneIds[$zone->getName()]);
	}
}

?>