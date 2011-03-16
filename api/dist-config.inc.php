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

$zoneModCount = 0;
$config = array(

	// authentication modules
	'authentication' => array(
		/* -- delete this line for enabling HtpasswdAuthentication --
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
		/* -- delete this line for enabling BindDlzPdoZone --
		array(
			'_module' => 'BindDlzPdoZone',
			'_sysname' => 'server'.(++$zoneModCount),
			'_name' => 'My DNS server',
			'pdo_dsn' => 'pgsql:host=localhost;dbname=bind',
			'pdo_username' => 'bind',
			'pdo_password' => 'bind',
		  // PgSQL only:
		  // 'search_path' => 'public',
		),
		// */

		/* -- delete this line for enabling MultiServerViewZone --
		array(
			'_module' => 'MultiServerViewZone',
			'_sysname' => 'server'.(++$zoneModCount),
			'_name' => 'My DNS server',
			'pdo_dsn' => 'pgsql:host=localhost;dbname=phpdnsadmin', // access to cache table
			'pdo_username' => 'phpdnsadmin',
			'pdo_password' => 'phpdnsadmin',
		  'tableprefix' => 'mv_',
			// PgSQL only:
		  // 'records_sequence' => 'records_id_seq',
		  // 'search_path' => 'public',
			'views' => array(
				// paste here zone module configurations

			)
		),
		// */

		/* -- delete this line for enabling JsonZone --
		array(
			'_module' => 'JsonZone',
			'_sysname' => 'server'.(++$zoneModCount),
			'_name' => 'My DNS server',
			'api_base' => 'http://localhost/phpdnsadmin/api/',
			'server_sysname' => 'server0'
		),
		// */

		/* -- delete this line for enabling MydnsPdoZone --
		array(
			'_module' => 'MydnsPdoZone',
			'_sysname' => 'server'.(++$zoneModCount),
			'_name' => 'My DNS server',
			'pdo_dsn' => 'pgsql:host=localhost;dbname=mydns',
			'pdo_username' => 'mydns',
			'pdo_password' => 'mydns',
			// PgSQL only:
		  // 'search_path' => 'public',
		),
		// */

		/* -- delete this line for enabling PdnsPdoZone --
		array(
			'_module' => 'PdnsPdoZone',
			'_sysname' => 'server'.(++$zoneModCount),
			'_name' => 'My DNS server',
			'pdo_dsn' => 'pgsql:host=localhost;dbname=pdns',
			'pdo_username' => 'powerdns',
			'pdo_password' => 'powerdns',
			// 'tableprefix' => '',

			/// PGSQL only:
			// 'domains_sequence' => 'domains_id_seq',
			// 'records_sequence' => 'records_id_seq',
			// 'search_path' => 'public'
			
		),
		// */
	),

	// internal phpDNSAdmin configuration
	'internal' => array(
		'debug' => false, // shows strack traces on exepctions. DANGER! maybe shows passwords!!!
	)
);

?>