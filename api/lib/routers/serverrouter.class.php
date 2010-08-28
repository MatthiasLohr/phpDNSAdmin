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
		return array_values(array_intersect(ResourceRecord::listTypes(), $features['rrtypes']));
	}

	public function zones($zonename = null) {
		if ($zonename === null) {
			if (RequestRouter::getRequestType() == 'PUT') {
				// create new Zone
				$result = new stdClass();
				$data = RequestRouter::getRequestData();
				if (isset($data['zonename'])) {
					try {
						$this->zoneModule->zoneCreate(new Zone($data['zonename'], $this->zoneModule));
						$result->success = true;
					} catch (Exception $e) {
						$result->success = false;
						$result->error = $e->getMessage();
					}
				} else {
					$result->success = false;
					$result->error = 'No zonename given!';
				}
				return $result;
			}

			$result = new stdClass();
			$zones = $this->zoneModule->listZones();
			foreach ($zones as $zone) {
				$tmp = new stdClass();
				$name = $zone->getName();
				$tmp->name = $name;
				$result->$name = $tmp;
			}
			return $result;
		} else {
			if (RequestRouter::getRequestType() == 'DELETE' && $this->endOfTracking()) {
				// Delete given Zone
				$result = new stdClass();
				try {
					$this->zoneModule->zoneDelete(new Zone($zonename, $this->zoneModule));
					$result->success = true;
				} catch (Exception $e) {
					$result->success = false;
					$result->error = $e->getMessage();
				}
				return $result;
			} else {
				try {
					$zone = new Zone($zonename, $this->zoneModule);
					$zoneRouter = new ZoneRouter($zone);
					return $zoneRouter->track($this->routingPath);
				} catch (NoSuchZoneException $e) {
					$result = new stdClass();
					$result->error = 'Zone not found!';
					return $result;
				} catch (InvalidFieldDataException $e) {
					$result = new stdClass();
					$result->error = $e->getMessage();
					return $result;
				}
			}
		}
	}

}
?>