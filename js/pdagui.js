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

function pdaGUI(api) {

	function displayServers(servers) {
		var rootNode = zonetree.root;
		rootNode.removeAll(true);
		for (serverkey in servers) {
			rootNode.appendChild(new Ext.tree.TreeNode({
				allowChildren: true,
				allowDrag: false,
				allowDrop: false,
				editable: false,
				expandable: true,
				serverkey: serverkey,
				id: 'server-'+serverkey,
				leaf:false,
				text: servers[serverkey].name,
				listeners: {
					beforeexpand: function(node, deep, anim) {
						if (!node.hasChildNodes()) {
							API.listZones(node.attributes.serverkey,displayZones);
							return false;
						}
						return true;
					},
					click: function(node, event) {
						if (node.isExpanded()) {
							node.collapse(1);
						}
						else {
							node.expand(1);
						}
					}
				}
			}));
		}
	}

	function displayZones(serverkey,zones) {
		var serverNode = zonetree.root.findChild('id','server-'+serverkey,1);
		serverNode.removeAll(true);
		for (id in zones) {
			zone = zones[id];
			serverNode.appendChild({
				allowChildren: false,
				allowDrag: false,
				allowDrop: false,
				editable: false,
				expandable: false,
				leaf: true,
				text: zone.name,
				listeners: {
					click: function(node, event) {
						// search for zone tab. if exists, show, else create
						
					}
				}
			});
		}
		serverNode.expand();
	}

	function updateLoginStatus(loggedIn) {
		if (loggedIn) {
			loginWindow.hide();
			mainContainer.enable();
			API.listServers(displayServers);

		}
		else {
			mainContainer.disable();
			loginWindow.show();
		}
	}

	// ============ constructor ==============

	var API = api;
	var URL = api.getURL();

	// init main container
	var zonetree = new Ext.tree.TreePanel({
		region: 'west',
		collapsible: true,
		title: 'Zones',
		xtype: 'treepanel',
		width: 300,
		autoScroll: true,
		split: true,
		loader: new Ext.tree.TreeLoader(),
		root: new Ext.tree.TreeNode(),
		rootVisible: false,
		buttons: [{
			text: 'Create'
		}, {
			text: 'Delete Selected'
		}]
	});
	var mainContainer = new Ext.Panel({
		layout: 'border',
		items: [zonetree, {
			region: 'center',
			xtype: 'tabpanel',
			title: 'Records',
			items: [],
			buttons: [{
				text: 'Add Record'
			}, {
				text: 'Delete Selected Records'
			}]
		}]
	});
	mainContainer.disable();

	var loginForm = new Ext.FormPanel({
		frame: true,
		defaultType:'textfield',
		monitorValid:true,
		url: URL+'/status',
		method: 'POST',
		listeners: {
			actioncomplete: function(form,action) {
				return true;
			}
		},
		items: [{
			fieldLabel: 'username',
			name: 'loginUsername',
			id: 'loginUsername',
			inputType: 'textfield'

		}, {
			fieldLabel: 'password',
			name: 'loginPassword',
			id: 'loginPassword',
			inputType: 'password'
		}],
		buttons: [{
			text: 'Login',
			formBind: true,
			handler: function () {
				var username = Ext.getDom('loginUsername').value;
				var password = Ext.getDom('loginPassword').value;
				API.checkLoginStatus(updateLoginStatus,{
					username: username,
					password: password
				});
			}
		/*}, {
			text: 'Reset',
			formBind: true,
			handler: function() {

			}*/
		}]
	});
	// wrap window
	var loginWindow = new Ext.Window({
		title: 'phpDNSAdmin Login',
		layout: 'fit',
		closable: false,
		resizable: false,
		plain: true,
		border: false,
		width: 300,
		height: 120,
		items: [loginForm]
	});

	// init viewport
	var viewport = new Ext.Viewport({
		layout: 'fit',
		items: [mainContainer]
	});
	
	// check login status
	API.checkLoginStatus(updateLoginStatus);


}
