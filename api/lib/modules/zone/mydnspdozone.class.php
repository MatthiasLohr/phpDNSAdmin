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
class MydnsPdoZone extends ZoneModule {

	/** @var PDO */
	private $db = null;
	/** @var int[] */
	private $zoneIds = null;

	protected function __construct($config) {
		try {
			$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);
		}
		catch (PDOException $e) {
			$config['pdo_password'] = 'xxxxxx';
			throw new ModuleConfigException('Could not connect to database!');
		}

		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO '.$this->db->quote($config['search_path']));
		}
	}

	public function getFeatures() {
		return array(
			'rrtypes' => array(
				'A', 'AAAA', 'ALIAS', 'CNAME', 'HINFO', 'MX', 'NS', 'PTR', 'RP', 'SOA',
				'SRV', 'TXT'
			)
		);
	}

	public static function getInstance($config) {
		return new MydnsPdoZone($config);
	}

	/**
	 * @todo implement
	 */
	public function getRecordById(Zone $zone,$recordid) {
		$this->zoneAssureExistence($zone);
		if ($recordid == -1) { // recordid == -1: SOA record from soa table
			// SOA record (stored in soa table)
			$result = $this->db->query('SELECT origin,ns,mbox,serial,refresh,retry,expire,minimum,ttl FROM soa WHERE origin = '.$this->db->quote($zone->getName()));
			$tmprecord = $result->fetch();
			return ResourceRecord::getInstance('SOA',
				'@',
				array(
					'primary'     => $tmprecord['ns'],
					'hostmaster'  => Email::convertFromDNS($tmprecord['mbox']),
					'serial'      => $tmprecord['serial'],
					'refresh'     => $tmprecord['refresh'],
					'retry'       => $tmprecord['retry'],
					'expire'      => $tmprecord['expire'],
					'negativettl' => $tmprecord['minimum'],
				),
				$tmprecord['ttl']
			);
		}
		else {
			// other records (stored in rr table)
			$stm = $this->db->query('SELECT name,type,data,aux,ttl FROM rr WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($this->zoneIds[$zone->getName()]));
			$tmprecord = $stm->fetch();
			if (!$tmprecord) return null;
			return ResourceRecord::getInstance(
				$tmprecord['type'],
				(substr($tmprecord['name'],-1) == '.')?substr($tmprecord['name'],0,-strlen($zone->getName())-2):$tmprecord['name'],
				$tmprecord['data'],
				$tmprecord['ttl'],
				($tmprecord['aux'] == 0?null:$tmprecord['aux'])
			);
		}
	}

	/**
	 * @todo implement
	 */
	public function listRecordsByFilter(Zone $zone,array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {
		$this->zoneAssureExistence($zone);
		// initial query with join for including SOA record in filter/sort options
		$query = 'SELECT * FROM ((SELECT id,name,type,data AS content,aux,ttl FROM rr WHERE zone = '
			.$this->db->quote($this->zoneIds[$zone->getName()]).') UNION ALL'
			.' (SELECT 0 AS id, CONCAT(origin,\'.\') AS name, \'SOA\' AS type, CONCAT_WS(\' \',ns,mbox,serial,refresh,retry,expire,minimum) AS content, NULL AS aux, ttl FROM soa WHERE id = '.$this->db->quote($this->zoneIds[$zone->getName()]).'))'
			.' AS tmptbl WHERE TRUE';
		// where conditions (apply filters)
		if (isset($filter['id'])) {
			$query .= ' AND id = ' . $this->db->quote($filter['id'] == -1?0:$filter['id']);
		}
		if (isset($filter['name'])) {
			if ($filter['name'] == '@') {
				$query .= ' AND name = ' . $this->db->quote($zone->getName().'.');
			}
			else {
				$query .= ' AND name = ' . $this->db->quote($filter['name'].'.'.$zone->getName().'.');
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
		$stm = $this->db->query($query);
		$records = array();
		while ($tmpRecord = $stm->fetch()) {
			if ($tmpRecord['id'] == 0) $tmpRecord['id'] = -1;
			if (substr($tmpRecord['name'],-1) == '.') {
				if (substr($tmpRecord['name'],0,-1) == $zone->getName()) {
					$name = '@';
				}
				else {
					$name = substr($tmpRecord['name'],0,-strlen($zone->getName())-2);
				}
			}
			else {
				$name = $tmpRecord['name'];
			}
			$records[$tmpRecord['id']] = ResourceRecord::getInstance(
				$tmpRecord['type'],
				$name,
				$tmpRecord['content'],
				$tmpRecord['ttl'],
				($tmpRecord['aux'] == 0?null:$tmpRecord['aux'])
			);
		}
		return $records;
	}

	public function listZones() {
		$result = $this->db->query('SELECT id,origin FROM soa ORDER BY origin ASC');
		$this->zoneIds = array();
		$zones = array();
		while ($tmpzone = $result->fetch()) {
			$this->zoneIds[$tmpzone['origin']] = $tmpzone['id'];
			$zones[] = new Zone($tmpzone['origin'],$this);
		}
		return $zones;
	}

	public function recordAdd(Zone $zone,ResourceRecord $record) {
		$this->zoneAssureExistence($zone);
		if ($record->getType() == 'SOA') return false; // SOAs are only in the soa table, one for each zone
		$stm = $this->db->query('INSERT INTO rr (zone,name,type,data,aux,ttl) VALUES ('
			.$this->db->quote($this->zoneIds[$zone->getName()]).','
			.$this->db->quote($record->getName() == '@'?$zone->getName():$record->getName().'.'.$zone->getName().'.').','
			.$this->db->quote($record->getType()).','
			.$this->db->quote($record->getContentString()).','
			.$this->db->quote(($record->fieldExists('priority')?$record->getField('priority'):'0')).','
			.$this->db->quote($record->getTTL()).')'
		);
		if ($stm->rowCount() > 0) {
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
		if ($recordid == -1) return false; // recordid == -1 is the SOA record from the soa table
		$stm = $this->db->query('DELETE FROM rr WHERE id = '.$this->db->quote($recordid).' AND zone = '.$this->db->quote($this->zoneIds[$zone->getName()]));
		return ($stm->rowCount() > 0);
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$this->zoneAssureExistence($zone);
		if ($record->getType() == 'SOA') {
			$result = $this->db->query('UPDATE soa SET ns = '.$this->db->quote($record->getField('primary'))
				.',mbox = '.$this->db->quote(Email::convertToDNS($record->getField('hostmaster')))
				.',serial = '.$this->db->quote($record->getField('serial'))
				.',refresh = '.$this->db->quote($record->getField('refresh'))
				.',retry = '.$this->db->quote($record->getField('retry'))
				.',expire = '.$this->db->quote($record->getField('expire'))
				.',minimum = '.$this->db->quote($record->getField('negativettl'))
				.',ttl = '.$this->db->quote($record->getTTL())
				.' WHERE origin = '.$this->db->quote($zone->getName())
			);
			return ($result->rowCount() > 0);
		}
		else {
			$stm = $this->db->query('UPDATE rr SET name = '.$this->db->quote($record->getName() == '@'?$zone->getName().'.':$record->getName().'.'.$zone->getName().'.')
				.', data = '.$this->db->quote($record->getContentString())
				.', aux = '.$this->db->quote(($record->fieldExists('priority')?$record->getField('priority'):'0'))
				.', ttl = '.$this->db->quote($record->getTTL())
				.' WHERE id = '.$this->db->quote($recordid)
				.' AND zone = '.$this->db->quote($this->zoneIds[$zone->getName()])
				.' AND type = '.$this->db->quote($record->getType())
			);
			return ($stm->rowCount() > 0);
		}
	}

	public function zoneCreate(Zone $zone) {
		if ($this->zoneExists($zone)) throw new ZoneExistsException('Zone \''.$zone->getName().'\' already exists.!');
		$stm = $this->db->query('INSERT INTO soa (origin,ns,mbox,serial) VALUES ('
			.$this->db->quote($zone->getName()).','
			.$this->db->quote('ns1.'.$zone->getName()).','
			.$this->db->quote('hostmaster.'.$zone->getName()).','
			.$this->db->quote(gmdate('Ymd00')).')'
		);
		return ($stm->rowCount() > 0);
	}

	public function zoneDelete(Zone $zone) {
		$this->zoneAssureExistence($zone);
		$stm = $this->db->query('SELECT id FROM soa WHERE origin = '.$this->db->quote($zone->getName()));
		$tmpZone = $stm->fetch();
		$zoneId = $tmpZone['id'];
		$this->db->query('DELETE FROM rr WHERE zone = '.$this->db->quote($zoneId));
		$stm = $this->db->query('DELETE FROM soa WHERE id = '.$this->db->quote($zoneId));
		unset($this->zoneIds[$zone->getName()]);
		return true;
	}

	public function zoneExists(Zone $zone) {
		$stm = $this->db->query('SELECT id FROM soa WHERE origin = '.$this->db->quote($zone->getName()));
		if ($stm->rowCount() > 0) {
			$tmp = $stm->fetch();
			$this->zoneIds[$zone->getName()] = $tmp['id'];
			return true;
		}
		else {
			unset($this->zoneIds[$zone->getName()]);
			return false;
		}
	}
}

?>