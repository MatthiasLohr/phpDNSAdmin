Ext.define('DNSAdmin.view.Viewport', {
			extend : 'Ext.container.Viewport',
			requires : ['DNSAdmin.view.LoggedInText', 'DNSAdmin.view.LoginDialog'],
			layout : 'fit',
			initComponent : function() {
				this.setLoading();
				this.items = {
					xtype : 'panel',
					dockedItems : [{
								dock : 'top',
								xtype : 'toolbar',
								height : 35,
								layout : {
									type : 'hbox',
									align : 'middle'
								},
								items : ['->', {
											xtype : 'loggedin'
										}, '-', {
											xtype : 'image',
											src : 'resources/images/logo.png'
										}]
							}],
					layout : {
						type : 'hbox',
						align : 'stretch'
					},
					items : [{
								width : 250,
								xtype : 'panel',
								layout : {
									type : 'vbox',
									align : 'stretch'
								},
								items : []
							}, {

							}]
				};

				this.callParent();
			}
		});