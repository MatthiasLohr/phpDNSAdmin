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

	protected function __construct($config) {

	}

	public function getFeatures() {

	}

	public static function getInstance($config) {
		
	}

	public function getRecordById(Zone $zone,$recordid) {

	}

	public function listRecordsByFilter(Zone $zone,array $filter = array()) {

	}

	public function listZones() {

	}

	public function recordAdd(Zone $zone,ResourceRecord $record) {

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