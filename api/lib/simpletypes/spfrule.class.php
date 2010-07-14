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
 * @subpackage Simpletypes
 */

/**
 * @package phpDNSAdmin
 * @subpackage Simpletypes
 */
class SpfRule extends SimpleType {

	public static function isValidValue($string) {
		// remove qualifier if given
		if (in_array(substr($string,0,1),array('+','-','~','?'))) {
			$string = substr($string,1);
		}
		// check mechanisms/modifiers
		// a, mx mechanism
		if (preg_match('/^(a|mx)(\:([^\/]+))?(\/([0-9]+))?$/',$string,$matches)) {
			$domain = $matches[3];
			$subnet = $matches[5];
			$domainok = false;
			$subnetok = false;
			// check domain
			if (isset($domain)) {
				if ($domain === '') {
					$domainok = true;
				}
				else {
					$domainok = Hostname::isValidValue($domain);
				}
			}
			else {
				$domainok = true;
			}
			// check subnet
			if (isset($subnet)) {
				if ($subnet === '') {
					$subnetok = true;
				}
				else {
					$subnetok = (UInt::isValidValue($subnet) && $subnet >= 0 && subnet <= 128);
				}
			}
			else {
				$subnetok = true;
			}
			// return result
			return ($domainok && $subnetok);
		}
		// all mechanism
		elseif ($string === 'all') {
			return true;
		}
		// ip4 mechanism
		elseif (preg_match('/^ip4\:([^\/]+)(\/([0-9]+))?$/',$string,$matches)) {
			$ip = $matches[1];
			$subnet = isset($matches[3])?$matches[3]:'';
			if (!IPv4::isValidValue($ip)) return false;
			if ($subnet === '') return true;
			return (UInt::isValidValue($subnet) && $subnet >= 0 && $subnet <= 32);
		}
		// ip6 mechanism
		elseif (preg_match('/^ip6\:([^\/]+)(\/([0-9]+))?$/',$string,$matches)) {
			$ip = $matches[1];
			$subnet = isset($matches[3])?$matches[3]:'';
			if (!IPv6::isValidValue($ip)) return false;
			if ($subnet === '') return true;
			return (UInt::isValidValue($subnet) && $subnet >= 0 && $subnet <= 128);
		}
		// ptr,exists,include mechanism
		elseif (preg_match('/^(ptr|exists|include)\:(.*)$/',$string,$matches)) {
			$domain = $matches[2];
			return Hostname::isValidValue($domain);
		}
		// redirect/exp modifier
		elseif (preg_match('/^(redirect|exp)=(.*)$/',$string,$matches)) {
			$domain = $matches[2];
			return Hostname::isValidValue($domain);
		}
		else {
			return false;
		}
	}

	public static function normalizeValue($string) {
		if (!self::isValid($string))
			throw new InvalidTypeException($string . ' is no valid SPF rule');
		return $string;
	}
}

?>