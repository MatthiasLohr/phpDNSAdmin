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
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Routers
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class ServerRouter extends RequestRouter {

	private $zoneModule = null;

	public function __construct(ZoneModule $zoneModule) {
		$this->zoneModule = $zoneModule;
	}

	public function rrtypes() {

	}

	public function zones($zonename = null) {
		if ($zonename === null) {
			$result = array();
			$zones = $this->zoneModule->listZones();
			foreach ($zones as $zone) {
				$tmp = new stdClass();
				$tmp->name = $zone->getName();
				$result[] = $tmp;
			}
			return $result;
		}
		else {
			$zone = new Zone($zonename,$this->zoneModule);
			try {
				
			}
			catch(NoSuchZoneException $e) {
				$result = new stdClass();
				$result->error = 'Zone not found!';
				return $result;
			}
		}
	}
}

?>