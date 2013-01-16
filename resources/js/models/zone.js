// Zone Model
var Zone = Backbone.Model.extend({
	defaults: {
		id: null,
		name: ""
	}
});

var Zones = Backbone.Collection.extend({
	url: function() {
		return this.parentUrl + '/zones/'
	},
	model: Zone,

	parse: function(resp) {
		return resp.zones;
	}
});