<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2012 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
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
class InwxZone extends ZoneModule {

	private $apiServer = 'https://api.domrobot.com/xmlrpc/';
	private $cookieFile = null;

	private $zones = null;

	protected function __construct($config) {
		// prepare module
		$this->cookieFile = tempnam('/tmp','idr');
		// login
		$result = $this->login($config['username'],$config['password']);
		if ($result['code'] != 1000) {
			if ($result['code'] == 2200) {
				throw new ModuleConfigException('Invalid username/password combination');
			}
			else {
				var_dump($result);
				throw new ModuleConfigException('Unknown error on login request');
			}
		}
	}

	public function  __destruct() {
		unlink($this->cookieFile);
	}

	private function callRemote($object, array $parameters = array()) {
		$request = xmlrpc_encode_request($object, $parameters, array(
			'encoding'  => 'UTF-8',
			'escaping'  => 'markup',
			'verbosity' => 'no_white_space'
		));

		$ch = curl_init();
		curl_setopt_array($ch,array(
			CURLOPT_URL => $this->apiServer,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT => 65,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => '',
			CURLOPT_COOKIEFILE => $this->cookieFile,
			CURLOPT_COOKIEJAR => $this->cookieFile,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: text/xml','Connection: keep-alive','Keep-Alive: 300'
			),
		));
		$response = curl_exec($ch);
		curl_close($ch);

		return xmlrpc_decode($response,'UTF-8');
	}

	public function countRecordsByFilter(Zone $zone, array $filter = array()) {

	}

	public function getFeatures() {
		return array(
			'dnssec' => false,
			'rrtypes' => array(
				'A', 'AAAA', 'AFSDB', 'CERT', 'CNAME', 'DS', 'HINFO', 'KEY', 'LOC', 'MX',
				'NAPTR', 'NS', 'PTR', 'RP', 'SOA', 'SPF', 'SRV', 'SSHFP', 'TXT', 'URL'
			)
		);
	}

	public static function getInstance($config) {
		return new InwxZone($config);
	}

	public function getRecordById(Zone $zone, $recordid) {
		$records = $this->listRecordsByFilter($zone);
		if (isset($records[$recordid])) return $records[$recordid];
		return null;
	}

	private function hostnameLong2Short(Zone $zone, $hostname) {
		if ($hostname == $zone->getName()) {
			return '@';
		} else {
			return substr($hostname, 0, -(strlen($zone->getName()) + 1));
		}
	}

	private function hostnameShort2Long(Zone $zone, $hostname) {
		if ($hostname == '@') {
			return $zone->getName();
		} else {
			return $hostname . '.' . $zone->getName();
		}
	}

	public function listRecordsByFilter(Zone $zone, array $filter = array(), $offset = 0, $limit = null, $sortoptions = '') {
		$result = $this->callRemote('nameserver.info',array('domain' => $zone->getName()));
		$records = array();
		foreach ($result['resData']['record'] as $record) {
			// patch some record data
			switch ($record['type']) {
				case 'SOA':
					$content = explode(' ',$record['content']);
					$content[1] = str_replace('@','.',$content[1]);
					$content[3] = isset($content[3])?$content[3]:14400;
					$content[4] = isset($content[4])?$content[4]:3600;
					$content[5] = isset($content[5])?$content[5]:604800;
					$content[6] = isset($content[6])?$content[6]:86400;
					$record['content'] = implode(' ',$content);
			}

			$id = $record['id'];
			$records[$id] = ResourceRecord::getInstance(
				$record['type'],
				$this->hostnameLong2Short($zone,$record['name']),
				$record['content'],
				$record['ttl'],
				$record['prio']
			);
		}

		// call helper functions
		$records = $this->helpFilter($records,$filter);
		$records = $this->helpSort($records,$sortoptions);
		$records = $this->helpPaging($records,$offset,$limit);
		return $records;
	}

	public function listZones() {
		$result = $this->callRemote('nameserver.list',array('domain' => '*','pagelimit' => 9999));
		$this->zones = array();
		foreach ($result['resData']['domains'] as $domain) {
			$zone = new Zone($domain['domain'],$this);
			$this->zones[$zone->getName()] = $zone;
		}
		return $this->zones;
	}

	private function login($username, $password) {
		return $this->callRemote('account.login',array('user' => $username, 'pass' => $password));
	}

	private function logout() {
		return $this->callRemote('account.logout');
	}

	public function recordAdd(Zone $zone, ResourceRecord $record) {
		$result = $this->callRemote('nameserver.createrecord',array(
			'domain' => $zone->getName(),
			'type' => $record->getType(),
			'content' => $record->getContentString(),
			'name' => $this->hostnameShort2Long($zone,$record->getName()),
			'ttl' => $record->getTTL(),
			'prio' => $record->fieldExists('priority')?$record->getField('priority'):0
		));
		if ($result['code'] == 1000) {
			return $result['resData']['id'];
		}
		else {
			return false;
		}
	}

	public function recordDelete(Zone $zone, $recordid) {
		$result = $this->callRemote('nameserver.deleterecord',array('id' => $recordid));
		if ($result['code'] == 1000) {
			return true;
		}
		else {
			return false;
		}
	}

	public function recordUpdate(Zone $zone, $recordid, ResourceRecord $record) {
		$result = $this->callRemote('nameserver.updaterecord',array(
			'id' => $recordid,
			'type' => $record->getType(),
			'content' => $record->getContentString(),
			'name' => $this->hostnameShort2Long($zone,$record->getName()),
			'ttl' => $record->getTTL(),
			'prio' => $record->fieldExists('priority')?$record->getField('priority'):0
		));
		if ($result['code'] == 1000) {
			return true;
		}
		else {
			return false;
		}
	}

	public function zoneCreate(Zone $zone) {
		$result = $this->callRemote('nameserver.create',array(
			'domain' => $zone->getName(),'type' => 'MASTER',
			'ns' => array('ns1.'.$zone->getName(),'ns2.'.$zone->getName())
			
		));
		if ($result['code'] == 1000) return true;
		return false;
	}

	public function zoneDelete(Zone $zone) {
		$result = $this->callRemote('nameserver.delete',array('domain' => $zone->getName()));
		if ($result['code'] == 1000) {
			return true;
		}
		else {
			return false;
		}
	}

	public function zoneExists(Zone $zone) {
		if ($this->zones === null) $this->listZones();
		return (isset($this->zones[$zone->getName()]));
	}

}

?>