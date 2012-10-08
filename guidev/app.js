Ext.application({
			name : 'DNSAdmin',
			controllers : ['ErrorController', 'LoginController'],
			models : ['Server'],

			autoCreateViewport : true,
			launch : function() {
			}
		});