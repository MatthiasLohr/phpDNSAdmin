Ext.namespace('Ext.data');
Ext.data.DnsWriter = Ext.extend(Ext.data.DataWriter, {
	render: function(params, baseParams, data){
		Ext.apply(params, baseParams);
		Ext.apply(params, data);
	},
	createRecord : function(r) {
		var params = {};
		var data = r.data;

		for(key in data){
			if(typeof data[key] == 'object') {
				for(skey in data[key]) {
					params[key+"\x5B"+skey+"\x5D"] = data[key][skey].value;
				}
			} else {
				params[key] = data[key];
			}
		}

		return params;
	},
	updateRecord : function(r) {
		var params = {};
		var data = r.data;

		for(key in data){
			if(typeof data[key] == 'object') {
				for(skey in data[key]) {
					params[key+"\x5B"+skey+"\x5D"] = data[key][skey].value;
				}
			} else {
				params[key] = data[key];
			}
		}

		return params;
	},
	destroyRecord : function(r) {
		return {
			id: r.id
		};
	}

});