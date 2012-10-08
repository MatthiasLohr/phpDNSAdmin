/**
 * This Controller catch all ErrorEvents and displays it.
 */
Ext.define('DNSAdmin.controller.ErrorController', {
			extend : 'Ext.app.Controller',
			views : [],
			requires : ['DNSAdmin.Config', 'Ext.window.MessageBox'],
			init : function() {
				this.application.on({
							errorEvent : this.onErrorEvent, /* Maybe more Events here? */
							scope : this
						});
			},

			onErrorEvent : function(message) {
				console.log('ErrorController > errorEvent');
				console.log(Ext);
				Ext.MessageBox.show({
							title : 'Error',
							msg : message,
							buttons : Ext.MessageBox.OK,
							icon : Ext.MessageBox.ERROR
						});
			}
		});