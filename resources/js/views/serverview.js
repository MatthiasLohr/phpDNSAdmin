var ServerView = Backbone.View.extend({

	template: _.template($('#serverViewTemplate').html()),

	initialize: function() {
		this.servers = new Servers();
		this.listenTo(this.servers, 'add', this.serverAdded);
	},

	serverAdded: function(server) {
		$('#serverviewbox').append(this.template(server.toJSON()));
		item = new ServerItemView({model: server});
		$('#serverlist_'+server.id).append(item.render().el);
		server.fetch();
		zoneview = new ZoneView({zones: server.zones, parentElement: $('#serverlist_'+server.id)});
		zoneview.loadZones();
	},

	loadServers: function() {
		this.servers.fetch({update: true, remove: true, success: function() {
			$('#serverloadingbox').spin(false);
		}});
	}
});