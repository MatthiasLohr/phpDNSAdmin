/*
 * Simple Validator "Class" with static validValue Function
 * Author: Andreas Litt
 * http://phpdnsadmin.sourceforge.net/
 */
function DNSValidator() {
	
}

DNSValidator.validValue = function(value, mode) {
	switch(mode) {
		case 'IPv4':
			return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(value);
		case 'IPv6':
			return /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(value);
		case 'Hostname':
			return /^(_?[0-9a-z]+([0-9a-z\-]*[0-9a-z]+)?(\._?[0-9a-z]+([0-9a-z\-]*[0-9a-z]+)?)*)|@$/.test(value);
		case 'UInt16':
			return (/^[0-9]+$/.test(value) && value >= 0 && value <= 65535);
		case 'UInt8':
			return (/^[0-9]+$/.test(value) && value >= 0 && value <= 255);
		case 'UInt':
			return /^[0-9]+$/.test(value);
		case 'DnskeyProtocol':
			return (value == 3);
		case 'Base64Content':
			return /^[a-zA-Z0-9\/+\r\n]+[=]{0,2}$/.test(value);
		case 'StringNoSpaces':
			return !(/\s/g.test(value));
		case 'String':
			return /^.+$/.test(value);
		case 'Email':
			return /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$/.test(value);
		case 'SpfContent':
			return true;
		default:
			return true;
	}
}

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

/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ns('Ext.ux.grid');
Ext.ux.grid.MultiCheckColumn = Ext.extend(Ext.grid.Column, {
	processEvent : function(name, e, grid, rowIndex, colIndex){
		return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
	},

	renderer : function(v, p, record){
		p.css += ' x-grid3-check-col-td';

		var out = '<div>';
		var count = 0;
		for(i in v) {
			count++;
		}

		var width = (100/count) + "%";

		for(views in v) {
			out += String.format('<div style="width: {2}; float: left;" class="x-grid3-check-col{0}" title="{1}">&#160;</div>', v[views] ? '-on' : '', views, width);
		}
		out += '</div>';
		return out;
	},

	// Deprecate use as a plugin. Remove in 4.0
	init: Ext.emptyFn
});

Ext.preg('multicheckcolumn', Ext.ux.grid.MultiCheckColumn);
Ext.grid.MultiCheckColumn = Ext.ux.grid.MultiCheckColumn;
Ext.grid.Column.types.multicheckcolumn = Ext.ux.grid.MultiCheckColumn;


/*!
 * phpDNSAdmin
 * Author: Andreas Litt
 * http://phpdnsadmin.sourceforge.net/
 */

Ext.ux.grid.ContentColumn = Ext.extend(Ext.grid.Column, {
	
	value: null,
	
	processEvent : function(name, e, grid, rowIndex, colIndex){
		return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
	},

	renderer : function(v, p, record) {
		var value = '';
		for(key in v) {
			value += ' ' + v[key].value;
		}
		value = Ext.util.Format.trim(value);
		return value;
	},
	init: Ext.emptyFn
});

Ext.preg('contentcolumn', Ext.ux.grid.ContentColumn);
Ext.grid.ContentColumn = Ext.ux.grid.ContentColumn;
Ext.grid.Column.types.contentcolumn = Ext.ux.grid.ContentColumn;


Ext.DNSContent = Ext.extend(Ext.form.Field, {
	defaultAutoCreate : {
		tag: "div"
	},
	fields: null,
	elems: new Array(),

	initComponent: function() {
		Ext.DNSContent.superclass.initComponent.call(this);
		this.addEvents();
	},

	setValue: function(value) {
		Ext.DNSContent.superclass.setValue.apply(this, arguments);
		this.fields = value;
		this.updateFields();
		return this;
	},

	getValue: function() {
		return this.fields;
	},

	updateFields: function() {
		if(this.el) {
			// remove all Childs
			this.el.dom.innerHTML = '';
			this.elems.length = 0;
		}

		var recordForm = new Ext.FormPanel({
			labelWidth: 80,
			bodyBorder: false,
			bodyCssClass: 'x-row-editor-body',
			defaultType:'textfield',
			monitorValid:true,
			renderTo: this.el
		});

		for(field in this.fields) {
			var Txtfield = new Ext.form.TextField({
				fieldLabel: field,
				name: field,
				value: this.fields[field].value,
				validType: this.fields[field].type,
				rField: this.fields,
				validator: function(value) {
					return DNSValidator.validValue(value, this.validType);
				},
				listeners: {
					change: function(field, newValue, oldValue) {
						if(String(newValue) !== String(oldValue)) {
							field.rField[field.name].value = newValue;
						}
					}
				}
			});
			recordForm.add(Txtfield);
			this.elems.push(Txtfield);
		}
		recordForm.doLayout();
	},
	
	isValid: function() {
		for(var i = 0; i < this.elems.length; i++) {
			if(!this.elems[i].isValid()) {
				return false;
			}
		}
		return true;
	},
	
	beforeDestroy: function() {
	}
});

Ext.reg('dnscontent', Ext.DNSContent);


Ext.ViewEditor = Ext.extend(Ext.form.Field, {
	defaultAutoCreate : {
		tag: "div"
	},
	views: null,
	elems: new Array(),

	initComponent: function() {
		Ext.ViewEditor.superclass.initComponent.call(this);
		this.addEvents();
	},

	setValue: function(value) {
		Ext.ViewEditor.superclass.setValue.apply(this, arguments);
		this.views = value;
		this.updateViews();
		return this;
	},

	getValue: function() {
		return this.views;
	},

	updateViews: function() {
		if(this.el) {
			// remove all Childs
			this.el.dom.innerHTML = '';
			this.elems.length = 0;
		}

		var viewForm = new Ext.FormPanel({
			labelWidth: 80,
			bodyBorder: false,
			bodyCssClass: 'x-row-editor-body',
			defaultType:'checkbox',
			monitorValid:true,
			renderTo: this.el
		});

		for(view in this.views) {
			var Checkbox = new Ext.form.Checkbox({
				fieldLabel: view,
				name: view,
				checked: this.views[view],
				rViews: this.views,
				listeners: {
					check: function(field, checked) {
						field.rViews[field.name] = checked?1:0;
					}
				}
			});
			viewForm.add(Checkbox);
			this.elems.push(Checkbox);
		}
		viewForm.doLayout();
	},

	isValid: function() {
		return true;
	},

	beforeDestroy: function() {
	}
});

Ext.reg('vieweditor', Ext.ViewEditor);

Ext.namespace('Ext.ux');

Ext.ux.PageSizePlugin = function() {
	Ext.ux.PageSizePlugin.superclass.constructor.call(this, {
		store: new Ext.data.SimpleStore({
			fields: ['text', 'value'],
			data: [['10', 10], ['20', 20], ['30', 30], ['50', 50], ['100', 100]]
		}),
		mode: 'local',
		displayField: 'text',
		valueField: 'value',
		editable: false,
		allowBlank: false,
		triggerAction: 'all',
		width: 40
	});
};

Ext.extend(Ext.ux.PageSizePlugin, Ext.form.ComboBox, {
	init: function(paging) {
		paging.on('render', this.onInitView, this);
	},

	onInitView: function(paging) {
		paging.add('-',
		this,
		'Items per page'
	);
		this.setValue(paging.pageSize);
		this.on('select', this.onPageSizeChanged, paging);
	},

	onPageSizeChanged: function(combo) {
		this.pageSize = parseInt(combo.getValue());
		this.doLoad(0);
	}
});