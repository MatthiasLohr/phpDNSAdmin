Ext.define('DNSAdmin.view.Viewport', {
			extend : 'Ext.container.Viewport',
			requires : [],
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
								items : [
										'->',
										'-', {
											xtype : 'image',
											src : 'resources/images/logo.png'
										}]
							}],
					layout : {
						type : 'hbox',
						align : 'middle',
						pack : 'center'
					},
					items : [{
								xtype : 'button',
								text : 'Click me',
								handler : function() {
									alert('You clicked the button!');
								}
							}]
				};

				this.callParent();
			}
		});