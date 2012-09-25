/**
 * This Controller check if the current User is logged in. It also has methods
 * to login a user. It hasn't an own Model/Store, cause we only have one user
 * logged in at the same time. (In one Browser)
 */
Ext.define('DNSAdmin.controller.LoginController', {
			extend : 'Ext.app.Controller',
			views : ['LoginView'],

			init : function() {
				this.control({
							'loginview button[action=login]' : {
								click : this.loginBtnClicked
							},
							'loginview input' : {
								specialkey : function(field, e) {
									console.log(field, e);
									if (e.getKey() == e.ENTER) {
										this.loginBtnClicked();
									}
								}
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

			loginBtnClicked : function(button) {
				var win = button.up('window'), form = win.down('form')
						.getForm();
				win.setLoading();
				if (form.isValid()) {
					Ext.getCmp('loginStatusLabel').hide();
					form.submit({
								success : function(form, action) {
									if (action.result.loggedIn) {
										// Login was successful
										win.hide();
									} else {
										// Login failed
										Ext.getCmp('loginStatusLabel').show();
									}
									win.setLoading(false);
								},
								failure : function(form, action) {
									win.setLoading(false);
									Ext.Msg
											.alert('Failed',
													action.result.error);
								}
							});
				}
			}
		});