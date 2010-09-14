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

	public function __default() {
		return $this->zones();
	}

	public function rrtypes() {
		$features = $this->zoneModule->getFeatures();
		$result = new stdClass();
		$result->rrtypes = array();
		foreach (array_values(array_intersect(ResourceRecord::listTypes(), $features['rrtypes'])) as $value) {
			$className = ResourceRecord::getClassByType($value);
			$rrtype = new stdClass();
			$rrtype->type = $value;
			$rrtype->fields = call_user_func(array($className, 'listFields'));

			$result->rrtypes[] = $rrtype;
		}
		return $result;
	}

	public function zones($zonename = null) {
		if ($this->endOfTracking()) {
			if ($zonename === null) {
				if ($this->getRequestType() == 'PUT') {
					$data = RequestRouter::getRequestData();
					$zone = new Zone($data['zonename'], $this->zoneModule);
					$this->zoneModule->zoneCreate($zone);
					$result = new stdClass();
					$result->success = true;
					return $result;
				}
				// list zones
				$result = new stdClass();
				$result->zones = array();
				foreach ($this->zoneModule->listZones() as $zone) {
					$tmpzone = new stdClass();
					$tmpzone->id = $zone->getName();
					$tmpzone->name = $zone->getName();
					$result->zones[$zone->getName()] = $tmpzone;
				}
				return $result;
			} else {
				$zone = new Zone($zonename, $this->zoneModule);
				if ($this->getRequestType() == 'DELETE') {
					$this->zoneModule->zoneDelete($zone);
					$result = new stdClass();
					$result->success = true;
					return $result;
				} else {
					$result = new stdClass();
					$result->success = false;
					return $result;
				}
			}
		} else {
			$zone = new Zone($zonename, $this->zoneModule);
			$zoneRouter = new ZoneRouter($zone);
			return $zoneRouter->track($this->routingPath);
		}
	}

}

?>