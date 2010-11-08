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
class JsonZone extends ZoneModule {

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
		curl_setopt_array($this->curl,array(
			'CURLOPT_URL' => $url,
			'CURLOPT_RETURNTRANSFER' => true,
			'CURLOPT_USERAGENT' => 'phpDNSAdmin Json Client',
			'CURLOPT_CUSTOMREQUEST' => $method
		));
		$result = curl_exec($cr);
		return json_decode($result);
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array()) {

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

	public function recordAdd(Zone $zone, ResourceRecord $record) {

	}

	public function recordDelete(Zone $zone, $recordid) {

	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {

	}

	public function zoneCreate(Zone $zone) {

	}

	public function zoneDelete(Zone $zone) {
		$result = $this->httpGet($this->apiBase.'/servers/'.$this->server.'/zones/'.$zone->getName());
		
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