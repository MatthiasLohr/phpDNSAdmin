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


$config = array(

	// authentication modules
	'authentication' => array(
		/*
		array(
			'_module' => 'HtpasswdAuthentication',
			'filename' => '/var/www/phpdnsadmin/.htpasswd'
		),
		// */
	),

	// autologin modules
	'autologin' => array(
		array(
			'_module' => 'SessionAutologin',

		),
	),

	// authorization modules
	'authorization' => array(

	),

	// zone modules
	'zone' => array(
		/*
		array(
			'_module' => 'PdnsPdoZone',
		 * '_sysname' => 'main',
			'pdo_dsn' => 'pgsql:host=localhost;dbname=pdns',
			'pdo_username' => 'powerdns',
			'pdo_password' => 'powerdns'
		),
		// */
	)
);

?>