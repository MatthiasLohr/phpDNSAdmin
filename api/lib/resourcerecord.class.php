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
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

define('RRTYPE_PATH',API_ROOT.'/lib/rrtypes');

/**
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
abstract class ResourceRecord {

	/**
	 * @var array registered record types
	 */
	private static $recordTypes = array();

	/**
	 * @var string hostname of this record
	 */
	private $name;

	/**
	 * @var int Time-to-live for this record
	 */
	private $ttl;

	/**
	 * @var array Array of field values
	 */
	protected $fieldValues = array();

	/**
	 * create a new instance of a record with the given content string
	 *
	 * @param string $name (host)name of this record entry
	 * @param string $content content string
	 * @param int $ttl Time-to-live for this record
	 * @param int $priority optional. priority for this record
	 */
	abstract public function __construct($name,$content,$ttl,$priority = null);

	/**
	 * convert the record content to a string
	 *
	 * @return string string representation of record content
	 */
	abstract public function __toString();

	/**
	 * Return a record with default values
	 *
	 * @param Zone $zone zone object of the zone in which the new record will be created
	 * @param string $name name of the new record
	 * @param int $ttl Time-to-live for the new record
	 * @return Record record with default values
	 */
	abstract public static function defaultRecord(Zone $zone, $name, $ttl);

	/**
	 * check if this record supports the given field
	 *
	 * @param string $fieldname fieldname to test
	 * @return bool true if this fields exists, false otherwise
	 */
	final public function fieldExists($fieldname) {
		$fields = $this->listFields();
		return (isset($fields[$fieldname]));
	}

	/**
	 * return a field value
	 *
	 * @param string $fieldname field to return
	 * @return string field value
	 * @throws FieldNotInitializedException if field not initialized
	 * @see setFieldByName
	 */
	final public function getFieldByName($fieldname) {
		$fields = $this->listFields();
		if (!isset($fields[$fieldname])) throw new NoSuchFieldException(
			'There is no field '.$fieldname
		);
		if (!isset($this->fieldValues[$fieldname])) return null;
		return $this->fieldValues[$fieldname];
	}

	/**
	 * return the (host)name of this record
	 *
	 * @return string name of this record
	 */
	final public function getName() {
		return $this->name;
	}

	/**
	 * return the name of a class which implements the given RRType
	 *
	 * @param string $type RRType string
	 * @return string name of the class which implements the given RRType
	 */
	public static final function getTypeClassName($type) {
		$assumedName = strtoupper(substr($type,0,1)).strtolower(substr($type,1)).'Record';
		if (file_exists(RRTYPE_PATH.'/'.strtolower($assumedName).'.class.php')) {
			require_once(RRTYPE_PATH.'/'.strtolower($assumedName).'.class.php');
			if (class_exists($assumedName)) return $assumedName;
		}
		else {
			return null;
		}
	}

	/**
	 * return the Time-to-live of this record
	 *
	 * @return int Time-to-live
	 */
	final public function getTTL() {
		return $this->ttl;
	}

	/**
	 * return the type string of this record
	 *
	 * @return string RRType string
	 */
	abstract public static function getTypeString();

	
	public static function instantiate($type,$name,$content,$ttl,$priority = null) {
		$className = self::getTypeClassName($type);
		return new $className($name,$content,$ttl,$priority);
	}

	/**
	 * return a list of fields contained in the specific record type
	 *
	 * @return array fieldname => SimpleType
	 */
	abstract public function listFields();

	public static final function listTypes() {
		$result = array();
		foreach (glob(RRTYPE_PATH.'/*record.class.php') as $filename) {
			$type = strtoupper(basename($filename,'record.class.php'));
			$tmp = new stdClass();
			$tmp->type = $type;
			$result[] = $tmp;
		}
		return $result;
	}

	/**
	 * Set the given field to a new value
	 *
	 * @param string $fieldname field to set
	 * @param string $value new value for the field
	 * @return true on success, false otherwise
	 */
	final public function setFieldByName($fieldname,$value) {
		if (!$this->fieldExists($fieldname)) return false;
			$a = $this->listFields();
			$ftype = $a[$fieldname];
			$tmp = new $ftype($value);
		if ($tmp->isValid()) {
			$this->fieldValues[$fieldname] = $value;
			return true;
		}
		else
			return false;
	}

	/**
	 * Set the (host)name of this record
	 *
	 * @param string $name (host)name
	 * @return true on success, false otherwise
	 * @todo implement type check
	 */
	final public function setName($name) {
		if (!(
				$name == '@' ||
				$name == '*' ||
				Hostname::isValidValue($name)
				|| (substr($name, 0, 2) == '*.' && Hostname::isValidValue(substr($name, 2, strlen($name) - 2)))
		)) return false;
		$this->name = $name;
		return true;
	}

	/**
	 * Set the (host)name of this record
	 *
	 * @param int $ttl Time-to-live
	 * @return true on success, false otherwise
	 * @todo implement type check
	 */
	final public function setTTL($ttl) {
		if (!UInt::isValidValue($ttl)) return false;
		$this->ttl = $ttl;
		return true;
	}
}

/**
 * @package phpDNSAdmin
 * @subpackage Exceptions
 */
class NoSuchFieldException extends Exception {}

/**
 * @package phpDNSAdmin
 * @subpackage Exceptions
 */
class FieldNotInitializedException extends Exception {}

?>