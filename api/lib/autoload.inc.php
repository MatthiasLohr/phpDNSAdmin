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
 *	Function for load files containing classes
 *
 * @param string $className
 */
function pdaAutoload($className) {
	$className = strtolower($className);
	if (substr($className,-9) == 'exception') {
		@include(API_ROOT.'/lib/'.substr($className,0,-9).'.exception.php');
	}
	else {
		@include(API_ROOT.'/lib/modules/'.$className.'.class.php');
		@include(API_ROOT.'/lib/routers/'.$className.'.class.php');
		@include(API_ROOT.'/lib/rrtypes/'.$className.'.class.php');
		@include(API_ROOT.'/lib/simpletypes/'.$className.'.class.php');
		@include(API_ROOT.'/lib/'.$className.'.class.php');
	}
}

spl_autoload_register('pdaAutoload',true,true);

?>