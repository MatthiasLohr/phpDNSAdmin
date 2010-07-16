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
abstract class RequestRouter {

	/** @var array remaining routing path */
	protected $routingPath = array();

	public function __default() {
		return null;
	}

	protected function endOfTracking() {
		return (count($this->routingPath) == 0);
	}

	public static function getRequestData() {
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'POST':
			case 'PUT':
			case 'DELETE':
				$str = file_get_contents('php://input');
				parse_str($str,$data);
				return $data;
			default:
				return null;
		}
	}

	public static function getRequestType() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Map URL parameters to the respective router classes
	 *
	 * @param string[] $path calls to be performed
	 * @return something
	 * @throws RequestRoutingException if a method cannot be called
	 */
	public final function track(array $path) {
		if (count($path) == 0) return $this->__default();
		$className = get_class($this);
		$routerReflector = new ReflectionClass($className);
		if ($routerReflector->hasMethod($path[0])) {
			$method = $routerReflector->getMethod($path[0]);
			if (!$method->isAbstract() && !$method->isConstructor() && !$method->isDestructor() && $method->isPublic() && !$method->isStatic()) {
				$paramCount = $method->getNumberOfParameters();
				if ($paramCount > 0) {
					$params = array_slice($path,1,$paramCount);
					if (count($params) < $method->getNumberOfRequiredParameters()) throw new RequestRoutingException(
						'Not enough parameters for '.$className.'->'.$method->getName().'()!'
					);
				}
				else {
					$params = array();
				}
				$this->routingPath = array_slice($path,1+$paramCount);
				return $method->invokeArgs($this,$params);
			}
			else {
				throw new RequestRoutingException('method '.$path[0].' is not eligible for routing');
			}
		}
		else {
			throw new RequestRoutingException('class '.$className.' has no method "'.$path[0].'"');
		}
	}

}

?>