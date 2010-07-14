<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2010 University of Trier - http://phpdnsadmin.sourceforge.net/
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
 * @author Matthias Lohr <lohr@uni-trier.de>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Zone
 * @author Matthias Lohr <lohr@uni-trier.de>
 */
class PdnsPdoViewsZone extends ZoneModule implements Views {

	private $modules = array();

	public function __construct($moduleConfig) {

	}

	public function getFeatures() {

	}

	public function getRecordById(Zone $zone, integer $recordid) {

	}

	public function listRecords(Zone $zone) {

	}

	public function listRecordsByType(Zone $zone, Something $type) {

	}

	public function listViews() {

	}

	public function listZones() {

	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {

	}

	public function recordDelete(Zone $zone, integer $recordid) {

	}

	public function recordUpdate(Zone $zone, integer $recordid, ResourceRecord $record) {

	}

	public function zoneCreate($zonename) {

	}

	public function zoneDelete(Zone $zone) {

	}

	public function zoneExists(Zone $zone) {

	}
}

?>