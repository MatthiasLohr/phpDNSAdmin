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

/**
 * @class Ext.ux.grid.CheckColumn
 * @extends Ext.grid.Column
 * <p>A Column subclass which renders a checkbox in each column cell which toggles the truthiness of the associated data field on click.</p>
 * <p><b>Note. As of ExtJS 3.3 this no longer has to be configured as a plugin of the GridPanel.</b></p>
 * <p>Example usage:</p>
 * <pre><code>
var cm = new Ext.grid.ColumnModel([{
       header: 'Foo',
       ...
    },{
       xtype: 'checkcolumn',
       header: 'Indoor?',
       dataIndex: 'indoor',
       width: 55
    }
]);

// create the grid
var grid = new Ext.grid.EditorGridPanel({
    ...
    colModel: cm,
    ...
});
 * </code></pre>
 * In addition to toggling a Boolean value within the record data, this
 * class toggles a css class between <tt>'x-grid3-check-col'</tt> and
 * <tt>'x-grid3-check-col-on'</tt> to alter the background image used for
 * a column.
 */
Ext.ux.grid.CheckColumn = Ext.extend(Ext.grid.Column, {

	/**
     * @private
     * Process and refire events routed from the GridView's processEvent method.
     */
	processEvent : function(name, e, grid, rowIndex, colIndex){
		if (name == 'mousedown') {
			var record = grid.store.getAt(rowIndex);
			record.set(this.dataIndex, !record.data[this.dataIndex]);
			return false; // Cancel row selection.
		} else {
			return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
		}
	},

	renderer : function(v, p, record){
		p.css += ' x-grid3-check-col-td';
		return String.format('<div class="x-grid3-check-col{0}">&#160;</div>', v ? '-on' : '');
	},

	// Deprecate use as a plugin. Remove in 4.0
	init: Ext.emptyFn
});

// register ptype. Deprecate. Remove in 4.0
Ext.preg('checkcolumn', Ext.ux.grid.CheckColumn);

// backwards compat. Remove in 4.0
Ext.grid.CheckColumn = Ext.ux.grid.CheckColumn;

// register Column xtype
Ext.grid.Column.types.checkcolumn = Ext.ux.grid.CheckColumn;


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
			value += v[key].value;
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