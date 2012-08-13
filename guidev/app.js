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
			autoCreateViewport : true,
			launch : function() {
			}
		});