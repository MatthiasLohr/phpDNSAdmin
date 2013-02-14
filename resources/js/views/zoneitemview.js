var ZoneItemView = Backbone.View.extend({

	tagName: 'li',
	template: _.template($('#zoneItemViewTemplate').html()),

	events: {
		'dblclick a': 'changeZoneName',
		'click a': 'openZone'
	},

	initialize: function() {
		this.listenTo(this.model, 'change', this.render);
	},

	render: function() {
		this.$el.html(this.template(this.model.toJSON()));
		return this;
	},

	changeZoneName: function() {
		console.log('dblclicked');
	},

	openZone: function() {
		console.log('clicked');
	}
});