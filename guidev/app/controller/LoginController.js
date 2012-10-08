/**
 * This Controller check if the current User is logged in. It also has methods
 * to login a user. It hasn't an own Model/Store, cause we only have one user
 * logged in at the same time. (In one Browser)
 */
Ext.define('DNSAdmin.controller.LoginController', {
			extend : 'Ext.app.Controller',
			views : ['LoginDialog', 'LoggedInText', 'Viewport'],
			requires : ['DNSAdmin.Config'],
			refs : [{
						ref : 'loggedInText',
						selector : 'loggedin'
					}],
			init : function() {
				this.control({
							'loginview button[action=login]' : {
								click : this.loginBtnClicked
							},
							'loginview textfield' : {
								specialkey : function(field, e) {
									if (e.getKey() == e.ENTER) {
										this.loginBtnClicked(field);
									}
								}
							}
						});

				this.application.on({
							loggedInEvent : this.onLoggedInEvent,
							notLoggedInEvent : this.onNotLoggedInEvent,
							scope : this
						});

				this.checkIfLoggedIn();
			},

			checkIfLoggedIn : function() {
				var app = this.application;
				Ext.Ajax.request({
							url : DNSAdmin.Config.apiBaseUrl + '/status',

							success : function(response) {
								var text = response.responseText;
								var jsonResponse = Ext.JSON.decode(text);
								if (jsonResponse.success) {
									var loggedInView = Ext.widget('loggedin');
									if (jsonResponse.loggedIn) {
										// User is logged in, do something!
										app.fireEvent('loggedInEvent',
												jsonResponse.username);
									} else {
										// User is not logged in! Call
										// notLoggedInEvent - Event
										app.fireEvent('notLoggedInEvent');
									}
								} else {
									// There was an Error on Server... need some
									// Error handling here!
									// TODO: Error Handling.
								}
							}
						});
			},

			loginBtnClicked : function(srcElement) {
				var win = srcElement.up('window'), form = win.down('form')
						.getForm();
				win.setLoading();
				var app = this.application;
				if (form.isValid()) {
					Ext.getCmp('loginStatusLabel').hide();
					form.submit({
								success : function(form, action) {
									if (action.result.loggedIn) {
										app.fireEvent('loggedInEvent',
												action.result.username);
										// Login was successful
										win.hide();
									} else {
										// Login failed
										app.fireEvent('notLoggedInEvent');
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
			},

			/*
			 * Functions which will be called on Events.
			 */
			onLoggedInEvent : function(username, env) {
				console.log('onLoggedInEvent');
				var loggedInTextView = this.getLoggedInText();
				loggedInTextView.setUsername(username);
			},

			onNotLoggedInEvent : function() {
				var loginView = Ext.widget('loginview');
				console.log(loginView);
				loginView.show();
			}
		});