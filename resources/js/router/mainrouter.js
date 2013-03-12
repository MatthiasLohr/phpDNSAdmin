// Controller Class for Router
var MainController = {
	indexAction: function () {
		console.log('MainRouter:indexAction');
		// Every Action starts with a Login check
		this.checkLogin(function() {
			Backbone.history.navigate('start/', {trigger: true});
		});
	},

	startAction: function () {
		console.log('MainRouter:startAction');
		// Every Action starts with a Login check
		this.checkLogin(function() {
			window.serverView.loadServers();
		});
	},

	showLoginAction: function () {
		console.log('MainRouter:showLoginAction');
		var loginView = new LoginView();
		var modal = new Backbone.BootstrapModal({
			content: loginView,
			title: 'Login',
			animate: true,
			allowCancel: false,
			escape: false,
			focusOk: false,
			okText: 'Login',
			okLoadingText: 'Loading...'
		});
		modal.on('ok', function () {
			if (modal.$el.find('form').valid()) {
				modal.loading(true);

				var auth = new Authentication();

				// Login
				auth.save({
					username: modal.$el.find('#username').val(),
					password: modal.$el.find('#password').val()
				}, {
					modal: modal,
					success: function (model, response, options) {
						options.modal.loading(false);
						if (model.attributes.loggedIn) {
							// Login was successful
							options.modal.close();
							Backbone.history.navigate('start/', {trigger: true});
						}
						else {
							modal.$el.find('#loginErrorAlert').removeClass('hidden');
						}
					},
					error: function (model, xhr, options) {
						// TODO: add error handling
					}
				});
			}
			modal.preventClose();
		});

		modal.open();
	},

	checkLogin: function (callback) {
		var auth = new Authentication();
		auth.fetch({
			callback: callback,
			success: function (model, response, options) {
				if (model.attributes.loggedIn) {
					options.callback();
				}
				else {
					Backbone.history.navigate('login/', {trigger: true});
				}
			},
			error: function (model, xhr, options) {
				// TODO: add error handling
			}
		});
	}
}

var MainRouter = Backbone.Marionette.AppRouter.extend({
	controller: MainController,
	appRoutes: {
		"": "indexAction",
		"login/": "showLoginAction",
		"start/": "startAction"
	}
});