Ext.define('DNSAdmin.view.LoginDialog', {
	extend : 'Ext.Window',
	requires : ['DNSAdmin.Config'],
	alias : 'widget.loginview',
	title : 'Login',
	autoHeight : true,
	closable : false,
	resizable : false,
	draggable : false,
	closeAction : 'hide',
	hideMode : 'display',
	width : 300,
	layout : 'fit',
	border : false,
	modal : true,

	beforehide : function() {
		console.log('Event', 'beforehide!');
	},
	onRender : function() {
		var form = this.down('form').getForm();
		form.url = DNSAdmin.Config.apiBaseUrl + '/status';
		this.callParent(arguments);
	},
	items : [{
				xtype : 'form',
				border : 0,
				plain : true,
				frame : true,
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
							xtype : 'panel',
							frame : true,
							html : '<span style="color: red;">Login incorrect!<span>',
							hideMode : 'display',
							hidden : true,
							id : 'loginStatusLabel'

						}, {
							text : "Login",
							type : "submit",
							action : "login",
							formBind : true,
							disabled : true
						}],
				defaultFocus : 'userName'
			}]
});