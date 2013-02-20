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
class Hostname extends SimpleType {

	public function isValidValue($string) {
		if (strpos($string, '.') === false)
			$tmp = array(0 => $string);
		else
			$tmp = explode('.', $string);
		foreach ($tmp as $index => $label) {
			if ($label == '' && $index == count($tmp) - 1) return true;
			if (strlen($label) < 1 || strlen($label) > 63) return false;
			for ($i = 0; $i < strlen($label); $i++)
				if (false === strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_', $label[$i]))
					return false;
			if ($label[0] == '-' || $label[strlen($label) - 1] == '-')
				return false;
		}
		return true;
	}

	public function normalizeValue($string) {
		if (!self::isValidValue($string))
			throw new InvalidTypeException($string . ' is no valid Hostname');
		if ($string[strlen($string) - 1] == '.')
			return substr($string, 0, -1);
		return $string;
	}
}

?>