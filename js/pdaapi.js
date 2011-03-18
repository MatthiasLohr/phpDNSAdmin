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

	this.logout = function(successCallback, errorCallback) {
		data = {username: ''};
		Ext.Ajax.request({
			url: URL+'/status',
			method: 'POST',
			params: data,
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				if (data.success == false) {
					 errorCallback(data.error, data.message);
				} else {
					successCallback(data.loggedIn);
				}
			}
		});
	}
	this.checkLoginStatus = function(successCallback, errorCallback, options) {
		Ext.Ajax.request({
			url: URL+'/status',
			method: 'GET',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				if(data.success == false) {
					errorCallback(data.error, data.message, true);
				} else {
					successCallback(data.loggedIn, data.username);
				}
			}
		});
	}

	this.getURL = function() {
		return URL;
	}

	this.listRecords = function(server,zone,callback) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones/'+zone+'/records',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					callback(server,zone,data.records);
				}
			}
		});
	}

	this.listServers = function(callback) {
		Ext.Ajax.request({
			url: URL+'/servers',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					callback(data.servers);
				}
			}
		});
	}

	this.listZones = function(server,callback) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones',
			success: function(response,options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					callback(server,data.zones);
				}
			}
		});
	}

	this.deleteZone = function(server, zone, success, failure) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones/'+zone,
			method: 'DELETE',
			success: function(response, options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					success(server);
				} else {
					failure(data.error);
				}
			}
		});
	}

	this.createZone = function(server, zonename, success, failure) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones/',
			method: 'PUT',
			params: 'zonename='+zonename,
			success: function(response, options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					success(server);
				} else {
					failure(data.error);
				}
			}
		});
	}

	this.deleteRecord = function(server, zone, recordid, success, failure) {
		Ext.Ajax.request({
			url: URL+'/servers/'+server+'/zones/'+zone+'/records/'+recordid,
			method: 'DELETE',
			success: function(response, options) {
				var data = Ext.decode(response.responseText);
				if(data.success) {
					success(recordid);
				} else {
					failure(data.error);
				}
			}
		});
	}

	// =============== constructor ===========
	var URL = url;
}
