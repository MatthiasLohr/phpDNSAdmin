var LoginView = Backbone.Marionette.ItemView.extend({
    template: "#loginViewTemplate",
    
    render: function() {
        this.$el.html(_.template($(this.template).html()));
        this.$el.find('form').validate({
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                }
            },
            highlight: function(element) {
                $(element).closest('.control-group').removeClass('success').addClass('error');
            },
            success: function(element) {
                $(element).closest('.control-group').removeClass('error').addClass('success');
            }
        });
    }
});