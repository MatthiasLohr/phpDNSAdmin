Ext.ux.Image = Ext.extend(Ext.BoxComponent, {
	url  : Ext.BLANK_IMAGE_URL,

	autoEl: {
		tag: 'img',
		src: Ext.BLANK_IMAGE_URL,
		cls: 'tng-managed-image'
	},

	initComponent : function(){
		Ext.ux.Image.superclass.initComponent.call(this);
		this.addEvents('load');
	},

	onRender: function() {
		Ext.ux.Image.superclass.onRender.apply(this, arguments);
		this.el.on('load', this.onLoad, this);
		if(this.url){
			this.setSrc(this.url);
		}
	},

	onLoad: function() {
		this.fireEvent('load', this);
	},

	setSrc: function(src) {
		this.el.dom.src = src;
	}
});