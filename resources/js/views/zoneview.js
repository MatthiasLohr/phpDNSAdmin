var ZoneView = Backbone.View.extend({

	initialize: function () {
		this.zones = this.options.zones;
		this.parentElement = this.options.parentElement;
		this.listenTo(this.zones, 'add', this.addZone);
	},

	addZone: function (zone) {
		item = new ZoneItemView({model: zone});
		this.parentElement.append(item.render().el);
	},

	loadZones: function () {
		this.zones.fetch({update: true, remove: true});
	}
});