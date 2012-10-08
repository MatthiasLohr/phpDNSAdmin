Ext.define('DNSAdmin.view.LoggedInText', {
			extend : 'Ext.toolbar.TextItem',
			alias : 'widget.loggedin',
			defaultText : 'Not logged in!',
			
			initComponent : function() {
				this.reset();
				this.callParent(arguments);
			},
			
			setUsername : function(username) {
				this.setText('Logged in as ' + username);
			},
			
			reset : function() {
				this.setText(this.defaultText);
			}
		});