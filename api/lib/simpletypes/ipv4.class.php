<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2010 Philip Rebohle - http://phpdnsadmin.sourceforge.net/
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
class IPv4 extends SimpleType {

	public function isValidValue($string) {
		$tmp = explode('.', $string);
		if (count($tmp) != 4) return false;
		for ($i = 0; $i < 4; $i++) {
			if (!is_numeric($tmp[$i])) return false;
			if ($tmp[$i] < 0 || $tmp[$i] > 255) return false;
		}
		return true;
	}

	public function normalizeValue($string) {
		if (!self::isValidValue($string))
			throw new InvalidTypeException($string . ' is no valid IPv4');
		$tmp = explode('.', $string);
		$result = intval($tmp[0]);
		for ($i = 1; $i < 4; $i++)
			$result .= '.' . intval($tmp[$i]);
		return $result;
	}
}

?>