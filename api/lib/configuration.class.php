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
 * Configuration loader
 *
 * @package phpDNSAdmin
 * @subpackage Core
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class Configuration {

	/**
	 * @var Configuration instance of last loaded configuration
	 */
	private static $instance = null;

	/**
	 * @var array configuration values
	 */
	private $config = array();

	/**
	 * Constructor
	 *
	 * @param array configuration values
	 */
	protected function __construct($configuration) {
		$this->config = $configuration;
	}

	/**
	 * Are we running in debug mode?
	 *
	 * @return boolean true/false
	 */
	public function debugMode() {
		if (isset($this->config['internal']) && isset($this->config['internal']['debug'])) return $this->config['internal']['debug'];
		return false;
	}

	/**
	 * Return authentication module configuration
	 *
	 * @return array configuration values
	 */
	public function getAuthenticationConfig() {
		return $this->config['authentication'];
	}

	/**
	 * Return authorization module configuration
	 *
	 * @return array configuration values
	 */
	public function getAuthorizationConfig() {
		return $this->config['authorization'];
	}

	/**
	 * Return autologin module configuration
	 *
	 * @return array configuration values
	 */
	public function getAutologinConfig() {
		return $this->config['autologin'];
	}

	/**
	 * Return the last loaded configuration instance
	 *
	 * @return Configuration configuration class
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Return zone module configuration
	 *
	 * @return array configuration values
	 */
	public function getZoneConfig() {
		return $this->config['zone'];
	}

	/**
	 * Load configuration from file and create Configuration object instance
	 *
	 * @return Configuration configuration instance
	 */
	public static function load($filename) {
		require($filename);
		self::$instance = new Configuration($config);
		return self::$instance;
	}

}

?>