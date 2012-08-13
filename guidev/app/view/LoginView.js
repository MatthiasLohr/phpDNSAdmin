Ext.define('DNSAdmin.view.LoginView', {
			extend : 'Ext.Window',
			alias : 'widget.loginview',
			title : 'Login',
			autoHeight : true,
			closable : false,
			resizable : false,
			draggable : false,
			width : 300,
			layout : 'fit',
			border : false,
			modal : true,

			initComponent : function() {
				Ext.apply(this, {
							items : [{
										xtype : 'form',
										plain : true,
										frame : true,
										border : 0,
										bodyPadding : 5,
										items : [{
													itemId : 'userName',
													xtype : 'textfield',
													fieldLabel : 'Username',
													name : 'username',
													allowBlank : false,
													anchor : '100%',
													selectOnFocus : true
												}, {
													xtype : 'textfield',
													fieldLabel : 'Password',
													name : 'password',
													allowBlank : false,
													inputType : 'password',
													anchor : '100%',
													selectOnFocus : true
												}]
									}]
						});
				this.callParent(arguments);
			},

			buttons : [{
						text : "Login",
						type : "submit",
						action : "login",
						formBind : true
					}],
			defaultFocus : 'userName'
		});