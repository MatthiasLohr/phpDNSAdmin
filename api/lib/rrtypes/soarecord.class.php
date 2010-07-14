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
class SoaRecord extends ResourceRecord {

	public function __construct($name,$content,$ttl,$priority = null) {
		$this->setName($name);
		$this->setTTL($ttl);
		list(
			$primary,
			$hostmaster,
			$serial,
			$refresh,
			$retry,
			$expire,
			$negativettl
		) = explode(' ',$content);
		$this->setFieldByName('primary',$primary);
		$this->setFieldByName('hostmaster',$hostmaster);
		$this->setFieldByName('serial',$serial);
		$this->setFieldByName('refresh',$refresh);
		$this->setFieldByName('retry',$retry);
		$this->setFieldByName('expire',$expire);
		$this->setFieldByName('negativettl',$negativettl);
	}

	public function __toString() {
		return implode(' ',array(
			strval($this->getFieldByName('primary')),
			strval($this->getFieldByName('hostmaster')),
			strval($this->getFieldByName('serial')),
			strval($this->getFieldByName('refresh')),
			strval($this->getFieldByName('retry')),
			strval($this->getFieldByName('expire')),
			strval($this->getFieldByName('negativettl'))
		));
	}

  public static function defaultRecord(Zone $zone,$name, $ttl) {
    return new SoaRecord($name, 'ns1.'.$zone->getName().' hostmaster@'.$zone->getName().' '.gmdate('Ymd00').' 3600 1800 604800 1800', $ttl);
  }

  public static function getTypeString() {
		return 'SOA';
	}

	private function hostmaster2Mail($hostmaster) {
		$result = '';
		$hasAtSign = false;
		for ($i = 0; $i < strlen($hostmaster); $i++)
			if ($hostmaster[$i] == '\\') {
				if ($hostmaster[++$i] == '.')
					$result .= '.';
			}
			else if ($hostmaster[$i] == '.' && !$hasAtSign) {
				$hasAtSign = true;
				$result .= '@';
			}
			else
				$result .= $hostmaster[$i];
		return $result;
	}

	public static function listFields() {
		return array(
			'primary'     => 'Hostname',
			'hostmaster'  => 'Email',
			'serial'      => 'UInt',
			'refresh'     => 'UInt',
			'retry'       => 'UInt',
			'expire'      => 'UInt',
			'negativettl' => 'UInt'
		);
	}

	private function mail2Hostmaster($mail) {
		$result = '';
		$hasAtSign = false;
		for ($i = 0; $i < strlen($mail); $i++)
			if ($mail[$i] == '@') {
				$result .= '.';
				$hasAtSign = true;
			}
			else if ($mail[$i] == '.' && !$hasAtSign)
				$result .= '\\.';
			else
				$result .= $mail[$i];
		return $result;
	}

	public function updateSerial() {
		$this->serial = max($this->serial+1,gmdate('Ymd00'));
		return $this->serial;
	}
}

?>