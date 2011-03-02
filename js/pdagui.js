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

	// Logo
	var phpDNSAdminLogo = new Ext.ux.Image({
		id: 'phpDNSAdminLogo',
		url: 'css/logo.png'
	});

	// Context Menus
	var contextMenuServer = new Ext.menu.Menu({
		items: [{
			id: 'create-zone',
			text: 'create zone'
		}, {
			id: 'refresh-zones',
			text: 'refresh zones'
		}],
		listeners: {
			itemclick: function(item) {
				switch(item.id) {
					case 'create-zone':
						createZone();
						break;
					case 'refresh-zones':
						refreshZones();
						break;
				}
			}
		}
	});

	var contextMenuZone = new Ext.menu.Menu({
		items: [{
			id: 'delete-zone',
			text: 'delete zone'
		}],
		listeners: {
			itemclick: function(item) {
				if(item.id == 'delete-zone')
					deleteZone();
			}
		}
	});

	// Notify-System
	var App = new Ext.App({});

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
					},
					contextmenu: function(node, e) {
						node.select();
						contextMenuServer.show(node.ui.getAnchor());
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
			
			// added for view support
			if(zone.views == undefined) {
				zone.views = false;
			}

			serverNode.appendChild({
				allowChildren: false,
				allowDrag: false,
				allowDrop: false,
				editable: false,
				expandable: false,
				serverkey: serverkey,
				zone: zone.name,
				views: zone.views,
				leaf: true,
				text: zone.name,
				listeners: {
					click: function(node, event) {
						// search for zone tab. if exists, show, else create
						var tab = zonetabs.findById('zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone);
						if (tab == null) {
							var RestURL = API.getURL() + '/servers/' + node.attributes.serverkey + '/zones/' + node.attributes.zone + '/records';
							var newStore = new Ext.data.Store({
								restful: true,
								paramNames: { start: 'offset', limit: 'limit', sort: 'sortby', dir: 'sortorder' },
								sortInfo: { field: 'name', direction: 'ASC' },
								remoteSort: true,
								reader: new Ext.data.JsonReader({
									fields: [{
										name: 'id',
										type: 'int'
									}, 'name', 'type', 'content', {
										name: 'ttl',
										type: 'int'
									}, { name: 'content', mapping: 'fields' }, {
										name: 'views',
										defaultValue:false
								  }],
									root: 'records',
									successProperty: 'success',
									totalProperty: 'totalCount'
								}),
								writer: new Ext.data.DnsWriter(),
								proxy: new Ext.data.HttpProxy({
									api: {
										read: {
											url: RestURL,
											method: "GET"
										},
										create: {
											url: RestURL,
											method: "PUT"
										},
										update: {
											url: RestURL,
											method: "POST"
										},
										destroy: {
											url: RestURL,
											method: "DELETE"
										}
									},
									listeners: {
										write: function(proxy, action, data, response, rs, options) {
											switch(action) {
												case 'update':
													if(response.success == true) {
														App.setAlert("notice", 'Record was changed successful!');
													} else {
														App.setAlert("notice", 'Changing record failed!');
													}
													break;
												case 'destroy':
													if(response.success == true) {
														App.setAlert("notice", 'Record was deleted successful!');
													} else {
														App.setAlert("notice", 'Deleting record failed!');
													}
													break;
												case 'create':
													if(response.success == true) {
														App.setAlert("notice", 'Record was created successful!');
													} else {
														App.setAlert("notice", 'Creating record failed! ' + response.error);
													}
													break;
											}
										}
									}
								})
							});
							var newColModel = null;
							// Code reuse is here not possible... I promise
							if(!node.attributes.views) {
								newColModel = new Ext.grid.ColumnModel({
									defaults: {
										sortable: true,
										menuDisabled: false
									},
									columns: [{
										header: 'name',
										dataIndex: 'name',
										editor: {
											xtype : 'textfield',
											allowBlank: false,
											validator: function(value) {
												return validValue(value, 'Hostname');
											}
										}
									}, {
										header: 'type',
										dataIndex: 'type'
									}, {
										header: 'content',
										id: 'content',
										dataIndex: 'content',
										xtype: 'contentcolumn',
										editor: new Ext.DNSContent()
									}, {
										header: 'TTL',
										dataIndex: 'ttl',
										editor: {
											xtype : 'textfield',
											allowBlank: false,
											validator: function(value) {
												return validValue(value, 'UInt');
											}
										}
									}]
								});
							} else {
								newColModel = new Ext.grid.ColumnModel({
									defaults: {
										sortable: true,
										menuDisabled: false
									},
									columns: [{
										header: 'name',
										dataIndex: 'name',
										editor: {
											xtype : 'textfield',
											allowBlank: false,
											validator: function(value) {
												return validValue(value, 'Hostname');
											}
										}
									}, {
										header: 'type',
										dataIndex: 'type'
									}, {
										header: 'content',
										id: 'content',
										dataIndex: 'content',
										xtype: 'contentcolumn',
										editor: new Ext.DNSContent()
									}, {
										header: 'TTL',
										dataIndex: 'ttl',
										editor: {
											xtype : 'textfield',
											allowBlank: false,
											validator: function(value) {
												return validValue(value, 'UInt');
											}
										}
									}, {
										header: 'Views',
										dataIndex: 'views',
										xtype: 'multicheckcolumn',
										editor: new Ext.ViewEditor()
								}]
								});
							}

							// use RowEditor for editing
							var editor = new Ext.ux.grid.RowEditor({
								errorSummary:false
							});

							editor.on("afteredit",function(roweditor,changes,record,index){
//								var del = true;
//								for(view in changes.views) {
//									del = !changes.views[view];
//								}
//								if(del) {
//									// remove from grid
//									roweditor.grid.getStore().load();
//								}
							});
							
							tab = new Ext.grid.GridPanel({
								serverkey: node.attributes.serverkey,
								zone: node.attributes.zone,
								title: node.attributes.zone,
								id: 'zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone,
								closable: true,
								selMode: new Ext.grid.CheckboxSelectionModel(),
								colModel: newColModel,
								store: newStore,
								autoExpandColumn: 'content',
								plugins: [editor],
								tbar: [{
									text: 'Add Record',
									handler: function(btn, ev) {
										addRecordWindow(node.attributes.serverkey, node.attributes.zone, tab.store);
									}
								}, '-', {
									text: 'Delete selected Records',
									handler: function() {
										var selection = tab.getSelectionModel();
										var store = tab.getStore();
										selection.each(function(record) {
											if(record != null) {
												store.remove(record);
											}
										});
									}
								}, '-'],
								bbar: new Ext.PagingToolbar({
									pageSize: 30,
									displayInfo: true,
									emptyMsg: 'No data found',
									store: newStore,
									plugins: [new Ext.ux.PageSizePlugin()]
								})
							});
							zonetabs.add(tab);
							newStore.on('beforeload', function() {
								tab.disable();
							});

							newStore.on('load', function() {
								tab.enable();
							});

							newStore.load({params:{start:0,limit:30}});
						}
						zonetabs.setActiveTab('zonetab-'+node.attributes.serverkey+'-'+node.attributes.zone);
					},
					contextmenu: function(node, e) {
						node.select();
						contextMenuZone.show(node.ui.getAnchor());
					}
				}
			});
		}
		serverNode.expand();
	}

	function updateLoginStatus(loggedIn, name) {
		if (loggedIn) {
			Ext.getCmp('status-label').setText('logged in as ' + name);
			Ext.getCmp('logout-button').setDisabled(false);
			loginWindow.hide();
			mainContainer.enable();
			API.listServers(displayServers);
		} else {
			Ext.getCmp('status-label').setText('not logged in');
			Ext.getCmp('logout-button').setDisabled(true);
			mainContainer.disable();
			loginWindow.show();
		}
	}

	function createZone() {
		var node = zonetree.getSelectionModel().getSelectedNode();
		if(node != null) {
			Ext.MessageBox.prompt('Create New Zone on ' + node.attributes.text, 'Enter new Zone Name:', function(btn, text) {
				if(btn == 'ok') {
					API.createZone(node.attributes.serverkey, text,
						function(server) {
							App.setAlert("ok", 'Zone '+text+' was created!');
							API.listZones(server, displayZones);
						},
						function(error) {
							// notify fail
							App.setAlert("error", error);
						});
				}
			});
		} else {
			App.setAlert("notice", 'Please select server first!');
		}
	}

	function deleteZone() {
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
							App.setAlert("ok", "Zone "+node.attributes.zone+" was successfully deleted.");
						}, function(error) {
							// notify fail
							App.setAlert("error", error);
						});
				}
			});
		}
	}

	function refreshZones() {
		var node = zonetree.getSelectionModel().getSelectedNode();
		API.listZones(node.attributes.serverkey, displayZones);
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
		rootVisible: false
	});
	var zonetabs = new Ext.TabPanel({
		region: 'center',
		title: 'Records',
		items: [],
		enableTabScroll: true
	});

	// Info Panel
	var infoPanel = new Ext.Toolbar({
		region: 'north',
		height: 30,
		items: [
		phpDNSAdminLogo,
		'->',
		{
			xtype: 'label',
			id: 'status-label',
			text: 'not logged in'
		},
		'-',
		{
			text: 'Logout',
			id: 'logout-button',
			disabled: true,
			handler: function() {
				API.logout(updateLoginStatus);
			}
		}
		]
	});

	var mainContainer = new Ext.Panel({
		layout: 'border',
		items: [infoPanel,zonetree, zonetabs]
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
		keys: [{
			key: [Ext.EventObject.ENTER],
			handler: function() {
				Ext.getCmp('login-button').handler.call(Ext.getCmp('login-button').scope);
			}
		}],
		buttons: [{
			id: 'login-button',
			text: 'Login',
			formBind: true,
			handler:function(){
				loginForm.getForm().submit({
					method:'POST',
					waitTitle:'Connecting',
					waitMsg:'Sending data...',
					success: function(form, action) {
						updateLoginStatus(action.result.loggedIn, action.result.username);
						App.setAlert("pl", 'Logged in as '+action.result.username);
					},
					failure: function(form, action) {
						switch (action.failureType) {
							case Ext.form.Action.CLIENT_INVALID:
								App.setAlert("error", 'Form fields may not be submitted with invalid values');
								break;
							case Ext.form.Action.CONNECT_FAILURE:
								App.setAlert("error", 'Ajax communication failed');
								break;
							case Ext.form.Action.SERVER_INVALID:
								App.setAlert("error", 'Login failed!');
						}
						//loginForm.getForm().reset();
						Ext.getCmp('loginUsername').focus(true, 10);
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
		items: [loginForm],
		listeners: {
			show: function(self) {
				Ext.getCmp('loginUsername').focus(true, 10);
			}
		}
	});

	// Valid Values with Mode
	function validValue(value, mode) {
		switch(mode) {
			case 'IPv4':
				return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(value);
			case 'IPv6':
				return /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(value);
			case 'Hostname':
				return /^(_?[0-9a-z]+([0-9a-z\-]*[0-9a-z]+)?(\._?[0-9a-z]+([0-9a-z\-]*[0-9a-z]+)?)*)|@$/.test(value);
			case 'UInt16':
				return (/^[0-9]+$/.test(value) && value >= 0 && value <= 65535);
			case 'UInt8':
				return (/^[0-9]+$/.test(value) && value >= 0 && value <= 255);
			case 'UInt':
				return /^[0-9]+$/.test(value);
			case 'DnskeyProtocol':
				return (value == 3);
			case 'Base64Content':
				return /^[a-zA-Z0-9\/+\r\n]+[=]{0,2}$/.test(value);
			case 'StringNoSpaces':
				return !(/\s/g.test(value));
			case 'String':
				return /^.+$/.test(value);
			case 'Email':
				return /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$/.test(value);
			case 'SpfContent':
				return true;
			default:
				return true;
		}
	}

	var rrStore = null;

	// add record window
	function addRecordWindow(server, zone, store) {
		var recordForm = new Ext.FormPanel({
			frame: true,
			defaultType:'textfield',
			monitorValid:true,
			url: URL+'/servers/'+server+'/zones/'+zone+'/records/',

			items: [{
				xtype: "label",
				fieldLabel: "Server",
				text: zonetree.getNodeById('server-'+server).text
			}, {
				xtype: "label",
				fieldLabel: "Zone",
				text: zone
			}, {
				xtype: "combo",
				name: "type",
				fieldLabel: "Type",
				editable: false,
				width: 170,
				height: 40,
				mode: 'remote',
				triggerAction: 'all',
				emptyText: 'Please select first.',
				displayField: 'type',
				valueField: 'type',
				store: new Ext.data.JsonStore({
					url: URL+'/servers/'+server+'/rrtypes',
					root: 'rrtypes',
					fields: ['type', 'fields']
				}),
				listeners:{
					// add Boxes on select
					'select': function(combo, record, index) {

						// first delete old Boxes
						var children = recordForm.findByType('textfield');

						for(i=0;i<children.length;i++) {
							if(children[i].name != 'name' && children[i].name != 'type' && children[i].name != 'ttl') {
								recordForm.remove(children[i]);
							}
						}

						for(key in record.data.fields) {
							recordForm.add({
								name: 'fields['+key+']',
								fieldLabel: key,
								width: 170,
								validType: record.data.fields[key],
								validator: function(value) {
									return validValue(value, this.validType);
								}
							});
						}
						recordForm.doLayout();
					}
				}
			}, {
				fieldLabel: 'Name',
				name: 'name',
				inputType: 'textfield',
				allowBlank:false,
				width: 170,
				validator: function(value) {
					return validValue(value, 'Hostname');
				}
			}, {
				fieldLabel: 'TTL',
				name: 'ttl',
				inputType: 'textfield',
				allowBlank:false,
				width: 170,
				validator: function(value) {
					return validValue(value, 'UInt');
				}
			}],
			buttons: [{
				text: 'Add Record',
				formBind: true,
				handler: function() {
//					Maybe use this? idk...
//					var u = new store.recordType();

//					editor.stopEditing();
//					tab.store.insert(0, u);
//					editor.startEditing(0);

					recordForm.getForm().submit({
						method:'PUT',
						waitTitle:'Connecting',
						waitMsg:'Sending data...',
						success: function(form, action) {
							App.setAlert("ok", 'Record successfully added! (New Id: '+action.result.newid+')');
							recordWindow.hide();
							recordWindow.destroy();
							store.load();
						},
						failure: function(form, action) {
							App.setAlert("error", 'Error: ' + action.result.error);
						}
					});
				}
			}]
		});

		var recordWindow = new Ext.Window({
			title: 'Add record',
			layout: 'anchor',
			closable: true,
			resizeable: false,
			autoHeight: true,
			autoScroll: true,
			width: 320,
			items: [recordForm]
		});

		recordWindow.show();
	}
	// init viewport
	var viewport = new Ext.Viewport({
		layout: 'fit',
		items: [mainContainer]
	});
	
	// check login status
	API.checkLoginStatus(updateLoginStatus);
}
