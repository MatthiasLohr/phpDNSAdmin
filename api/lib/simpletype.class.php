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

	/** @var string value of the simpletype */
	protected $content;

	/**
	 * Constructor. Create a new SimpleType instance
	 *
	 * @param string $content
	 */
	public function __construct($content) {
		$this->content = $content;
	}

	/**
	 * Convert this instance to a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->normalize();
	}

	/**
	 * Is the content valid?
	 *
	 * @return boolean yes/no
	 */
	public function isValid() {
		return $this->isValidValue($this->content);
	}
	
  /**
   * Validate data against data type
	 *
   * @param string $string data to check
   * @return bool true if valid, otherwise false
   */
	abstract public function isValidValue($string);

	/**
	 * Return the normalized content from this instance
	 *
	 * @return string normalized content
	 */
	public function normalize() {
		return $this->normalizeValue($this->content);
	}

  /**
   * Normalize data, e.g. cut off leading zeros
	 *
   * @param string $string given data
   * @return string normalized data
   */
  abstract public function normalizeValue($string);

}

?>