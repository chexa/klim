/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Islider
 * @copyright  Copyright (c) 2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */
var AWAutorelatedCategoryForm = Class.create({
    initialize: function(name) {
        this.global = window;
        this._objectName = name;
        this.global[name] = this;
        
        this.selectors = {
            categoriesArea: 'currently_viewed_categories_area',
            categoriesGrid: 'gridcontainer_categories'
        };
        
        document.observe("dom:loaded", this.init.bind(this));
    },
    
    _getSelfObjectName: function() {
        return this._objectName;
    },
    
    init: function() {
        if(typeof(this.selectors.categoriesArea) != 'undefined' && $(this.selectors.categoriesArea))
            $(this.selectors.categoriesArea).observe('change', this.global[this._getSelfObjectName()].checkCategoriesArea.bind(this));
        this.checkCategoriesArea();
        var removeButton = $$('#conditions_fieldset>span.rule-param>a.rule-param-remove').first();
        if(typeof removeButton != 'undefined')
            removeButton.hide();
    },
    
    checkCategoriesArea: function() {
        if(typeof(this.selectors.categoriesArea) != 'undefined' && $(this.selectors.categoriesArea)) {
            switch($(this.selectors.categoriesArea).value) {
                case '1':
                    $(this.selectors.categoriesGrid).up().up().hide();
                    break;
                case '2':
                    $(this.selectors.categoriesGrid).up().up().show();
                    break;
            }
        }
    }
});

new AWAutorelatedCategoryForm('aw_category_block_form');
;
