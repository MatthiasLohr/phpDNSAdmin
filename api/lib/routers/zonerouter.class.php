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

	private function countRecordsByFilter($overrideFilter = array()) {
		$filter = array();
		// take GET filters
		if (isset($_GET['filter']) && is_array($_GET['filter'])) {
			foreach ($_GET['filter'] as $key => $value) {
				$filter[$key] = urldecode($value);
			}
		}
		// take override filters
		foreach ($overrideFilter as $filterName => $filterValue) {
			$filter[$filterName] = $filterValue;
		}
		return $this->zone->countRecordsByFilter($filter);
	}

	private function listRecordsByFilter($overrideFilter = array()) {
		$filter = array();
		// take GET filters
		if (isset($_GET['filter']) && is_array($_GET['filter'])) {
			foreach ($_GET['filter'] as $key => $value) {
				$filter[$key] = urldecode($value);
			}
		}
		// take override filters
		foreach ($overrideFilter as $filterName => $filterValue) {
			$filter[$filterName] = $filterValue;
		}
		// sort options
		$sortoptions = '';
		if (isset($_GET['sortby'])) {
			$sortoptions = strval($_GET['sortby']);
			if (isset($_GET['sortorder']) && $_GET['sortorder'] == 'DESC') {
				$sortoptions = '-'.$sortoptions;
			}
		}
		// limit+offset
		$offset = 0;
		$limit = null;
		if (isset($_GET['offset']) && is_numeric($_GET['offset']) && $_GET['offset'] >= 0) {
			$offset = $_GET['offset'];
		}
		if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) {
			$limit = $_GET['limit'];
		}

		$records = $this->zone->listRecordsByFilter($filter,$offset,$limit,$sortoptions);
		$result = array();
		foreach ($records as $recordid => $record) {
			$result[] = $this->record2Json($recordid, $record);
		}
		return $result;
	}

	public function incserial() {
		$result = new stdClass();
		$tmp = $this->zone->getModule()->incrementSerial($this->zone);
		if ($tmp === false) {
			$result->success = false;
		}
		else {
			$result->success = true;
			$result->newserial = $tmp;
		}
		return $result;
	}

	public function records($recordid = null) {
		$result = new stdClass();
		if ($this->endOfTracking() && $recordid === null) {
			if (RequestRouter::getRequestType() == 'PUT') {
				$data = RequestRouter::getRequestData();
				if (!isset($data['type'])) {
					$result->success = false;
					$result->error = 'No record type specified!';
				} elseif (!isset($data['name'])) {
					throw new InvalidFieldDataException('name is empty!');
				} elseif (!isset($data['ttl'])) {
					throw new InvalidFieldDataException('ttl is empty!');
				} elseif (!isset($data['fields'])) {
					throw new InvalidFieldDataException('No field values given!');
				} else {
					// workaround to avoid php warnings
					$prio = isset($data['fields']['priority']) ? $data['fields']['priority'] : null;
					$record = ResourceRecord::getInstance($data['type'], $data['name'], $data['fields'], $data['ttl'], $prio);
					$newid = $this->zone->recordAdd($record);
					$result->success = true;
					$result->newid = $newid;
					//$result->records = $this->listRecordsByFilter();
					$result->totalCount = $this->countRecordsByFilter();
				}
			} else {
				$result->records = $this->listRecordsByFilter();
				$result->totalCount = $this->countRecordsByFilter();
				$result->success = true;
			}
		} elseif ($this->endOfTracking() && $recordid !== null) {
			$record = $this->zone->getRecordById($recordid);
			if ($this->getRequestType() == 'POST') {
				$data = RequestRouter::getRequestData();
				if (!isset($data['type'])) {
					$result->success = false;
					$result->error = 'No record type specified!';
				} elseif (!isset($data['name'])) {
					throw new InvalidFieldDataException('name is empty!');
				} elseif (!isset($data['ttl'])) {
					throw new InvalidFieldDataException('ttl is empty!');
				} elseif (!isset($data['fields'])) {
					throw new InvalidFieldDataException('No field values given!');
				} else {
					// workaround to avoid php warnings
					$prio = isset($data['fields']['priority']) ? $data['fields']['priority'] : null;
					if(isset($data['views'])) {
						// format views
						foreach($data['views'] as $view => $value ) {
							$views[$view] = $value;
						}
					} else {
						$views = null;
					}
					
					$record = ResourceRecord::getInstance($data['type'], $data['name'], $data['fields'], $data['ttl'], $prio, $views);
					$this->zone->recordUpdate($recordid, $record);
					$result->success = true;
					//$result->records = $this->listRecordsByFilter();
					$result->totalCount = $this->countRecordsByFilter();
				}
			} elseif ($this->getRequestType() == 'DELETE') {
				$result->success = $this->zone->recordDelete($recordid);
				//$result->records = $this->listRecordsByFilter();
				$result->totalCount = $this->countRecordsByFilter();
			} else {
				if ($record === null) {
					$result->success = false;
					$result->error = 'No such record!';
				} else {
					$result->success = true;
					$result->record = $this->record2Json($recordid, $record);
				}
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

	public function views($view = null) {
		$result = new stdClass();
		if ($this->zone->getModule()->hasViews()) {
			$views = $this->zone->getModule()->listViews();
		} else {
			$views = null;
		}
		//
		if ($view === null) { // list all views
			if ($views !== null) {
				$result->success = true;
				$result->views = $views;
			} else {
				$result->success = false;
			}
		} else { // list records from one view
			$result->records = $this->listRecordsByFilter(array('view' => $view));
			$result->totalCount = $this->countRecordsByFilter(array('view' => $view));
			$result->success = true;
		}
		return $result;
	}

}

?>