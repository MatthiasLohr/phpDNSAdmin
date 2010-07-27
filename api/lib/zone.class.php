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

/**
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class Zone {

	/** @var string */
	private $name;

	/** @var ZoneModule */
	private $module;

	public function  __construct($zoneName,ZoneModule $zoneModule) {
		if(!Hostname::isValidValue($zoneName)) {
		 throw new InvalidFieldDataException("\'$zoneName\' is no valid hostname.");
		}
		$this->name = $zoneName;
		$this->module = $zoneModule;
	}

	public function create() {
		return $this->module->zoneCreate($this->getName());
	}

	public function delete() {
		return $this->module->zoneDelete($this);
	}

	public function exists() {
		return $this->module->zoneExists($this);
	}

	public function getModule() {
		return $this->module;
	}

	public function getName() {
		return $this->name;
	}

	public function getRecordById($recordid) {
		return $this->module->getRecordById($this,$recordid);
	}

	public function listRecords() {
		return $this->module->listRecords($this);
	}

	public function recordAdd(ResourceRecord $record) {
		return $this->module->recordAdd($this,$record);
	}

	public function recordUpdate($recordid, ResourceRecord $record) {
		return $this->module->recordUpdate($this, $recordid, $record);
	}

	public function recordDel($recordid) {
		return $this->module->recordDelete($this, $recordid);
	}
}

?>