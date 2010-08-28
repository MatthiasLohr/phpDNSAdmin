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

	function updateLoginStatus(loggedIn) {
		if (loggedIn) {
			loginWindow.hide();
			mainContainer.enable();

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
	var mainContainer = new Ext.Panel({
		layout: 'border',
		items: [{
			region: 'west',
			collapsible: true,
			title: 'Zones',
			xtype: 'treepanel',
			width: 200,
			autoScroll: true,
			split: true,
			loader: new Ext.tree.TreeLoader(),
			root: new Ext.tree.AsyncTreeNode({
				expanded: true,
				children: [{
					text: 'Menu Option 1',
					leaf: true
				}, {
					text: 'Menu Option 2',
					leaf: true
				}, {
					text: 'Menu Option 3',
					leaf: true
				}]
			}),
			rootVisible: false,
			listeners: {
				click: function(n) {
					Ext.Msg.alert('Navigation Tree Click', 'You clicked: "' + n.attributes.text + '"');
				}
			}


		}, {
			region: 'center',
			xtype: 'tabpanel',
			title: 'Records',
			items: []
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
