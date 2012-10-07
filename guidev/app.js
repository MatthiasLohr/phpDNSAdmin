/**
 * Configuration
 */
Ext.define('Config', {
			singleton : true, /* do not change this line! */

			apiBaseUrl : '/phpDNSAdmin/api'
		});

Ext.application({
			name : 'DNSAdmin',
			controllers : ['LoginController'],
			models : ['Server'],
			autoCreateViewport : true,
			launch : function() {

			}
		});