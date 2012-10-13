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
 *	Function for load files containing classes
 *
 * @param string $className
 */
function pdaAutoload($className) {
	$className = strtolower($className);
	if (substr($className,-9) == 'exception') {
		includeIfExists(API_ROOT.'/lib/'.substr($className,0,-9).'.exception.php');
	}
	else {
		if (substr($className,-4) == 'zone') includeIfExists(API_ROOT.'/lib/modules/zone/'.$className.'.class.php');
		includeIfExists(API_ROOT.'/lib/routers/'.$className.'.class.php');
		includeIfExists(API_ROOT.'/lib/rrtypes/'.$className.'.class.php');
		includeIfExists(API_ROOT.'/lib/simpletypes/'.$className.'.class.php');
		includeIfExists(API_ROOT.'/lib/'.$className.'.class.php');
	}
}

/**
 * include file if exists
 *
 * @param string $filename
 * @return boolean true if found, false otherwise
 */
function includeIfExists($filename) {
		if (file_exists($filename)) {
			return include($filename);
		}
		else {
			return false;
		}
	}

// register class autoloader
spl_autoload_register('pdaAutoload',true);

?>