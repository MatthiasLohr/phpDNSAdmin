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
class Email extends SimpleType {

	public static function isValidValue($string) {
		$tmp = explode('@', $string);
    if (count($tmp) != 2) return false;
    $tmp[0] = strtolower(preg_replace('/\(.*\)/', '', $tmp[0]));
    if ($tmp[0][0] == '.' || $tmp[0][strlen($tmp[0]) - 1] == '.') return false;
    for ($i = 0; $i < strlen($tmp[0]); $i++)
      if (false === strpos('abcdefghijklmnopqrstuvwxyz0-9.!#$%&\'*+-/=?^_`{|}~', $tmp[0][$i]))
        return false;
    return Hostname::isValidValue($tmp[1]);
	}

  public static function normalizeValue($string) {
    if (!self::isValidValue($string))
      throw new InvalidTypeException($string . ' is no valid Email');
    $tmp = explode('@', $string);
    return $tmp[0] . '@' . Hostname::normalizeValue($tmp[1]);
  }

}

?>