var ServerItemView = Backbone.Marionette.ItemView.extend({
    tagName: "li",
    template: "#serverItemViewTemplate"
});

/*
	Item which represent a Server Model
*/
/*
var ServerItemView = Backbone.View.extend({

	tagName: 'li',
	className: 'nav-header',
	template: _.template($('#serverItemViewTemplate').html()),

	initialize: function() {
		this.listenTo(this.model, 'change', this.render);
	},

	render: function() {
		this.$el.html(this.template(this.model.toJSON()));
		return this;
	}
});
*/