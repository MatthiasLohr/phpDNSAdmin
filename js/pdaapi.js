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

function pdaAPI(url) {

	this.checkLoginStatus = function(callback,options) {
		var method = 'GET';
		var data = {};
		if (options != undefined) {
			method = 'POST';
			data.username = options.username;
			data.password = options.password;
		}
		Ext.Ajax.request({
			url: URL+'/status',
			method: method,
			params: data,
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				callback(data.loggedIn);
			}
		});
	}

	this.getURL = function() {
		return URL;
	}

	this.listRecords = function(server,zone,callback) {

	}

	this.listServers = function(callback) {
		Ext.Ajax.request({
			url: URL+'/servers',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				callback(data);
			}
		});
	}

	this.listZones = function(server,callback) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				callback(server,data);
			}
		});
	}

	// =============== constructor ===========
	var URL = url;
}
