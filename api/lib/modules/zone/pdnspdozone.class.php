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

	protected function __construct($config) {
		$this->db = new PDO($config['pdo_dsn'],$config['pdo_username'],$config['pdo_password']);
	}

	public static function getInstance($config) {
		return new PdnsPdoZone($config);
	}

	public function getRecordById($recordid) {

	}

	public function getZoneByName($zonename) {

	}

	public function listRecords() {

	}

	public function listRecordsByType($type) {

	}

	public function listZones() {
		$result = array();
		$stm = $this->db->query('SELECT name FROM domains WHERE type = \'MASTER\'');
		while ($row = $stm->fetch()) {
			$zone = new Zone($row['name'],$this);
			$result[] = $zone;
		}
		return $result;
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {

	}

	public function recordDelete(Zone $zone, $recordid) {

	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {

	}

	public function zoneAdd(Zone $zone) {

	}

	public function zoneDelete(Zone $zone) {

	}
}

?>