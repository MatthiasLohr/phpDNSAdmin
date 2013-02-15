var Authentication = Backbone.Model.extend({
    url: Config.apiUrl + 'status/',
    defaults: {
        loggedIn: false,
        username: null,
        password: null
    },
    
    isLoggedIn: function() {
        return this.loggedIn;
    }
});