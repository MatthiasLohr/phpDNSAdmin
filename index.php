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
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="js/extjs/resources/css/ext-all.css" />
		<link rel="stylesheet" type="text/css" href="js/extjs/growl/css/ext/ux/Growl.css" />
		<script type="text/javascript" src="js/extjs/adapter/ext/ext-base.js"></script>
		<script type="text/javascript" src="js/extjs/ext-all-debug.js"></script>
		<script type="text/javascript" src="js/extjs/growl/Growl.js"></script>
		<script type="text/javascript" src="js/extjs/extjs.addons.js"></script>
		<script type="text/javascript" src="js/pdaapi.js"></script>
		<script type="text/javascript" src="js/pdagui.js"></script>
		<title id="page-title">phpDNSAdmin</title>
		<script type="text/javascript">
			Ext.onReady(function() {
				Ext.QuickTips.init();
				var GUI = new pdaGUI(new pdaAPI('api'));
				
			});
		</script>

	</head>
	<body>

	</body>
</html>