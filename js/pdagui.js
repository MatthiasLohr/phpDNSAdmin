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

	function displayRecords(serverkey,zone,records) {
		var tab = zonetabs.findById('zonetab-'+serverkey+'-'+zone);
		var store = tab.getStore();
		//store.loadData(records);
		for (recordid in records) {
			record = records[recordid];
			store.add(new store.recordType({
				id: record.id,
				name: record.name,
				type: record.type,
				content: record.content,
				ttl: record.ttl
			}
			));
		}
		tab.enable();
	}

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
				serverkey: serverkey,
				zone: zone.name,
				leaf: true,
				text: zone.name,
				listeners: {
					click: function(node, event) {
						// search for zone tab. if exists, show, else create
						var tab = zonetabs.findById('zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone);
						if (tab == null) {
							tab = new Ext.grid.GridPanel({
								serverkey: node.attributes.serverkey,
								zone: node.attributes.zone,
								title: node.attributes.zone,
								id: 'zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone,
								closable: true,
								selMode: new Ext.grid.CheckboxSelectionModel(),
								colModel: new Ext.grid.ColumnModel({
									defaults: {
										sortable: true,
										menuDisabled: false
									},
									columns: [
									{
										header: 'name',
										dataIndex: 'name'
									},

									{
										header: 'type',
										dataIndex: 'type'
									},

									{
										header: 'content',
										id: 'content',
										dataIndex: 'content'
									},

									{
										header: 'TTL',
										dataIndex: 'ttl'
									}
									]
								}),
								store: new Ext.data.JsonStore({
									fields: ['id', 'name', 'type', 'content', 'ttl']
								}),
								autoExpandColumn: 'content'
							});
							zonetabs.add(tab);
							// load record data
							tab.disable();
							API.listRecords(node.attributes.serverkey,node.attributes.zone,displayRecords);
						}
						zonetabs.setActiveTab('zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone);
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
		} else {
			mainContainer.disable();
			loginWindow.show();
		}
	}

	function notifyMsg(msg, title) {
		if(title==null) {
			Ext.ux.Growl.notify({
				message: msg,
				alignment: "tr-tr",
				offset: [-10, 10]
			});
		} else {
			Ext.ux.Growl.notify({
				title: title,
				message: msg,
				alignment: "tr-tr",
				offset: [-10, 10]
			});
		}
	}

	// ============ constructor ==============

	var API = api;
	var URL = api.getURL();

	// init main container
	var zonetree = new Ext.tree.TreePanel({
		itemId: 'zonetree',
		region: 'west',
		collapsible: true,
		title: 'Zones',
		width: 300,
		autoScroll: true,
		split: true,
		loader: new Ext.tree.TreeLoader(),
		root: new Ext.tree.TreeNode(),
		rootVisible: false,
		buttons: [{
			text: 'Create',
			handler: function() {
				var node = zonetree.getSelectionModel().getSelectedNode();
				if(node != null) {
					Ext.MessageBox.prompt('Create New Zone', 'Enter new Zone Name:', function(btn, text) {
						if(btn == 'ok') {
							API.createZone(node.attributes.serverkey, text,
								function(server) {
									notifyMsg('Zone '+text+' was created!');
									API.listZones(server, displayZones);
								},
								function(error) {
									// notify fail
									notifyMsg(error);
								});
						}
					});
				} else {
					notifyMsg('Please select server first!');
				}
			}
		}, {
			text: 'Delete Selected',
			handler: function() {
				var node = zonetree.getSelectionModel().getSelectedNode();
				if(node.attributes.zone) {
					var servername = zonetree.root.findChild('id','server-'+node.attributes.serverkey,1).text;
					Ext.MessageBox.confirm('Are you sure?', "Do you really want to delete "+node.attributes.zone+" on "+servername+"?", function(choice) {
						if(choice=='yes') {
							API.deleteZone(node.attributes.serverkey, node.attributes.zone,
								function(server) {
									// close Tab, if open
									var tab = zonetabs.findById('zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone);
									if(tab != null) {
										zonetabs.remove(tab, true);
									}
									// refresh Tree
									API.listZones(server, displayZones);

									// notify success
									notifyMsg("Zone "+node.attributes.zone+" was successfully deleted.");
								}, function(error) {
									// notify fail
									notifyMsg(error);
								});
						}
					});
				}
			}
		}]
	});
	var zonetabs = new Ext.TabPanel({
		region: 'center',
		title: 'Records',
		items: [],
		enableTabScroll: true,
		buttons: [{
			text: 'Add Record'
		}, {
			text: 'Delete Selected Records',
			handler: function() {
				var tab = zonetabs.getActiveTab();
				if(tab != null) {
					var selection = tab.getSelectionModel();
					if(selection.getCount() > 0) {
						if(selection.getCount() == 1) {
							msg = 'a Record';
						} else {
							msg = selection.getCount() + ' Records';
						}

						Ext.MessageBox.confirm('Are you sure?', "Do you really want to delete "+msg+"?", 
							function(choice) {
								if(choice== 'yes') {
									selection.each(function(record) {
										API.deleteRecord(tab.serverkey, tab.zone, record.data.id, function() {
											var store = tab.getStore();
											store.remove(record);
											notifyMsg('Record deleted.');
										}, function(error) {
											notifyMsg(error, 'Error!');
										});
									});
								}
							});
					}
				}
			}
		}]
	});
	var mainContainer = new Ext.Panel({
		layout: 'border',
		items: [zonetree, zonetabs]
	});
	mainContainer.disable();

	var loginForm = new Ext.FormPanel({
		frame: true,
		defaultType:'textfield',
		monitorValid:true,
		url: URL+'/status',
		items: [{
			fieldLabel: 'Username',
			name: 'username',
			id: 'loginUsername',
			inputType: 'textfield',
			allowBlank:false
		}, {
			fieldLabel: 'Password',
			name: 'password',
			id: 'loginPassword',
			inputType: 'password',
			allowBlank:false
		}],
		buttons: [{
			text: 'Login',
			formBind: true,
			handler:function(){
				loginForm.getForm().submit({
					method:'POST',
					waitTitle:'Connecting',
					waitMsg:'Sending data...',
					success: function(form, action) {
						updateLoginStatus(action.result.loggedIn);
						notifyMsg('Logged in as '+action.result.username);
					},
					failure: function(form, action) {
						switch (action.failureType) {
							case Ext.form.Action.CLIENT_INVALID:
								notifyMsg('Form fields may not be submitted with invalid values');
								break;
							case Ext.form.Action.CONNECT_FAILURE:
								notifyMsg('Ajax communication failed');
								break;
							case Ext.form.Action.SERVER_INVALID:
								notifyMsg('Login failed!');
						}
						loginForm.getForm().reset();
					}
				});
			}
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
