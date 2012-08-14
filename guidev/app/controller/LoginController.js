/**
 * This Controller check if the current User is logged in. It also has methods
 * to login a user. It hasn't an own Model/Store, cause we only have one user
 * logged in at the same time. (In one Browser)
 */
Ext.define('DNSAdmin.controller.LoginController', {
			extend : 'Ext.app.Controller',
			views: [
				'LoginView'
			],

			init : function() {
				this.control({
					'loginview button[action=login]': {
						click: this.loginBtnClicked
					}
				});
				
				this.checkIfLoggedIn();
			},

			checkIfLoggedIn : function() {
				Ext.Ajax.request({
							url : Config.apiBaseUrl + '/status',

							success : function(response) {
								var text = response.responseText;
								var jsonResponse = Ext.JSON.decode(text);
								if (jsonResponse.success) {
									if (jsonResponse.loggedIn) {
										// User is logged in, do something!
										console.log('LoginController',
												'Logged in!');
									} else {
										// User is not logged in! Call Login
										// View to do this Job!												
										var loginView = Ext.widget('loginview');
										loginView.show();
									}
								} else {
									// There was an Error on Server... need some
									// Error handling here!
								}
							}
						});
			},
			
			loginBtnClicked: function(button) {
				var win    = button.up('window'),
        form   = win.down('form'),
        record = form.getRecord(),
        values = form.getValues();
        form.submit();
        console.log(values);
			}
		});