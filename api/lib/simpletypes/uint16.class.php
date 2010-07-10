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
class UInt16 extends SimpleType {
	public function isValid() {
		return self::isValidValue($this->content);
	}

	public function normalize() {
		return self::normalize($this->content);
	}

	public static function isValidValue($string) {
		return (preg_match('!^[0-9]+$!',$string) && $string >= 0 && $string <= 65535);
	}

	public static function normalizeValue($string) {
		if (!self::isValid($string))
			throw new InvalidTypeException($string . ' is no valid UInt');
		return intval($string);
	}
}

?>