var ServerItemView = Backbone.Marionette.ItemView.extend({
    template: "#serverItemViewTemplate"
});

var NoServerItemView = Backbone.Marionette.ItemView.extend({
    template: "#noServerItemViewTemplate"
});

var ServerView = Backbone.Marionette.CollectionView.extend({
    id: 'accori',
    className: 'accordion',
    itemView: ServerItemView,
    
    emptyView: NoServerItemView,
    
    loadServers: function() {
		this.collection.fetch({
            success: function(model, response, options) {
                /*
                 * Do maybe something
                 */
            }
        });
	}
});

/*
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
*/