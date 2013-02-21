// Server Model
var Server = Backbone.Model.extend({
	defaults: {
		sysname: "",
		name: "",
		zones: null
	}/*,
	 fetch: function () {
	 this.zones = new Zones();
	 this.zones.parentUrl = this.url();
	 }*/
});

// Collection for Servers
var Servers = Backbone.Collection.extend({
	url: Config.apiUrl + 'servers/',
	model: Server,

	parse: function (resp) {
		if (resp.success) {
			return resp.servers;
		}
		DNSApp.vent.trigger('error', resp.errorMessage);
	}
});