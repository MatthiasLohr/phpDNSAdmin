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
class BindDlzPdoZone extends ZoneModule {

	/** @var PDO */
	private $db = null;

	/** @var string */
	private $table = 'dns_records';

	protected function __construct($config) {
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);

		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO '.$this->db->quote($config['search_path']));
		}
	}

	public function getFeatures() {
		return array(
			'rrtypes' => array(
				'A', 'AAAA', 'AFSDB', 'CERT', 'CNAME', 'DNSKEY', 'DS', 'HINFO', 'KEY', 'LOC',
				'MX', 'NAPTR', 'NS', 'NSEC', 'NSEC3', 'PTR', 'RP', 'RRSIG', 'SOA', 'SPF', 'SSHFP',
				'SRV', 'TXT'
			)
		);
	}

	public static function getInstance($config) {
		return new BindDlzPdoZone($config);
	}

	public function getRecordById(Zone $zone,$recordid) {

	}

	public function listRecordsByFilter(Zone $zone,array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {

	}

	public function listZones() {
		$stm = $this->db->query('SELECT DISTINCT zone FROM '.$this->table.' ORDER BY zone ASC');
		$result = array();
		while ($tmpzone = $stm->fetch()) {
			$zone = new Zone($tmpzone['zone'],$this);
			$result[$zone->getName()] = $zone;
		}
		return $result;
	}

	public function recordAdd(Zone $zone,ResourceRecord $record) {
		$this->db->beginTransaction();
		$this->db->query('INSERT INTO '.$this->table.' (zone,host,type,data,ttl) VALUES ('.$this->db->quote($zone->getName()).','.$this->db->quote($record->getName()).','.$this->db->quote($record->getType()).','.$this->db->quote(strval($record)).','.$this->db->quote($record->getTTL()).')');
		$stm = $this->db->query('SELECT MAX(entry_id) FROM '.$this->table);
		$tmp = $stm->fetch;
		$recordid = $tmp[0];
		// additional fields?
		if ($record->getType() == 'SOA') {

		}
		elseif ($record->fieldExists('priority')) {
			$this->db->query('UPDATE '.$this->table.' SET mx_priority = '.$this->db->quote($record->getField('priority')).' WHERE entry_id = '.$this->db->quote($recordid));
		}
		$this->db->commit();
		return $recordid;
	}

	public function recordDelete(Zone $zone, $recordid) {
		$this->db->beginTransaction();

		$this->db->commit();
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {

	}

	public function zoneCreate(Zone $zone) {

	}

	public function zoneDelete(Zone $zone) {
		$stm = $this->db->query('DELETE FROM '.$this->table.' WHERE zone = '.$this->db->quote($zone->getName()));
		return ($stm->rowCount() > 0);
	}

	public function zoneExists(Zone $zone) {
		$stm = $this->db->query('SELECT COUNT(*) FROM '.$this->table.' WHERE zone = '.$this->db->quote($zone->getName()));
		$tmp = $stm->fetch();
		return($tmp[0] > 0);
	}
}

?>