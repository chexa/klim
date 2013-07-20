document.observe('dom:loaded', function() {
    var removeButton = $$('#viewed_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
    if(typeof removeButton != 'undefined')
        removeButton.hide();
    
    removeButton = $$('#related_conditions_fieldset>span.rule-param>a.rule-param-remove').first();
    if(typeof removeButton != 'undefined')
        removeButton.hide();
});;
