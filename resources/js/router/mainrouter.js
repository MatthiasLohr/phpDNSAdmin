// Controller Class for Router
var MainController = {
    indexAction: function() {
        console.log('MainRouter:indexAction');
        this.checkLogin({
            success: function() {
                console.log('User is logged in, navigate to #start/');
                Backbone.history.navigate('start/');
            },
            failure: function() {
                console.log('User is not logged in, navigate to #login/');
                Backbone.history.navigate('login/');
            }
        });
    },
    
    showLoginAction: function() {
        var loginView = new LoginView();
        var modal = new Backbone.BootstrapModal({
            content: loginView,
            title: 'Login',
            animate: true,
            allowCancel: false,
            escape: false,
            focusOk: false,
            okText: 'Login'
        });
        modal.on('ok', function() {
            if(modal.$el.find('form').valid()) {
                $btn = modal.$el.find('.btn.ok');
                $btn.attr('disabled', true);
                $btn.text('Loading...');
                $btn.addClass('disabled');
                
                var auth = new Authentication({
                    username: modal.$el.find('#username').val(),
                    password: modal.$el.find('#password').val()
                });
                
                // Login
                auth.save({
                    success: function(model, response, options) {
                        console.log('r', response);
                    }
                });
            }
            modal.preventClose();
        });
        
        modal.open();
    },
    
    checkLogin: function(options) {
        var auth = new Authentication();
        auth.fetch({
            callbacks: options,
            success: function(model, response, options) {
                if(model.isLoggedIn()) {
                    options.callbacks.success();
                } else {
                    options.callbacks.failure();
                }
            }
        });
    }
}

var MainRouter = Backbone.Marionette.AppRouter.extend({
    controller: MainController,
    appRoutes: {
        "": "indexAction",
        "login/": "showLoginAction"
    }
});