// Server Model
var Server = Backbone.Model.extend({
	defaults: {
		id: null,
		name: "",
		zones: null
	},
	fetch: function() {
		this.zones = new Zones();
		this.zones.parentUrl = this.url();
	}
});

// Collection for Servers
var Servers = Backbone.Collection.extend({
	url: Config.apiUrl + 'servers/',
	model: Server,

	parse: function(resp) {
		return resp.servers;
	}
});