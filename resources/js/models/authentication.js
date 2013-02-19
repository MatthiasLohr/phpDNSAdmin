var Authentication = Backbone.Model.extend({
    url: Config.apiUrl + 'status/',
    defaults: {
        loggedIn: false,
        username: null,
        password: null
    },
    
    isLoggedIn: function() {
        return this.loggedIn;
    },
    
    parse: function (response) {
        return {
            loggedIn: response.loggedIn,
            username: response.username,
            password: null
        };
    },
});