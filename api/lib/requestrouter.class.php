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

	/**
	 * Default function for calls without a method name
	 *
	 * @return null
	 */
	public function __default() {
		return null;
	}

	/**
	 * Check if this request is the and of URL tracking
	 *
	 * @return boolean true: end of tracking
	 */
	protected function endOfTracking() {
		return (count($this->routingPath) == 0);
	}

	/**
	 * Change the request type for further tracking
	 *
	 * @param string $type GET, POST, PUT or DELETE
	 * @return boolean success true/false
	 */
	public static function forceRequestType($type) {
		if (in_array($type, array('GET', 'POST', 'PUT', 'DELETE'))) {
			$_SERVER['REQUEST_METHOD'] = $type;
			return true;
		}
		return false;
	}

	/**
	 * Return the request data as php data structure
	 *
	 * @return mixed
	 */
	public static function getRequestData() {
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'POST':
			case 'PUT':
			case 'DELETE':
				$str = file_get_contents('php://input');
				$data = null;
				if (preg_match('/json$/i', $_SERVER['CONTENT_TYPE'])) {
					/* Request was sent as JSON Object
					 * Decode JSON Object to associative array
					 */
					$data = json_decode($str, true);
				}
				else {
					// Fallback with old behavior
					parse_str($str, $data);
				}
				return $data;
			default:
				return null;
		}
	}

	/**
	 *
	 * @return string
	 * @see forceRequestType
	 */
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
					$params = array_slice($path, 1, $paramCount);
					if (count($params) < $method->getNumberOfRequiredParameters()) throw new RequestRoutingException(
						'Not enough parameters for ' . $className . '->' . $method->getName() . '()!'
					);
				}
				else {
					$params = array();
				}
				$this->routingPath = array_slice($path, 1 + $paramCount);
				return $method->invokeArgs($this, $params);
			}
			else {
				throw new RequestRoutingException('method ' . $path[0] . ' is not eligible for routing');
			}
		}
		else {
			throw new RequestRoutingException('class ' . $className . ' has no method "' . $path[0] . '"');
		}
	}

	/**
	 *
	 * @param string $path
	 */
	public final function trackByURL($path) {
		// shortcut for empty paths
		if ($path == '') return $this->track(array());
		// trim trailing slashes
		if (substr($path, -1) == '/') {
			$path = substr($path, 0, -1);
		}
		// run router
		$explodedPath = explode('/', $path);
		return $this->track($explodedPath);
	}

}

?>