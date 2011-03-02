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
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);

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

	public function getRecordById(Zone $zone,$recordid) {
		if ($recordid == -1) {
			// SOA
			$result = $this->db->query('SELECT origin,ns,mbox,serial,refresh,retry,expire,minimum,ttl FROM soa WHERE origin = '.$this->db->quote($zone->getName()));
			$tmprecord = $result->fetch();
			return new ResourceRecord(
				'@',
				array(
					'primary'     => $tmprecord['ns'],
					'hostmaster'  => $tmprecord['mbox'],
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
			// OTHER
			
		}
	}

	public function listRecordsByFilter(Zone $zone,array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {

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
		if ($record->getType() == 'SOA') return false;
		
	}

	public function recordDelete(Zone $zone, $recordid) {

	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		if ($record->getType() == 'SOA') {
			$result = $this->db->query('UPDATE soa SET ns = '.$record->getField('primary')
				.',mbox = '.$record->getField('hostmaster')
				.',serial = '.$record->getField('serial')
				.',refresh = '.$record->getField('refresh')
				.',retry = '.$record->getField('retry')
				.',expire = '.$record->getField('expire')
				.',minimum = '.$record->getField('negativettl')
				.',ttl = '.$record->getTTL()
				.' WHERE origin = '.$this->db->quote($zone->getName())
			);
			return ($result->affectedRows() > 0);
		}
		else {

		}
	}

	public function zoneCreate(Zone $zone) {

	}

	public function zoneDelete(Zone $zone) {

	}

	public function zoneExists(Zone $zone) {

	}
}

?>