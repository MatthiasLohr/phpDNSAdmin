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
class Zone {

	/** @var string */
	private $name;

	/** @var ZoneModule */
	private $module;

	/**
	 * Constructor. Create a new instance of this zone with the given name and the
	 * according zone module
	 *
	 * @param string $zoneName
	 * @param ZoneModule $zoneModule
	 */
	public function  __construct($zoneName,ZoneModule $zoneModule) {
		if(!Hostname::isValidValue($zoneName)) {
		 throw new InvalidFieldDataException("\'$zoneName\' is no valid hostname.");
		}
		$this->name = $zoneName;
		$this->module = $zoneModule;
	}

	/**
	 * Return record count for the specified filter criteria
	 * 
	 * @param array $filter filter criteria
	 * @return amount of records matching the filter criterias
	 */
	public function countRecordsByFilter($filter) {
		return $this->module->countRecordsByFilter($this,$filter);
	}

	/**
	 * Create this zone in the zone module
	 *
	 * @return boolean true on success, false otherwise
	 */
	public function create() {
		return $this->module->zoneCreate($this->getName());
	}

	/**
	 * Delete this zone in the zone module
	 *
	 * @return boolean true on success, false otherwise
	 */
	public function delete() {
		return $this->module->zoneDelete($this);
	}

	/**
	 * Check if the zone exists in the according zone module
	 *
	 * @return boolean true/false if the zone exists
	 */
	public function exists() {
		return $this->module->zoneExists($this);
	}

	/**
	 * Returns the zone module of this zone
	 *
	 * @return ZoneModule
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Return the name of this zone
	 *
	 * @return string name of the zone
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Give the record with the specified id
	 *
	 * @param int $recordid
	 * @return ResourceRecord
	 */
	public function getRecordById($recordid) {
		return $this->module->getRecordById($this,$recordid);
	}

	/**
	 * List all records in this zone
	 *
	 * @return ResourceRecord[] list of zone records
	 */
	public function listRecords() {
		return $this->module->listRecords($this);
	}

	/**
	 * List all records in this zone with the specified filter criteria
	 *
	 * @param array $filter filter criteria
	 * @return ResourceRecord[] list of zone records
	 */
	public function listRecordsByFilter($filter, $offset = 0, $limit = null) {
		return $this->module->listRecordsByFilter($this,$filter,$offset,$limit);
	}

	/**
	 * Add a record to the zone
	 *
	 * @param ResourceRecord $record
	 * @return boolean success true/false
	 */
	public function recordAdd(ResourceRecord $record) {
		return $this->module->recordAdd($this,$record);
	}

	/**
	 * Update a record
	 *
	 * @param int $recordid
	 * @param ResourceRecord $record
	 * @return boolean success true/false
	 */
	public function recordUpdate($recordid, ResourceRecord $record) {
		return $this->module->recordUpdate($this, $recordid, $record);
	}

	/**
	 * Delete a record
	 *
	 * @param int $recordid
	 * @return boolean success true/false
	 */
	public function recordDelete($recordid) {
		return $this->module->recordDelete($this, $recordid);
	}
}

?>