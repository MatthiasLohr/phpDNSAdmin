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

abstract class ZoneModule {

	abstract public function getRecordById($recordid);
	abstract public function getZoneByName($zonename);

	abstract public function listRecords();
	abstract public function listRecordsByType($type);
	abstract public function listZones();

	abstract public function recordAdd(Zone $zone,ResourceRecord $record);
	abstract public function recordDelete(Zone $zone, $recordid);
	abstract public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record);

	abstract public function zoneAdd(Zone $zone);
	abstract public function zoneDelete(Zone $zone);
}

?>