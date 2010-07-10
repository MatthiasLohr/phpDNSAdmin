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
 * @subpackage ResourceRecords
 */

/**
 * @package phpDNSAdmin
 * @subpackage ResourceRecords
 */
class DnskeyRecord extends ResourceRecord {

	public function __construct($name,$content,$ttl,$priority = null) {
		$this->setName($name);
		$this->setTTL($ttl);
		list(
			$flags,
			$protocol,
			$algorithm,
			$pubkey
		) = explode(' ',$content);
		$this->setFieldByName('flags',$flags);
		$this->setFieldByName('protocol',$protocol);
		$this->setFieldByName('algorithm',$algorithm);
		$this->setFieldByName('pubkey',$pubkey);
	}

	public function __toString() {
		return implode(' ',array(
			strval($this->getFieldByName('flags')),
			strval($this->getFieldByName('protocol')),
			strval($this->getFieldByName('algorithm')),
			strval($this->getFieldByName('pubkey'))
		));
	}

  public static function defaultRecord(Zone $zone, $name, $ttl) {
    return new DnskeyRecord($name,' 3  ',$ttl);
  }

  public static function getTypeString() {
		return 'DNSKEY';
	}

	public function listFields() {
		return array(
			'flags' => 'UInt16',
			'protocol' => 'DnskeyProtocol',
			'algorithm' => 'UInt8',
			'pubkey' => 'Base64Content'
		);
	}
}

?>