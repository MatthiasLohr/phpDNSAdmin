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
				rField: this.fields,
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
						field.rViews[field.name] = checked;
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