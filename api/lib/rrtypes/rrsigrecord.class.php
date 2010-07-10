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
class RrsigRecord extends ResourceRecord {

	public function __construct($name,$content,$ttl,$priority = null) {
		$this->setName($name);
		$this->setTTL($ttl);
		list(
			$typecovered,
			$algorithm,
			$labelcount,
			$originalttl,
			$sigexpiration,
			$siginception,
			$keytag,
			$signer,
			$signature
		) = explode(' ',$content);
		$this->setFieldByName('typecovered',$typecovered);
		$this->setFieldByName('algorithm',$algorithm);
		$this->setFieldByName('labelcount',$labelcount);
		$this->setFieldByName('originalttl',$originalttl);
		$this->setFieldByName('sigexpiration',$sigexpiration);
		$this->setFieldByName('siginception',$siginception);
		$this->setFieldByName('keytag',$keytag);
		$this->setFieldByName('signer',$signer);
		$this->setFieldByName('signature',$signature);
		
	}

	public function __toString() {
		return implode(' ',array(
			strval($this->getFieldByName('typecovered')),
			strval($this->getFieldByName('algorithm')),
			strval($this->getFieldByName('labelcount')),
			strval($this->getFieldByName('originalttl')),
			strval($this->getFieldByName('sigexpiration')),
			strval($this->getFieldByName('siginception')),
			strval($this->getFieldByName('keytag')),
			strval($this->getFieldByName('signer')),
			strval($this->getFieldByName('signature'))
		));
	}

  public static function defaultRecord(Zone $zone, $name, $ttl) {
    return new RrsigRecord($name,'   86400     ',$ttl);
  }

  public static function getTypeString() {
		return 'RRSIG';
	}

	public function listFields() {
		return array(
			'typecovered' => 'StringNoSpaces',
			'algorithm' => 'UInt8',
			'labelcount' => 'UInt',
			'originalttl' => 'UInt',
			'sigexpiration' => 'UInt',
			'siginception' => 'UInt',
			'keytag' => 'UInt16',
			'signer' => 'Hostname',
			'signature' => 'Base64Content'
		);
	}
}

?>