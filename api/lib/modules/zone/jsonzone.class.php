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
class JsonZone extends ZoneModule implements Views {

	private $apiBase = null;
	private $server = null;

	protected function __construct($config) {
		$this->apiBase = $config['api_base'];
		$this->server = $config['server_sysname'];
	}

	public function getFeatures() {
		return array(
			'dnssec',
			'rrtypes' => array(
				'A', 'AAAA', 'AFSDB', 'CERT', 'CNAME', 'DNSKEY', 'DS', 'HINFO', 'KEY', 'LOC',
				'MX', 'NAPTR', 'NS', 'NSEC', 'NSEC3', 'PTR', 'RP', 'RRSIG', 'SOA', 'SPF', 'SSHFP',
				'SRV', 'TXT'
			)
		);
	}

	public static function getInstance($config) {
		try {
			return new JsonZone($config);
		}
		catch (Exception $e) {
			return null;
		}
	}

	public function getRecordById(Zone $zone, $recordid) {
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records/'.$recordid);
		if ($result->success) {
			return ResourceRecord::getInstance($result->record->type,$result->record->name,$result->record->fields,$result->record->ttl,isset($result->record->fields->priority)?$result->record->priority:null);
		}
		else {
			return null;
		}
	}

	private function httpDelete($url, $args = array()) {
		return $this->httpRequest('DELETE',$url,$args);
	}

	private function httpGet($url, $args = array()) {
		return $this->httpRequest('GET',$url,$args);
	}

	private function httpPost($url, $args = array()) {
		return $this->httpRequest('POST',$url,$args);
	}

	private function httpPut($url, $args = array()) {
		return $this->httpRequest('PUT',$url,$args);
	}

	private function httpRequest($method, $url, $args) {
		$cr = curl_init();
		// init request
		curl_setopt_array($cr,array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'phpDNSAdmin Json Client',
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => http_build_query($args)
		));
		$result = curl_exec($cr);
		return json_decode($result);
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {
		$tmpFilter = array();
		$tmpFilter['filter'] = $filter;
		if (is_numeric($offset) && $offset > 0) $tmpFilter['offset'] = $offset;
		if (is_numeric($limit) && $limit > 0) $tmpFilter['limit'] = $limit;
		// sort options
		if (strlen($sortoptions) > 0) {
			$cols = explode(',',$sortoptions);
			if (substr($cols[0],0,1) == '-') {
				$colname = substr($cols[0],1);
				$order = 'DESC';
			}
			else {
				$colname = $cols[0];
				$order = 'ASC';
			}
			if (in_array($colname,array('id','name','type','content','ttl','priority'))) {
				$tmpFilter['sortby'] = $colname;
				$tmpFilter['sortorder'] = $order;
			}
		}
		// limit/offset
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records?'.http_build_query($tmpFilter));
		if ($result->success) {
			$records = array();
			foreach ($result->records as $recordId => $tmpRecord) {
				$records[$recordId] = ResourceRecord::getInstance($tmpRecord->type,$tmpRecord->name,$tmpRecord->fields,$tmpRecord->ttl,isset($tmpRecord->fields->priority)?$tmpRecord->priority:null);
			}
			return $records;
		}
		else {
			return array();
		}
	}

	public function listViews() {
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/views');
		if ($result->success && is_array($result->views)) {
			return $result->views;
		}
		else {
			return false;
		}
	}

	public function listZones() {
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/zones');
		if ($result->success) {
			$zones = array();
			foreach ($result->zones as $zoneName => $tmpZone) {
				$zone = new Zone($zoneName,$this);
				$zones[$zone->getName()] = $zone;
			}
			return $zones;
		}
		else {
			return array();
		}
	}

	public function incrementSerial(Zone $zone) {
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/incserial');
		return $result->success;
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$data = new stdClass();
		$data->name = $record->getName();
		$data->type = $record->getType();
		$data->content = $record->getContentString();
		$data->fields = array();
		$classname = ResourceRecord::getClassByType($record->getType());
		foreach (call_user_func(array($classname,'listFields')) as $fieldname => $simpletype) {
			$data->fields[$fieldname] = $record->getField($fieldname);
		}
		$data->ttl = $record->getTTL();
		$result = $this->httpPut($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records',$data);
		if ($result->success) {
			return $result->newid;
		}
		else {
			return null;
		}
	}

	public function recordDelete(Zone $zone, $recordid) {
		$result = $this->httpDelete($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records/'.$recordid);
		return $result->success;
	}

	public function recordSetViews(Zone $zone, $recordid, array $views) {
		$record = $this->getRecordById($zone,$recordid);
		$data = new stdClass();
		$data->type = $record->getType();
		$data->name = $record->getName();
		$data->ttl = $record->getTTL();
		$data->fields = array();
		foreach ($record->listFields as $fieldname => $simpletype) {
			$data->fields[$fieldname] = $record->getField($fieldname);
		}
		$data->views = $views;
		$result = $this->httpPost($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records/'.$recordid,$data);
		return $result->success;
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$data = new stdClass();
		$data->name = $record->getName();
		$data->type = $record->getType();
		$data->content = $record->getContentString();
		$data->fields = array();
		$classname = ResourceRecord::getClassByType($record->getType());
		foreach (call_user_func(array($classname,'listFields')) as $fieldname => $simpletype) {
			$data->fields[$fieldname] = $record->getField($fieldname);
		}
		$data->ttl = $record->getTTL();
		$result = $this->httpPost($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName().'/records/'.$recordid,$data);
		return $result->success;
	}

	public function zoneCreate(Zone $zone) {
		if ($this->zoneExists($zone)) return false;
		$data = array();
		$data->name = $zone->getName();
		$result = $this->httpPut($this->apiBase.'/servers/'.$this->server.'/zones',$data);
		return $result->success;
	}

	public function zoneDelete(Zone $zone) {
		$result = $this->httpDelete($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName());
		return $result->success;
	}

	public function zoneExists(Zone $zone) {
		$zones = $this->listZones();
		if (isset($zones[$zone->getName()])) {
			return true;
		}
		else {
			return false;
		}
	}

}

?>