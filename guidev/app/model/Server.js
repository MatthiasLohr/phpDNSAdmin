Ext.define('DNSAdmin.model.Server', {
    extend: 'Ext.data.Model',
    fields: ['id', 'name'],

    proxy: {
        type: 'ajax',
        url: Config.apiBaseUrl + '/servers',
        reader: {
            type: 'json',
            root: 'servers'
        }
    }
});