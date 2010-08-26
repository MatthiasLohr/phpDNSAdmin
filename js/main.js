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

function checkLogin() {

}

function initLogin() {
	var loginForm = new Ext.FormPanel({
		frame: true,
		defaultType:'textfield',
		monitorValid:true,
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
				
			}
		}, {
			text: 'Reset',
			formBind: true,
			handler: function() {
				
			}
		}]
	});
	// wrap window
	var win = new Ext.Window({
		title: 'Please log in',
		layout: 'fit',
		closable: false,
		resizable: false,
		plain: true,
		border: false,
		width: 300,
		height: 120,
		items: [loginForm]
	});
	win.show();
	return win;
}

function initMain() {
	var viewport = new Ext.Viewport({
		layout: 'border',
		listeners: {
			disable: function(me) {
				me.items.each(function(item) {
					item.disable();
				});
			},
			enable: function(me) {
				me.items.each(function(item) {
					item.enable();
				});
			}
		},
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

	viewport.disable();
	return viewport;
}