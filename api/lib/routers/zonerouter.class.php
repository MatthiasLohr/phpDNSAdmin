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

	function records($recordid = null) {
		if ($recordid === null) {
			// create new record
			if (RequestRouter::getRequestType() == 'PUT') {

			}
			$result = array();
			$records = $this->zone->listRecords();
			foreach ($records as $recordid => $record) {
				$result[$recordid] = $this->record2Json($recordid,$record);
			}
			return $result;
		}
		else {
			// delete record
			if (RequestRouter::getRequestType() == 'DELETE') {

				return $this->records();
			}
			// return the one record
			$record = $this->zone->getRecordById($recordid);
			if ($record === null) {
				return new stdClass();
			}
			else {
				return $this->record2Json($recordid,$record);
			}
		}
	}

	private function record2Json($recordid,ResourceRecord $record) {
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

		}
		else {
			return new stdClass();
		}
	}
}

?>