/*
 * Tine 2.0
 * 
 * @package     Sales
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 *
 */
 
Ext.namespace('Tine.Sales');

/**
 * Product edit dialog
 * 
 * @namespace   Tine.Sales
 * @class       Tine.Sales.ProductEditDialog
 * @extends     Tine.widgets.dialog.EditDialog
 * 
 * <p>Product Edit Dialog</p>
 * <p><pre>
 * TODO         make category a combobox + get data from settings
 * </pre></p>
 * 
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * 
 * @param       {Object} config
 * @constructor
 * Create a new Tine.Sales.ProductGridPanel
 */
Tine.Sales.ProductEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
    tbarItems: [{xtype: 'widget-activitiesaddbutton'}],
    
    /**
     * @private
     */
    initComponent: function() {
        Tine.Sales.ProductEditDialog.superclass.initComponent.call(this);
    },
    
    /**
     * executed when record is loaded
     * @private
     */
    onRecordLoad: function() {
        Tine.Sales.ProductEditDialog.superclass.onRecordLoad.call(this);
    },
    
    /**
     * returns dialog
     * 
     * NOTE: when this method gets called, all initalisation is done.
     */
    getFormItems: function() {
        return {
            xtype: 'tabpanel',
            border: false,
            plain:true,
            activeTab: 0,
            border: false,
            plugins: [{
                ptype : 'ux.tabpanelkeyplugin'
            }],
            items:[
                {
                title: this.app.i18n.n_('Product', 'Product', 1),
                autoScroll: true,
                border: false,
                frame: true,
                layout: 'border',
                items: [{
                    region: 'center',
                    xtype: 'columnform',
                    labelAlign: 'top',
                    formDefaults: {
                        xtype:'textfield',
                        anchor: '100%',
                        labelSeparator: '',
                        columnWidth: .333
                    },
                    items: [[{
                        columnWidth: 1,
                        fieldLabel: this.app.i18n._('Name'),
                        name: 'name',
                        allowBlank: false
                    }], [{
                        columnWidth: 1,
                        xtype: 'numberfield',
                        fieldLabel: this.app.i18n._('Price'),
                        name: 'price',
                        allowNegative: false,
                        allowBlank: false
                        //decimalSeparator: ','
                    }], [{
                        columnWidth: 1,
                        fieldLabel: this.app.i18n._('Manufacturer'),
                        name: 'manufacturer'
                    }], [{
                        columnWidth: 1,
                        fieldLabel: this.app.i18n._('Category'),
                        name: 'category'
                    }], [{
                        columnWidth: 1,
                        fieldLabel: this.app.i18n._('Description'),
                        emptyText: this.app.i18n._('Enter description...'),
                        name: 'description',
                        xtype: 'textarea',
                        height: 150
                    }]] 
                }, {
                    // activities and tags
                    layout: 'accordion',
                    animate: true,
                    region: 'east',
                    width: 210,
                    split: true,
                    collapsible: true,
                    collapseMode: 'mini',
                    header: false,
                    margins: '0 5 0 5',
                    border: true,
                    items: [
                        new Tine.widgets.activities.ActivitiesPanel({
                            app: 'Sales',
                            showAddNoteForm: false,
                            border: false,
                            bodyStyle: 'border:1px solid #B5B8C8;'
                        }),
                        new Tine.widgets.tags.TagPanel({
                            app: 'Sales',
                            border: false,
                            bodyStyle: 'border:1px solid #B5B8C8;'
                        })
                    ]
                }]
            }, new Tine.widgets.activities.ActivitiesTabPanel({
                app: this.appName,
                record_id: this.record.id,
                record_model: this.appName + '_Model_' + this.recordClass.getMeta('modelName')
            })
            ]
        };
    }
});
