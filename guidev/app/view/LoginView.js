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

			items : [{
						xtype : 'form',
						url : Config.apiBaseUrl + '/status',
						border : 0,
						plain : true,
						frame: true,
						bodyPadding : 5,
						layout : 'anchor',
						defaults : {
							anchor : '100%'
						},
						defaultType : 'textfield',
						items : [{
									itemId : 'userName',
									fieldLabel : 'Username',
									name : 'username',
									allowBlank : false,
									selectOnFocus : true
								}, {
									fieldLabel : 'Password',
									name : 'password',
									allowBlank : false,
									inputType : 'password',
									selectOnFocus : true
								}],
						buttons : [{
									text : "Login",
									type : "submit",
									action : "login",
									formBind : true,
									disabled : true
								}],
						defaultFocus : 'userName'
					}]
		});