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
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class ZoneRouter extends RequestRouter {

	/** @var Zone instance of zone object */
	private $zone = null;

	public function __construct(Zone $zone) {
		$this->zone = $zone;
	}

	public function __default() {
		return $this->records();
	}

	private function listRecords() {
		if (isset($_GET['filter']) && is_array($_GET['filter'])) {
			foreach ($_GET['filter'] as $key => $value) {
				$filter[$key] = urldecode($value);
			}
		}
		$records = $this->zone->listRecordsByFilter($filter);
		foreach ($records as $recordid => $record) {
			$records[$recordid] = $this->record2Json($recordid, $record);
		}
		return $records;
	}

	function records($recordid = null) {
		$result = new stdClass();
		if ($this->endOfTracking() && $recordid === null) {
			if (RequestRouter::getRequestType() == 'PUT') {
				$data = RequestRouter::getRequestData();
				if (!isset($data['type'])) {
					$result->success = false;
					$result->error = 'No record type specified!';
				}
				elseif (!isset($data['name'])) {
					throw new InvalidFieldDataException('name is empty!');
				}
				elseif (!isset($data['ttl'])) {
					throw new InvalidFieldDataException('ttl is empty!');
				}
				elseif (!isset($data['fields'])) {
					throw new InvalidFieldDataException('No field values given!');
				}
				else {
					// workaround to avoid php warnings
					$prio = isset($data['fields']['priority']) ? $data['fields']['priority'] : null;
					$record = ResourceRecord::getInstance($data['type'], $data['name'], $data['fields'], $data['ttl'], $prio);
					$newid = $this->zone->recordAdd($record);
					$result->success = true;
					$result->newid = $newid;
					$result->records = $this->listRecords();
				}
			}
			else {
				$result->records = $this->listRecords();
				$result->success = true;
			}
		}
		elseif ($this->endOfTracking() && $recordid !== null) {
			$record = $this->zone->getRecordById($recordid);
			if ($this->getRequestType() == 'POST') {
				/**
				 * @TODO implement
				 */
			}
			elseif ($this->getRequestType() == 'DELETE') {
				$result->success = $this->zone->recordDel($recordid);
				$result->records = $this->listRecords();
			}
			else {
				$result->success = true;
				$result->record = $record;
			}
		}
		return $result;
	}

	private function record2Json($recordid, ResourceRecord $record) {
		$result = new stdClass();
		$result->id = $recordid;
		$result->name = $record->getName();
		$result->type = $record->getType();
		$result->content = strval($record);
		$result->fields = array();
		$fields = $record->listFields();
		foreach ($fields as $fieldname => $simpletype) {
			$result->fields[$fieldname] = new stdClass();
			$result->fields[$fieldname]->type = $simpletype;
			$result->fields[$fieldname]->value = $record->getField($fieldname);
		}
		$viewinfo = $record->getViewinfo();
		if (count($viewinfo) > 0) {
			$result->views = $viewinfo;
		}
		$result->ttl = $record->getTTL();
		return $result;
	}

	function views() {
		if ($this->zone->getModule() instanceof Views) {

		} else {
			return new stdClass();
		}
	}

}
?>