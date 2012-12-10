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
	 * Return record count for the specified filter criteria
	 *
	 * @param Zone $zone zone object
	 * @param array $filter filter criteria
	 * @return amount of records matching the filter criterias
	 */
	public function countRecordsByFilter(Zone $zone,array $filter = array()) {
		$result = $this->listRecordsByFilter($zone,$filter);
		if (is_array($result)) {
			return count($result);
		}
		else {
			return 0;
		}
	}

	/**
	 * Return an array with feature information
	 *
	 * @return array feature list
	 */
	abstract public function getFeatures();

	/**
	 * Give an instance with the given config
	 *
	 * @param array $config
	 * @return ZoneModule zone module instance
	 * @throws ModuleConfigException on errors
	 */
	abstract public static function getInstance($config);

	/**
	 * Get a record by its unique ID
	 *
	 * @param integer $recordid ID of the record
	 * @return ResourceRecord the record (or null if it doesn't exist?)
	 */
	abstract public function getRecordById(Zone $zone,$recordid);

	/**
	 * Get a zone by full name
	 *
	 * @param string $zonename the name of the zone
	 * @return Zone the zone (or null if it doesn't exist)
	 */
	public function getZoneByName($zonename) {
		$tmpZone = new Zone($zonename,$this);
		if ($this->zoneExists($zone)) {
			return $tmpZone;
		}
		else {
			return null;
		}
	}

	/**
	 * Check if this zone module supports views
	 *
	 * @return boolean true/false
	 */
	public final function hasViews() {
		if ($this instanceof Views) {
			$views = $this->listViews();
			if (is_array($views) && count($views) > 1) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Helper function for filtering records without backend filter support
	 *
	 * @param array $records array of records to be filtered
	 * @param array $filter filter definition
	 * @return array array of filtered records
	 */
	protected static function helpFilter(array $records, array $filter) {
		// shortcut for id filtering (only 1 result record)
		if (isset($filter['id'])) {
			if (isset($records[$filter['id']])) {
				return array($filter['id'] => $records[$filter['id']]);
			}
			else {
				return array();
			}
		}
		// apply other filters
		foreach ($records as $id => $record) {
			if (isset($filter['name']) && $filter['name'] != $record->getName()) {
				unset($records[$id]);
				continue;
			}
			if (isset($filter['type']) && $filter['type'] != $record->getType()) {
				unset($records[$id]);
				continue;
			}
			if (isset($filter['content']) && $filter['content'] != $record->getContentString()) {
				unset($records[$id]);
				continue;
			}
			if (isset($filter['ttl']) && $filter['ttl'] != $record->getTTL()) {
				unset($records[$id]);
				continue;
			}
		}
		return $records;
	}

	/**
	 * Helper function for paging without backend paging support
	 *
	 * @param array $records array of all records
	 * @param type $offset index of first record in result array
	 * @param type $limit amount of max. records in result array
	 * @return array array of records
	 */
	protected static function helpPaging(array $records, $offset = 0, $limit = null) {
		if ($limit === null) {
			return array_slice($records,$offset);
		}
		else {
			return array_slice($records,$offset,$limit);
		}
	}

	/**
	 * Helper function for sorting records without backend sorting support
	 *
	 * @param array $records unsorted records
	 * @param type $sortoptions sort options
	 * @return array sorted array of records
	 */
	protected static function helpSort(array $records, $sortoptions = '') {
		$cycles = explode(',',$sortoptions);
		foreach ($cycles as $cycle) {
			$inverse = (substr($cycle,0,1) == '-');
			$subject = substr($cycle,$inverse?1:0);
			switch ($subject) {
				case 'id':
					if ($inverse) {
						krsort($records,SORT_NUMERIC);
					}
					else {
						ksort($records,SORT_NUMERIC);
					}
					break;
				case 'name':
					uasort($records,array('self','helpSortCompareName'));
					if ($inverse) $records = array_reverse($records,true);
					break;
				case 'type':
					uasort($records,array('self','helpSortCompareType'));
					if ($inverse) $records = array_reverse($records,true);
					break;
				case 'content':
					uasort($records,array('self','helpSortCompareContent'));
					if ($inverse) $records = array_reverse($records,true);
					break;
				case 'ttl':
					uasort($records,array('self','helpSortCompareTTL'));
					if ($inverse) $records = array_reverse($records,true);
					break;
				case 'priority':
					uasort($records,array('self','helpSortComparePriority'));
					if ($inverse) $records = array_reverse($records,true);
					break;
			}
		}
		return $records;
	}

	/**
	 * Helper function for sorting: compare the content of two records
	 *
	 * @param ResourceRecord $a record $a
	 * @param ResourceRecord $b record $b
	 * @return int 0: both records are equal, -1: $a is predecessor of $b, 1: $b is predecessor of $a
	 */
	private static function helpSortCompareContent(ResourceRecord $a, ResourceRecord $b) {
		return strcmp($a->getContentString(),$b->getContentString());
	}

	/**
	 * Helper function for sorting: compare the name of two records
	 *
	 * @param ResourceRecord $a record $a
	 * @param ResourceRecord $b record $b
	 * @return int 0: both records are equal, -1: $a is predecessor of $b, 1: $b is predecessor of $a
	 */
	private static function helpSortCompareName(ResourceRecord $a, ResourceRecord $b) {
		$arg1 = $a->getName();
		$arg2 = $b->getName();
		if ($arg1 == $arg2) return 0;
		$arg1a = explode('.', $arg1);
		$arg2a = explode('.', $arg2);
		while (count($arg1a) && count($arg2a)) {
			$head1 = array_pop($arg1a);
			$head2 = array_pop($arg2a);
			$cmp = strcasecmp($head1, $head2);
			if ($cmp != 0) return $cmp;
		}
		return (count($arg1a) - count($arg2a));
	}

	/**
	 * Helper function for sorting: compare the priority of two records
	 *
	 * @param ResourceRecord $a record $a
	 * @param ResourceRecord $b record $b
	 * @return int 0: both records are equal, -1: $a is predecessor of $b, 1: $b is predecessor of $a
	 */
	private static function helpSortComparePriority(ResourceRecord $a, ResourceRecord $b) {
		return ($a->getField('priority') < $b->getField('priority'))?-1:(($a->getField('priority') > $b->getField('priority'))?1:0);
	}

	/**
	 * Helper function for sorting: compare the TTL of two records
	 *
	 * @param ResourceRecord $a record $a
	 * @param ResourceRecord $b record $b
	 * @return int 0: both records are equal, -1: $a is predecessor of $b, 1: $b is predecessor of $a
	 */
	private static function helpSortCompareTTL(ResourceRecord $a, ResourceRecord $b) {
		return ($a->getTTL() < $b->getTTL())?-1:(($a->getTTL() > $b->getTTL())?1:0);
	}

	/**
	 * Helper function for sorting: compare the type of two records
	 *
	 * @param ResourceRecord $a record $a
	 * @param ResourceRecord $b record $b
	 * @return int 0: both records are equal, -1: $a is predecessor of $b, 1: $b is predecessor of $a
	 */
	private static function helpSortCompareType(ResourceRecord $a, ResourceRecord $b) {
		return strcmp($a->getType(),$b->getType());
	}

	/**
	 * Shortcut function for fast serial incrementation (to trigger a AXFR)
	 *
	 * @param Zone $zone zone
	 * @return boolean true on success, false otherwise
	 */
	public function incrementSerial(Zone $zone) {
		$records = $this->listRecordsByType($zone,'SOA');
		if ($records === false || count($records) == 0) return false;
		$success = true;
		foreach ($records as $recordId => $soa) {
			if ($soa->getName() != '@') continue;
			$aSerial = intval(date('Ymd00'));
			$newSerial = max((intval($soa->getField('serial'))+1), $aSerial);
			$soa->setField('serial', $newSerial);
			$success = $this->recordUpdate($zone, $recordId, $soa) && $success;
		}
		return $success;
	}

	/**
	 * Get all records from all zones
	 *
	 * @param int $offset where to start
	 * @param int $limit max count of returned records
	 * @return ResourceRecord[] the records
	 */
	public function listRecords(Zone $zone, $offset = 0, $limit = null, $sortoptions = '') {
		return $this->listRecordsByFilter($zone,$offset,$limit,$sortoptions);
	}

	/**
	 * Give a list of records with specified filter criteria
	 *
	 * @param Zone $zone zone object
	 * @param array $filter filter criteria
	 * @param int $offset where to start
	 * @param int $limit max count of returned records
	 * @return ResourceRecord[] array with resource records
	 */
	abstract public function listRecordsByFilter(Zone $zone,array $filter = array(), $offset = 0, $limit = null, $sortoptions = '');

	/**
	 * Give all records with a specified name
	 *
	 * @param Zone $zone zone object
	 * @param string $name name to search for
	 * @param int $offset where to start
	 * @param int $limit max count of returned records
	 * @return ResourceRecord[] array with resource records
	 */
	public function listRecordsByname(Zone $zone,$name, $offset = 0, $limit = null, $sortoptions = '') {
		return $this->listRecordsByFilter($zone,array('name' => $name),$offset,$limit,$sortoptions);
	}

	/**
	 * Get records of a specific type from all zones
	 *
	 * @param string $type record type to search for
	 * @param int $offset where to start
	 * @param int $limit max count of returned records
	 * @return ResourceRecord[] matching records
	 */
	public function listRecordsByType(Zone $zone,$type, $offset = 0, $limit = null, $sortoptions = '') {
		return $this->listRecordsByFilter($zone,array('type' => $type),$offset,$limit,$sortoptions);
	}

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
	 * @return false or id of new record
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
	 * Create a new zone in this server
	 *
	 * @param Zone $zone zone object to create here
	 * @return boolean success true/false
	 */
	abstract public function zoneCreate(Zone $zone);

	/**
	 * Make the script explode if a zone doesn't exist
	 *
	 * @param Zone $zone zone object
	 * @return boolean alway true - or a fat BOOOM
	 */
	protected function zoneAssureExistence(Zone $zone) {
		if (!$this->zoneExists($zone)) throw new NoSuchZoneException('No zone '.$zone->getName().' here!');
		return true;
	}

	/**
	 * Delete a zone
	 *
	 * @param Zone $zone zone to remove
	 * @return boolean true on success, false otherwise?
	 */
	abstract public function zoneDelete(Zone $zone);

	/**
	 * Check if a zone exists
	 *
	 * @param Zone $zone
	 * @return true if the zone exists, false otherwise
	 */
	abstract public function zoneExists(Zone $zone);
}

?>