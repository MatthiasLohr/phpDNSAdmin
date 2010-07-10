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
class DsRecord extends ResourceRecord {

	public function __construct($name,$content,$ttl,$priority = null) {
		$this->setName($name);
		$this->setTTL($ttl);
		list(
			$keytag,
			$algorithm,
			$digesttype,
			$digest
		) = explode(' ',$content);
		$this->setFieldByName('keytag',$keytag);
		$this->setFieldByName('algorithm',$algorithm);
		$this->setFieldByName('digesttype',$digesttype);
		$this->setFieldByName('digest',$digest);
	}

	public function __toString() {
		return implode(' ',array(
			strval($this->getFieldByName('keytag')),
			strval($this->getFieldByName('algorithm')),
			strval($this->getFieldByName('digesttype')),
			strval($this->getFieldByName('digest'))
		));
	}

  public static function defaultRecord(Zone $zone, $name, $ttl) {
    return new DsRecord($name,'   ',$ttl);
  }

  public static function getTypeString() {
		return 'DS';
	}

	public function listFields() {
		return array(
			'keytag' => 'UInt16',
			'algorithm' => 'UInt8',
			'digesttype' => 'UInt8',
			'digest' => 'Base64Content'
		);
	}
}

?>