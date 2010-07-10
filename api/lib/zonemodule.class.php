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
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
abstract class ZoneModule {
	/**
	 * Get a record by its unique ID
	 *
	 * @param integer $recordid ID of the record
	 * @return ResourceRecord the record (or null if it doesn't exist?)
	 */
	abstract public function getRecordById($recordid);

	/**
	 * Get a zone by full name
	 *
	 * @param string $zonename the name of the zone
	 * @return Zone the zone (or null if it doesn't exist?)
	 */
	abstract public function getZoneByName($zonename);


	/**
	 * Get all records from all zones
	 *
	 * @return ResourceRecord[] the records
	 */
	abstract public function listRecords();

	/**
	 * Get records of a specific type from all zones
	 *
	 * @param Something $type record type to search for
	 * @return ResourceRecord[] matching records
	 */
	abstract public function listRecordsByType($type);

	/**
	 * Get all zones
	 *
	 * @return Zone[] all zones
	 */
	abstract public function listZones();

	/**
	 * Add a new record to a specific zone
	 *
	 * @param Zone $zone zone to add record to
	 * @param ResourceRecord $record record to add
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function recordAdd(Zone $zone,ResourceRecord $record);

	/**
	 * Delete a record from a specific zone
	 *
	 * @param Zone $zone zone to add record to
	 * @param integer $recordid ID of the record to delete
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function recordDelete(Zone $zone, $recordid);

	/**
	 * Overwrite a specific record
	 *
	 * @param Zone $zone zone to add record to
	 * @param integer $recordid ID of the record to update
	 * @param ResourceRecord $record new record
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record);

	/**
	 * Create a new zone
	 *
	 * @param Zone $zone zone to add
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function zoneAdd(Zone $zone);

	/**
	 * Delete a zone
	 *
	 * @param Zone $zone zone to remove
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function zoneDelete(Zone $zone);
}

?>