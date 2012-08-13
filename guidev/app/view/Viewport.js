Ext.define('DNSAdmin.view.Viewport', {
    extend: 'Ext.container.Viewport',
    requires: [
    ],
    layout: 'fit',
    initComponent: function() {
        this.items = {
            xtype: 'panel',
            dockedItems: [{
                dock: 'top',
                xtype: 'toolbar',
                height: 25,
                items: [
                    '->',
                    '-', {
                    xtype: 'component',
                    html: 'phpDNSAdmin'
                }]
            }],
            layout: {
                type: 'hbox',
                align: 'middle',
                pack: 'center'
            },
            items: [{
                xtype: 'button',
                text: 'Click me',
                handler: function() {
                    alert('You clicked the button!');
                }
            }]
        };

        this.callParent();
    }
});