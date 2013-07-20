$j = jQuery.noConflict();

Event.observe(window, "load", function () {

    //load window
	if ( $("quickOrderLink") )
	{
		Event.observe($("quickOrderLink"), "click", function () {
		
			$$("body")[0].insert("<div id='qOrderBg'></div>", { position: "top" });
			//$$("body")[0].insert("<div id='ajax-preloader'>Artikel wird geladen ...</div>", { position: "top" });
			//console.log($('loading'));
			$('loading').setStyle({display:'block'});
			

			$('qOrderBg').observe("click", function () {
				qOrder.removeWindow();
			});
			
			

			new Ajax.Request ('/quickorder/index/showForm/',
			{
				onSuccess: function ( response ) {
					//$('qLoaderBg').remove();
					$('loading').hide();
					$$("body")[0].insert({ bottom: response.responseText});
					$('qClose').observe("click", function () {
						qOrder.removeWindow();
					});

					//bind search action from articul field
					$('qSearchByArticul').observe("keydown", function (e) {
						var element = this;
						if ( e.keyCode == 8 ) qOrder.removeChooseList();
						if ( e.keyCode != 13 ) return false;
						if ( element.value.replace(/\s+/, "").length == 0  ) return false;
						qOrder.sendSearchRequest(element.value);
					});

					$('qStartSearch').observe("click", function () {
						var inputText = $('qSearchByArticul');
						if ( inputText.value.replace(/\s+/, "").length == 0  ) return false;
						qOrder.sendSearchRequest(inputText.value);
					})
					
				}
			});
		});
		pageTracker._trackEvent('Bestellschein', 'Aufruf', 'Klick auf Bestellschein');
	}
});

var qOrder = {};
qOrder.tr   = false;
qOrder.data = false;
qOrder.q    = false;
qOrder.onSubmit = function () {
	$('loading').setStyle({display:'block'});
	$('qOrderWindow').setStyle({display:'none'});
	$('qForm').submit();
	pageTracker._trackEvent('Bestellschein', 'Warenkorb', 'Klick in den Warenkorb');
};
qOrder.sendSearchRequest = function (value)
{
    var self = this;
    self.q  = value;
    qOrder.indicatorShow();
	
	var div = document.createElement("div");
	div.innerHTML = value;
	var value = div.textContent || div.innerText || "";
	
    new Ajax.Request ('/quickorder/index/searchBySku/',
    {
        parameters : {q : value},
        onSuccess: function ( response ) {
			qOrder.removeChooseList();
            qOrder.indicatorHide();
            var jsonObject = response.responseJSON;
            self.data = jsonObject;
            var dataLength = jsonObject.length;
            if ( dataLength == 1 )
            {
                self.chooseItem(self.data[0]);
            }
            else if ( dataLength > 0 )
            {
                self.showChooseForm();
            }
            else
            {
                self.showNotice("Artikel mit der Artikelnummer \""+value+"\" wurde nicht gefunden");
            }
        }
    });
    pageTracker._trackEvent('Bestellschein', 'Suche', 'Klick auf Suchen im Bestellschein');
};
qOrder.createNewRow = function () {
  var self = this;
};
qOrder.showChooseForm = function () {
    var self = this;
    //position
    self.removeChooseList();
    var chooseList = new Element("div", {className : 'qChooseList'});
    var chooseTable = new Element("table", {className: 'qChooseTable'});
	chooseTable.className = 'qChooseTable';
	chooseList.className = 'qChooseList';
	chooseList.insert( { bottom: chooseTable} );

	
	var firstHtml = '<td colspan="2" style="padding-top:8px"><ul style="display: block; " class="messages">'+           
          '<li class="notice-msg">Mehrere Artikel gefunden. Bitte geben Sie eine komplette Katalognummer ein oder wahlen Sie den passenden Artikel aus der Liste aus.</li>  '+              
        '</ul></td>';
		
	var newRowDiv         = new Element("tr", {'style':'border-bottom: 1px solid #CCC; '});
	newRowDiv.insert( { bottom: firstHtml} );
	chooseTable.insert( { bottom: newRowDiv} );
	
	
	
    self.data.each(function (itemData) {
        var newRowDiv         = new Element("tr", {className : 'qChooseRow', 'rel' : itemData.id});
		newRowDiv.className = 'qChooseRow';
        /*newRowDiv.innerHTML  += '<div class="qChooseRowImage"><img src="'+itemData.image+'" alt="" /></div>';
        newRowDiv.innerHTML  += '<div class="qChooseRowTitle">'+itemData.name+'</div>';
        newRowDiv.innerHTML  += '<input type="hidden" class="qRowData" value="'+Object.toQueryString(itemData)+'">';
        newRowDiv.innerHTML  += '<br clear="all" />'; */
		//console.log(itemData.chooseHtml);
		newRowDiv.insert({bottom:itemData.chooseHtml});
		var qString = Object.toQueryString(itemData);
        newRowDiv.select('td')[0].insert( { bottom: '<input type="hidden" class="qRowData" value="'+qString+'">'} );
        chooseTable.insert( { bottom: newRowDiv} );
    });

    var elem = $('qSearchByArticul');
    elem.insert( { after: chooseList} );
	$j('body').toggleClass('ieFix');
    self.chooseRowClickObserve();
};
qOrder.chooseItem = function (data) {
    var self = this;
    self.removeChooseList();
 
    $('qSearchByArticul').value = '';
	var classItem = $('qTable').select("tbody tr").length % 2 == 0 ? 'even' : '';
	//qty-cart698
    var newTR = data.html;
	
	var dataElements = '<input type="hidden" name="products[]" value="'+data.id+'">';
	self.showNotice('Artikel "'+data.sku+'" wurde zur Bestellliste hinzugefugt. <br /><br />Sie können weitere Artikel dieser Liste hinzufügen und anschließend alle Artikel im Warenkorb ablegen!');
	
	if ( $$("#qty-cart"+data.id).length > 0 )
	{
		var element = $("qty-cart"+data.id);
		++element.value;
		return false;
	}
	
	//dataElements += '';
	//newTR.select('td')[0].insert({"bottom" : dataElements});
	
   // var newTR = new Element("tr");
	/* '+data.super_attribute+' */
   /** newTR.innerHTML += '<td>'+data.sku+' <input type="hidden" name="products[]" value="'+data.id+'"></td>';
    newTR.innerHTML += '<td>' +
            '<div class="qMImage"><img src="'+data.image+'" alt="" /></div>' +
            '<div class="qMInfo">'+data.name+'</div>' +
        '</td>';
    newTR.innerHTML += '<td><div class="qRemovePosition">Remove</div></td>';
    newTR.innerHTML += '<td>'+data.status+'</td>';
    newTR.innerHTML += '<td>'+data.htmlQuantity+'</td>';
    newTR.innerHTML += '<td>'+data.price+'</td>'; */

    var k = $('qTable').select("tbody tr")[0].insert({"after" : newTR});
	
	var tr = $('qTable').select("tbody tr")[1];
	tr.setStyle({'opacity': 0});
		
	$j(tr).animate({
		'opacity' : 1
	}, 600);
	
	tr.setAttribute('class',classItem );
	
	var lager = tr.select('.lager');
	
	$j(lager).tinyTips('', 'title');
	
    $('qTable').show();
    $('qToCartWrap').setStyle({display:'block'});
    $('qOrderNote').setStyle({display:'block'});
    self.bindRemoveEvent('.qRemovePosition', data.sku);
};


qOrder.removeChooseList = function () {
    if ( $$('.qChooseList').length == 0 ) return false;
    $$('.qChooseList').each(function (element) {
        element.remove();
    });
};


qOrder.chooseRowClickObserve = function ()
{
    var self = this;
    $$('.qChooseRow').each(function (element) {
        element.observe("click", function () {
            var data = element.down('.qRowData').value;
            self.chooseItem(data.parseQuery());
        });
    });
};
qOrder.bindRemoveEvent = function(element, sku)
{
    $$(element).each(function (el) {
        var tr = el.up("tr");
        el.observe("click", function () {
            try
            {
				qOrder.showNotice('Artikel '+sku+' wurde entfernt.');
				$j(tr).animate({
					'opacity' : 0
				}, 600, function () {
					$j(tr).remove();
					if ( $('qTable').select("tbody tr").length == 1 )
					{
						$('qTable').hide();
						$('qToCartWrap').hide();
						$('qOrderNote').hide();
					}
				})
                
            } catch (e) {
				
			}
        })
    })
};

qOrder.indicatorShow = function ()
{
    $('qLoader').setStyle({display:'block'});
};
qOrder.indicatorHide = function ()
{
    $('qLoader').hide();
};

qOrder.removeWindow = function () {
	if ( $('qTable').select("tbody tr").length > 1 )
	{
		if ( confirm("Möchten Sie die Bestellliste schließen ohne die Artikel in den Warenkorb abzulegen? Ihre Auswahl geht dabei verloren.") )
		{
			 $("qOrderWindow").remove();
			 $("qOrderBg").remove();
		}
	}
	else
	{
		 $("qOrderWindow").remove();
		$("qOrderBg").remove();
	}
};

qOrder.showNotice = function (message)
{
	$("qMessage").setStyle({display:'block'});
	$("qMessage").select("li")[0].innerHTML = message;
}


if(typeof del == 'function') {
    function del (feld)
    {
       var zahl = document.getElementById(feld).value;
       if(zahl <= 0){
         document.getElementById(feld).value = 0;
       }
       else{
         zahl = document.getElementById(feld).value;
         zahl--;
          if(zahl == 0){
            document.getElementById(feld).value = 0;
          }
          else{
            document.getElementById(feld).value = zahl;
          }
       }
    }
}

if(typeof add == 'function') {
    function add (feld)
    {
       var zahl = document.getElementById(feld).value;
       if(zahl < 12){
           zahl++;
         document.getElementById(feld).value = zahl;
       }else{
        document.getElementById(feld).value = 12;
       }
    }
}
