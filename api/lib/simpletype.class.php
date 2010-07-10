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
 * @subpackage Core
 */

/**
 * @package phpDNSAdmin
 * @subpackage Core
 */
class InvalidTypeException extends Exception { }

/**
 * @package phpDNSAdmin
 * @subpackage Core
 */
abstract class SimpleType {

	protected $content;

	public function __construct($content) {
		$this->content = $content;
	}

	public function __toString() {
		return $this->normalize();
	}

  /**
   * Validate data against data type
   * @param string $string data to check
   * @return bool true if valid, otherwise false
   */
	abstract public static function isValidValue($string);

  /**
   * Normalize data, e.g. cut off leading zeros
   * @param string $string given data
   * @return string normalized data
   */
  abstract public static function normalizeValue($string);

}

?>