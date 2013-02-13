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

	/** @var PDO */
	private $db = null;
	/** @var int[] */
	private $zoneIds = array();
	/** @var string */
	private $recordsSequence = 'records_id_seq';
	/** @var string */
	private $domainsSequence = 'domains_id_seq';
	/** @var string */
	private $tablePrefix = '';

	protected function __construct($config) {
		try {
			$this->db = new PDO($config['pdo_dsn'], $config['pdo_username'], $config['pdo_password']);
		}
		catch (PDOException $e) {
			$config['pdo_password'] = 'xxxxxx';
			throw new ModuleConfigException('Could not connect to database!');
		}

		if (isset($config['domains_sequence'])) $this->domainsSequence = $config['domains_sequence'];
		if (isset($config['records_sequence'])) $this->recordsSequence = $config['records_sequence'];

		if (isset($config['tableprefix'])) {
			$this->tablePrefix = $config['tableprefix'];
		}

		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO '.$this->db->quote($config['search_path']));
		}

		$this->listZones();
	}

	public function countRecordsByFilter(Zone $zone, array $filter = array()) {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id,name,type,content,ttl,prio FROM records WHERE ' . $this->tablePrefix . 'domain_id = ' . $this->db->quote($this->zoneIds[$zone->getName()]);
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

//		if($limit > 0) {
//			$query .= ' LIMIT ' . $this->db->quote($limit) . ' OFFSET ' . $this->db->quote($offset);
//		}

		$stm = $this->db->query($query);
		return $stm->rowCount();
	}

	public function getFeatures() {
		return array(
			'dnssec' => true,
			'rrtypes' => array(
				'A', 'AAAA', 'AFSDB', 'CERT', 'CNAME', 'DNSKEY', 'DS', 'HINFO', 'KEY', 'LOC',
				'MX', 'NAPTR', 'NS', 'NSEC', 'NSEC3', 'PTR', 'RP', 'RRSIG', 'SOA', 'SPF', 'SSHFP',
				'SRV', 'TXT'
			)
		);
	}

	public static function getInstance($config) {
		return new PdnsPdoZone($config);
	}

	public function getRecordById(Zone $zone, $recordid) {
		$this->zoneAssureExistence($zone);
		$tmp = $this->listRecordsByFilter($zone, array('id' => $recordid));
		if (isset($tmp[$recordid])) {
			return $tmp[$recordid];
		} else {
			return null;
		}
	}

	private function hostnameLong2Short(Zone $zone, $hostname) {
		if ($hostname == $zone->getName()) {
			return '@';
		} else {
			return substr($hostname, 0, -(strlen($zone->getName()) + 1));
		}
	}

	private function hostnameShort2Long(Zone $zone, $hostname) {
		if ($hostname == '@') {
			return $zone->getName();
		} else {
			return $hostname . '.' . $zone->getName();
		}
	}

	public function incrementSerial(Zone $zone) {
		$this->db->beginTransaction();
		if (parent::incrementSerial($zone)) {
			return $this->db->commit();
		}
		else {
			$this->db->rollBack();
			return false;
		}
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {
		$this->zoneAssureExistence($zone);
		$query = 'SELECT id,name,type,content,ttl,prio FROM records WHERE ' . $this->tablePrefix . 'domain_id = ' . $this->db->quote($this->zoneIds[$zone->getName()]);
		// where conditions (apply filters)
		if (isset($filter['id'])) {
			$query .= ' AND id = ' . $this->db->quote($filter['id']);
		}
		if (isset($filter['name'])) {
			if ($filter['name'] == '@') {
				$query .= ' AND name = ' . $this->db->quote($zone->getName());
			}
			else {
				$query .= ' AND name = ' . $this->db->quote($filter['name'].'.'.$zone->getName());
			}
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
		// sort options
		if (strlen($sortoptions) > 0) {
			$firstcol = true;
			$cols = explode(',',$sortoptions);
			if (!in_array('id',$cols) && !in_array('-id',$cols)) $cols[] = 'id';
			foreach ($cols as $col) {
				if (substr($col,0,1) == '-') {
					$colname = substr($col,1);
					$order = 'DESC';
				}
				else {
					$colname = $col;
					$order = 'ASC';
				}
				if (in_array($colname,array('id','name','type','content','ttl','priority'))) {
					if ($firstcol) {
						$firstcol = false;
						$query .= ' ORDER BY '.$colname.' '.$order;
					}
					else {
						$query .= ','.$colname.' '.$order;
					}
				}
			}
		}
		// limit/offset
		if($limit > 0) {
			$query .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
		}
		// execute query
		$result = array();
		$stm = $this->db->query($query);
		while ($row = $stm->fetch()) {
			$result[$row['id']] = ResourceRecord::getInstance(
				$row['type'],
				$this->hostnameLong2Short($zone, $row['name']),
				$row['content'],
				$row['ttl'],
				$row['prio']
			);
		}
		return $result;
	}

	public function listZones() {
		$this->zoneIds = array();
		$result = array();
		$stm = $this->db->query('SELECT id,name FROM ' . $this->tablePrefix . 'domains WHERE type = \'MASTER\'');
		while ($row = $stm->fetch()) {
			$zone = new Zone($row['name'], $this);
			$this->zoneIds[$zone->getName()] = $row['id'];
			$result[] = $zone;
		}
		return $result;
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$this->zoneAssureExistence($zone);
		$domainid = $this->zoneIds[$zone->getName()];
		try {
			$priority = $record->getField('priority');
			$res = $this->db->query(
				'INSERT INTO ' . $this->tablePrefix . 'records (domain_id,name,type,content,ttl,prio) VALUES ('
				. $this->db->quote($domainid) . ','
				. $this->db->quote($this->hostnameShort2Long($zone, $record->getName())) . ','
				. $this->db->quote($record->getType()) . ','
				. $this->db->quote(strval($record)) . ','
				. $this->db->quote($record->getTTL()) . ','
				. $this->db->quote($priority) . ')'
			);
		} catch (NoSuchFieldException $e) {
			$res = $this->db->query(
				'INSERT INTO ' . $this->tablePrefix . 'records (domain_id,name,type,content,ttl) VALUES ('
				. $this->db->quote($domainid) . ','
				. $this->db->quote($this->hostnameShort2Long($zone, $record->getName())) . ','
				. $this->db->quote($record->getType()) . ','
				. $this->db->quote(strval($record)) . ','
				. $this->db->quote($record->getTTL()) . ')'
			);
		}
		if ($res->rowCount() > 0) {
			switch ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
				case 'pgsql':
					return $this->db->lastInsertId($this->recordsSequence);
				default:
					return $this->db->lastInsertId();
			}
		}
		else {
			return false;
		}
	}

	public function recordDelete(Zone $zone, $recordid) {
		$this->zoneAssureExistence($zone);
		$domainid = $this->zoneIds[$zone->getName()];
		$this->db->query('DELETE FROM ' . $this->tablePrefix . 'records WHERE id = ' . $this->db->quote($recordid) . ' AND domain_id = ' . $this->db->quote($domainid));
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$this->zoneAssureExistence($zone);
		$domainid = $this->zoneIds[$zone->getName()];
		try {
			$priority = $record->getField('priority');
			$stm = $this->db->query(
				'UPDATE ' . $this->tablePrefix . 'records SET'
				. ' name = ' . $this->db->quote($this->hostnameShort2Long($zone, $record->getName())) . ','
				. ' type = ' . $this->db->quote($record->getType()) . ','
				. ' content = ' . $this->db->quote(strval($record)) . ','
				. ' ttl = ' . $this->db->quote($record->getTTL()) . ','
				. ' prio = ' . $this->db->quote($priority)
				. ' WHERE id = ' . $this->db->quote($recordid)
				. ' AND domain_id = ' . $domainid
			);
		} catch (NoSuchFieldException $e) {
			$stm = $this->db->query(
				'UPDATE ' . $this->tablePrefix . 'records SET'
				. ' name = ' . $this->db->quote($this->hostnameShort2Long($zone, $record->getName())) . ','
				. ' type = ' . $this->db->quote($record->getType()) . ','
				. ' content = ' . $this->db->quote(strval($record)) . ','
				. ' ttl = ' . $this->db->quote($record->getTTL())
				. ' WHERE id = ' . $this->db->quote($recordid)
				. ' AND domain_id = ' . $domainid
			);
		}
		return ($stm !== false && $stm->rowCount() > 0);
	}

	public function zoneCreate(Zone $zone) {
		if ($this->zoneExists($zone))
			throw new ZoneExistsException('Zone \''.$zone->getName().'\' already exists.!');
		$stm = $this->db->query('INSERT INTO ' . $this->tablePrefix . 'domains (name,last_check,type,notified_serial) VALUES (' . $this->db->quote($zone->getName()) . ',0,\'MASTER\',0)');
		return ($stm->rowCount() > 0);
	}

	public function zoneDelete(Zone $zone) {
		$this->zoneAssureExistence($zone);
		$this->db->query('DELETE FROM ' . $this->tablePrefix . 'records WHERE domain_id = ' . $this->db->quote($this->zoneIds[$zone->getName()]));
		$this->db->query('DELETE FROM ' . $this->tablePrefix . 'domains WHERE id = ' . $this->db->quote($this->zoneIds[$zone->getName()]));
		unset($this->zoneIds[$zone->getName()]);
		return true;
	}

	public function zoneExists(Zone $zone) {
		return isset($this->zoneIds[$zone->getName()]);
	}

}

?>