var ServerItemView = Backbone.Marionette.ItemView.extend({
	template: "#serverItemViewTemplate",
	className: 'accordion-group'
});

var NoServerItemView = Backbone.Marionette.ItemView.extend({
	template: "#noServerItemViewTemplate",
	className: 'accordion-group'
});

var ServerView = Backbone.Marionette.CollectionView.extend({
	id: 'accori', // needed for accordion function
	className: 'accordion',
	itemView: ServerItemView,

	emptyView: NoServerItemView,

	loadServers: function () {
		// This should be done in controller, later
		this.collection.fetch({
			success: function (model, response, options) {
				/*
				 * Do maybe something
				 */
			},
			error: function (model, xhr, options) {
				console.log('xhr', xhr);
				console.log('options', options);
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