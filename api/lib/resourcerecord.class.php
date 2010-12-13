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

/** path to rrtype classes */
define('RRTYPE_PATH',API_ROOT.'/lib/rrtypes');

/**
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
abstract class ResourceRecord {

	/** @var string hostname of this record */
	private $name;

	/** @var int Time-to-live for this record */
	private $ttl;

	/** @var array Array of field values */
	private $fieldValues = array();

	private $viewinfo;

	protected final function __construct($name,$content,$ttl,$priority = null,array $viewinfo = null) {
		if (!$this->setName($name)) {
			throw new InvalidFieldDataException('No valid record name!');
		}
		if (is_array($content)) {
			$this->fieldValues = $content;
		}
		else {
			$this->fieldValues = $this->parseContent($content);
		}
		if ($priority !== null) $this->setField('priority',$priority);
		// check fields
		foreach ($this->listFields() as $fieldname => $simpletype) {
			if (!isset($this->fieldValues[$fieldname])) throw new InvalidFieldDataException('Field '.$fieldname.' is empty!');
			$value = new $simpletype($this->fieldValues[$fieldname]);
			if (!$value->isValid()) throw new InvalidFieldDataException('No valid data for field '.$fieldname.' ('.$simpletype.')');
			$this->fieldValues[$fieldname] = $value->normalize();
		}
		// do the rest
		if (!$this->setTTL($ttl)) {
			throw new InvalidFieldDataException('No valid record ttl!');
		}
		$this->viewinfo = $viewinfo;
	}

	/**
	 * convert the record content to a string
	 *
	 * @return string string representation of record content
	 */
	public function __toString() {
		return $this->getContentString();
	}

	/**
	 * Check if this ResourceRecord equals to another one
	 *
	 * @param ResourceRecord $record ResourceRecord to compare
	 * @return boolean true/false: equal/not equal
	 */
	public function equals(ResourceRecord $record) {
		if ($this->getName() != $record->getName()) return false;
		if ($this->getType() != $record->getType()) return false;
		if ($this->getTTL() != $record->getTTL()) return false;
		foreach ($this->listFields() as $fieldname => $simpletype) {
			if ($this->getField($fieldname) != $record->getField($fieldname)) return false;
		}
		return true;
	}

	/**
	 * Check if the field exists in this ResourceRecord type
	 *
	 * @param string $fieldname
	 * @return boolean true/false
	 */
	public function fieldExists($fieldname) {
		$fields = $this->listFields();
		return isset($fields[$fieldname]);
	}

	/**
	 * return the name of a class which implements the given RRType
	 *
	 * @param string $type RRType string
	 * @return string name of the class which implements the given RRType
	 */
	public static final function getClassByType($type) {
		$assumedName = strtoupper(substr($type,0,1)).strtolower(substr($type,1)).'Record';
		if (file_exists(RRTYPE_PATH.'/'.strtolower($assumedName).'.class.php')) {
			require_once(RRTYPE_PATH.'/'.strtolower($assumedName).'.class.php');
			if (class_exists($assumedName)) return $assumedName;
		}
		else {
			return null;
		}
	}

	public function getContentString() {
		$tmp = array();
		foreach ($this->listFields() as $fieldname => $simpletype) {
			$tmp[] = $this->getField($fieldname);
		}
		return implode(' ',$tmp);
	}

	/**
	 * Return a field value
	 *
	 * @param string $fieldname
	 * @return string field value
	 */
	final public function getField($fieldname) {
		if (!$this->fieldExists($fieldname)) throw new NoSuchFieldException();
		return $this->fieldValues[$fieldname];
	}

	final public static function getInstance($type,$name,$content,$ttl,$priority = null, array $viewinfo = null) {
		$className = self::getClassByType($type);
		if ($className === null) throw new NotSupportedException('RRType '.$type.' is not supported yet!');
		$record = new $className($name,$content,$ttl,$priority,$viewinfo);
		return $record;
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
	public function getType() {
		if (get_class($this) == 'ResourceRecord') return null;
		return strtoupper(substr(get_class($this),0,-6));
	}

	public function getViewinfo() {
		return $this->viewinfo;
	}

	/**
	 * return a list of fields contained in the specific record type
	 *
	 * @return array fieldname => SimpleType
	 */
	abstract public static function listFields();

	public static final function listTypes() {
		$result = array();
		foreach (glob(RRTYPE_PATH.'/*record.class.php') as $filename) {
			$type = strtoupper(basename($filename,'record.class.php'));
			/*$tmp = new stdClass();
			$tmp->type = $type;*/
			//$result[] = $tmp;
      $result[] = $type;
		}
		return $result;
	}

	protected function parseContent($content) {
		$fields = $this->listFields();
		$fieldCount = count($fields);
		$values = explode(' ',$content,$fieldCount);
		$i = 0;
		$result = array();
		foreach ($fields as $key => $simpletype) {
			if ($key == 'priority') continue;
			$result[$key] = $values[$i];
			$i++;
		}
		return $result;
	}

	final public function setField($fieldname,$value) {
		if (!$this->fieldExists($fieldname)) throw new NoSuchFieldException();
		$fields = $this->listFields();
		$simpletype = $fields[$fieldname];
		$stypeInstance = new $simpletype($value);
		if ($stypeInstance->isValid()) {
			$this->fieldValues[$fieldname] = $value;
			return true;
		}
		else {
			return false;
		}
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

	final public function setViewinfo($viewinfo) {
		$this->viewinfo = $viewinfo;
	}
}

/**
 * @package phpDNSAdmin
 * @subpackage Exceptions
 */
class NoSuchFieldException extends Exception {}

?>