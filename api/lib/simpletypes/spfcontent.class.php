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
class SpfContent extends SimpleType {
	public function isValid() {
		return self::isValidValue($this->content);
	}

	public function normalize() {
		return self::normalize($this->content);
	}

	public static function isValidValue($string) {
		$rules = explode(' ',$string);
		if ($rules[0] !== 'v=spf1') return false;
		$count = count($rules);
		for ($i = 1; $i < $count; $i++) {
			if (!SpfRule::isValidValue($rules[$i])) {
				return false;
			}
		}
		return true;
	}

	public static function normalizeValue($string) {
		if (!self::isValid($string))
			throw new InvalidTypeException($string.' is no valid SPF content');
		return $string;
	}
}

?>