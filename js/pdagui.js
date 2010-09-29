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
		store.removeAll();
		//store.loadData(records);
		for (recordid in records) {
			record = records[recordid];
			store.add(new store.recordType({
				id: record.id,
				name: record.name,
				type: record.type,
				content: record.content,
				ttl: record.ttl,
				fields: record.fields
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
					},
					contextmenu: function(node, e) {
						contextMenu = new Ext.menu.Menu({
							items: [{id: 'create-zone', text: 'create zone'}],
							listeners: {
								itemclick: function(item) {
									switch (item.id) {
										case 'create-zone':

										break;
									}
								}
							}
						}),
						node.select();
						contextMenu.show(node.ui.getEl());
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
								autoExpandColumn: 'content',
								listeners: {
									dblclick: function(event) {
										var selection = tab.getSelectionModel();
										if(selection.getCount() == 1) {
											record = selection.getSelected();
											if(!Ext.getCmp(tab.serverkey+"-"+tab.zone+"-"+record.data.id)) {
												changeRecordWindow(tab.serverkey, tab.zone, record.data);
											}
										}
									}
								}
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
				text: 'Add Record',
				handler: function() {
					var tab = zonetabs.getActiveTab();
					if(tab != null) {
						addRecordWindow(tab.serverkey, tab.zone);
					}
				}
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

	// Valid Values with Mode
	function validValue(value, mode) {
		switch(mode) {
			case 'IPv4':
				return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(value);
			case 'IPv6':
				return /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(value);
			case 'Hostname':
				return true;
			case 'UInt16':
				return (/^[0-9]+$/.test(value) && value >= 0 && value <= 65535);
			case 'UInt8':
				return (/^[0-9]+$/.test(value) && value >= 0 && value <= 255);
			case 'UInt':
				return /^[0-9]+$/.test(value);
			case 'DnskeyProtocol':
				return (value == 3);
			case 'Base64Content':
				return true;
			case 'StringNoSpaces':
				return !(/\s/g.test(value));
			case 'String':
				return true;
			case 'Email':
				return /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$/.test(value);
			case 'SpfContent':
				return true;
			default:
				return true;
		}
	}

	function changeRecordWindow(server, zone, record) {
		var recordForm = new Ext.FormPanel({
			id: server+"-"+zone+"-"+record.id,
			frame: true,
			defaultType:'textfield',
			monitorValid:true,
			url: URL+'/servers/'+server+'/zones/'+zone+'/records/'+record.id,
			items: [{
					xtype: "label",
					fieldLabel: "Server",
					text: zonetree.getNodeById('server-'+server).text
				}, {
					xtype: "label",
					fieldLabel: "Zone",
					text: zone
				}, {
					xtype: "label",
					fieldLabel: "Id",
					text: record.id
				}, {
					xtype: "label",
					fieldLabel: "Type",
					text: record.type
				}, {
					xtype: "hidden",
					name: "type",
					value: record.type
				}, {
					xtype: "textfield",
					width: 170,
					fieldLabel: "Name",
					name: "name",
					value: record.name,
					allowBlank: false
				}, {
					xtype: "textfield",
					width: 170,
					fieldLabel: "TTL",
					name: "ttl",
					value: record.ttl,
					validator: function(value) {
						return /^[0-9]+$/.test(value);
					}
				}],
			buttons: [{
					text: 'Change Record',
					formBind: true,
					handler: function() {
						recordForm.getForm().submit({
							method:'POST',
							waitTitle:'Connecting',
							waitMsg:'Sending data...',
							success: function(form, action) {
								displayRecords(server, zone, action.result.records);
								notifyMsg('Record successfully changed!');
								recordWindow.hide();
								recordWindow.destroy();
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
										data = Ext.util.JSON.decode(action.response.responseText);
										notifyMsg(data.error, 'Change record failed!');
								}
							}
						});
					}
				}]
		});

		// add fields
		for(key in record.fields) {
			recordForm.add({
				name: 'fields['+key+']',
				fieldLabel: key,
				width: 170,
				value: record.fields[key].value,
				validType: record.fields[key].type,
				validator: function(value) {
					return validValue(value, this.validType);
				}
			});
		}
		recordForm.doLayout();

		var recordWindow = new Ext.Window({
			title: 'Change record',
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

	// add record window
	function addRecordWindow(server, zone) {
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
					width: 170
				}, {
					fieldLabel: 'TTL',
					name: 'ttl',
					inputType: 'textfield',
					allowBlank:false,
					width: 170,
					validator: function(value) {
						return /^[0-9]+$/.test(value);
					}
				}],
			buttons: [{
					text: 'Add Record',
					formBind: true,
					handler: function() {
						recordForm.getForm().submit({
							method:'PUT',
							waitTitle:'Connecting',
							waitMsg:'Sending data...',
							success: function(form, action) {
								displayRecords(server, zone, action.result.records);
								notifyMsg('Record successfully added! (New Id: '+action.result.newid+')');
								recordWindow.hide();
								recordWindow.destroy();
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
										data = Ext.util.JSON.decode(action.response.responseText);
										notifyMsg(data.error, 'Add record failed!');
								}
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
