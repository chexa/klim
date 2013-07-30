$j = jQuery.noConflict();


function qOrder() {
    this.tr = false;
    this.data = false;
    this.q = false;
    this.mainElement = false;
    this.closeWindowOnSubmit = true;
    this.getEl = function (elClass, selectAll) {
        if (selectAll !== true) {
            return this.getMainElement().select(elClass)[0];
        }

        return this.getMainElement().select(elClass);
    };
    this.setMainElement = function (el) {
        this.mainElement = el;
    };
    this.getMainElement = function () {
        return this.mainElement;
    };

    this.setCloseWindowOnSubmit = function (el) {
        this.closeWindowOnSubmit = el;
    };
    this.getCloseWindowOnSubmit = function () {
        return this.closeWindowOnSubmit;
    };
    this.onSubmit = function () {
        $('loading').setStyle({display: 'block'});
        if (this.getCloseWindowOnSubmit() && this.getEl('.qOrderWindow')) {
            this.getEl('.qOrderWindow').setStyle({display: 'none'});
        }

        var queryString = '';
        this.getEl('.product-qty', true).each(function (element) {
            var id = element.getAttribute('rel');
            var qty = element.value;
            if (queryString == '') {
			queryString += '?';
		} else {
			queryString += '&';
		}
	    queryString += 'qty[' + id + ']=' + qty;
        })

	$$("body")[0].insert("<form id='qFormSubmit' method='post' action='" + this.getEl('.qForm').getAttribute('action') + queryString + "'></form>", { position: 		"top" });
	$('qFormSubmit').submit();

       // this.getEl('.qForm').submit();
        pageTracker._trackEvent('Bestellschein', 'Warenkorb', 'Klick in den Warenkorb');
    };
    this.sendSearchRequest = function (value) {
        var self = this;
        self.q = value;
        this.indicatorShow();

        var div = document.createElement("div");
        div.innerHTML = value;
        var value = div.textContent || div.innerText || "";

        new Ajax.Request('/quickorder/index/searchBySku/',
            {
                parameters: {q: value},
                onSuccess: function (response) {
                    self.removeChooseList();
                    self.indicatorHide();
                    var jsonObject = response.responseJSON;
                    self.data = jsonObject;
                    var dataLength = jsonObject.length;

                    if (dataLength == 1) {
                        self.chooseItem(self.data[0]);
                    }
                    else if (dataLength > 0) {
                        self.showChooseForm();
                    }
                    else {
                        self.showNotice("Artikel mit der Artikelnummer \"" + value.replace('<', '') + "\" wurde nicht gefunden");
                    }

                    self.getEl('.qToCartWrap', true).each(function (element) {
                        element.observe("click", function () {
                            self.onSubmit();
                        });
                    });
                }
            });
        pageTracker._trackEvent('Bestellschein', 'Suche', 'Klick auf Suchen im Bestellschein');
    };
    this.createNewRow = function () {
        var self = this;
    };
    this.showChooseForm = function () {
        var self = this;
        //position
        self.removeChooseList();
        var chooseList = new Element("div", {className: 'qChooseList'});
        var chooseTable = new Element("table", {className: 'qChooseTable'});
        chooseTable.className = 'qChooseTable';
        chooseList.className = 'qChooseList';
        chooseList.insert({ bottom: chooseTable});


        var firstHtml = '<td colspan="2" style="padding-top:8px"><ul style="display: block; " class="messages">' +
            '<li class="notice-msg">Mehrere Artikel gefunden. Bitte geben Sie eine komplette Katalognummer ein oder wahlen Sie den passenden Artikel aus der Liste aus.</li>  ' +
            '</ul></td>';

        var newRowDiv = new Element("tr", {'style': 'border-bottom: 1px solid #CCC; '});
        newRowDiv.insert({ bottom: firstHtml});
        chooseTable.insert({ bottom: newRowDiv});


        self.data.each(function (itemData) {
            var newRowDiv = new Element("tr", {className: 'qChooseRow', 'rel': itemData.id});
            newRowDiv.className = 'qChooseRow';
            /*newRowDiv.innerHTML  += '<div class="qChooseRowImage"><img src="'+itemData.image+'" alt="" /></div>';
             newRowDiv.innerHTML  += '<div class="qChooseRowTitle">'+itemData.name+'</div>';
             newRowDiv.innerHTML  += '<input type="hidden" class="qRowData" value="'+Object.toQueryString(itemData)+'">';
             newRowDiv.innerHTML  += '<br clear="all" />'; */
            //console.log(itemData.chooseHtml);
            newRowDiv.insert({bottom: itemData.chooseHtml});
            var qString = Object.toQueryString(itemData);
            newRowDiv.select('td')[0].insert({ bottom: '<input type="hidden" class="qRowData" value="' + qString + '">'});
            chooseTable.insert({ bottom: newRowDiv});
        });

        var elem = this.getEl('.qSearchByArticul');
        elem.insert({ after: chooseList});
        $j('body').toggleClass('ieFix');
        self.chooseRowClickObserve();
    };
    this.chooseItem = function (data) {
        var self = this;
        self.removeChooseList();

        self.getEl('.qSearchByArticul').value = '';
        var classItem = self.getEl('.qTable').select("tbody tr").length % 2 == 0 ? 'even' : '';
        //qty-cart698
        var newTR = data.html;

        var dataElements = '<input type="hidden" name="products[]" value="' + data.id + '">';
        self.showNotice('Artikel "' + data.sku + '" wurde zur Bestellliste hinzugefugt. <br /><br />Sie können weitere Artikel dieser Liste hinzufügen und anschließend alle Artikel im Warenkorb ablegen!');

        if (self.getEl(".qty-cart" + data.id, true).length > 0) {
            var element = self.getEl(".qty-cart" + data.id);
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

        var k = this.getEl('.qTable').select("tbody tr")[0].insert({"after": newTR});

        var tr = this.getEl('.qTable').select("tbody tr")[1];
        tr.setStyle({'opacity': 0});

        $j(tr).animate({
            'opacity': 1
        }, 600);

        tr.setAttribute('class', classItem);

        var lager = tr.select('.lager');

        $j(lager).tinyTips('', 'title');

        this.getEl('.qTable').show();
        this.getEl('.qToCartWrap').setStyle({display: 'block'});
        this.getEl('.qOrderNote').setStyle({display: 'block'});
        self.bindRemoveEvent('.qRemovePosition', data.sku);
    };
    this.removeChooseList = function () {
        if (this.getEl('.qChooseList', true).length == 0) return false;
        this.getEl('.qChooseList', true).each(function (element) {
            element.remove();
        });
    };
    this.chooseRowClickObserve = function () {
        var self = this;
        this.getEl('.qChooseRow', true).each(function (element) {
            element.observe("click", function () {
                var data = element.down('.qRowData').value;
                self.chooseItem(data.parseQuery());
            });
        });
    };
    this.bindRemoveEvent = function (element, sku) {
        var self = this;
        this.getEl(element, true).each(function (el) {
            var tr = el.up("tr");
            el.observe("click", function () {
                try {
                    self.showNotice('Artikel ' + sku + ' wurde entfernt.');
                    $j(tr).animate({
                        'opacity': 0
                    }, 600, function () {
                        $j(tr).remove();
                        if (self.getEl('.qTable').select("tbody tr").length == 1) {
                            self.getEl('.qTable').hide();
                            self.getEl('.qToCartWrap').hide();
                            self.getEl('.qOrderNote').hide();
                        }
                    })

                } catch (e) {

                }
            });
        })
    };
    this.indicatorShow = function () {
        this.getEl('.qLoader').setStyle({display: 'block'});
    };
    this.indicatorHide = function () {
        this.getEl('.qLoader').hide();
    };

    this.removeWindow = function () {
        if (this.getEl('.qTable').select("tbody tr").length > 1) {
            if (confirm("Möchten Sie die Bestellliste schließen ohne die Artikel in den Warenkorb abzulegen? Ihre Auswahl geht dabei verloren.")) {
                this.getEl(".qOrderWindow").remove();
                $("qOrderBg").remove();
                this.getMainElement().remove();
            }
        }
        else {
            this.getEl(".qOrderWindow").remove();
            $("qOrderBg").remove();
            this.getMainElement().remove();
        }
    };

    this.showNotice = function (message) {
        this.getEl(".qMessage").setStyle({display: 'block'});
        this.getEl(".qMessage").select("li")[0].innerHTML = message;
    };

}
;

function initQuickOrder() {
    this.obj = null;
    this.setCloseWindowOnSubmit = function (el) {
        this.getObj().setCloseWindowOnSubmit(el);
    };
    this.getObj = function () {
      return this.obj;
    };
    this.setObj = function (o) {
       this.obj = o;
    };
    this.initActions = function (el) {
        //bind search action from articul field
        var self = this;
        var orderObject = new qOrder();
        this.setObj(orderObject);
        this.getObj().setMainElement(el);
        el.select('.qSearchByArticul').each(function (element) {
            element.observe("keydown", function (e) {
                var element = this;
                if (e.keyCode == 8) self.getObj().removeChooseList();
                if (e.keyCode != 13) return false;
                if (element.value.replace(/\s+/, "").length == 0) return false;
                self.getObj().sendSearchRequest(element.value);
            });
        });

        el.select('.qStartSearch').each(function (element) {
            element.observe("click", function () {
                var inputText = el.select('.qSearchByArticul')[0];
                if (inputText.value.replace(/\s+/, "").length == 0) return false;
                self.getObj().sendSearchRequest(inputText.value);
            })
        })

    }
}

    function addToCartM(el) {
        var zahl = el.next().value;
        if (zahl <= 0) {
            el.next().value = 0;
        }
        else {
            zahl = el.next().value;
            zahl--;
            if (zahl == 0) {
                el.next().value = 0;
            }
            else {
                el.next().value = zahl;
            }
        }
    }

    function addToCartP(el) {
        var zahl = $j(el).prev()[0].value;
        if (zahl < 12) {
            zahl++;
            $j(el).prev()[0].value = zahl;
        } else {
            $j(el).prev()[0].value = 12;
        }
    }

    if (typeof del == 'function') {
        function del(feld) {
            var zahl = document.getElementById(feld).value;
            if (zahl <= 0) {
                document.getElementById(feld).value = 0;
            }
            else {
                zahl = document.getElementById(feld).value;
                zahl--;
                if (zahl == 0) {
                    document.getElementById(feld).value = 0;
                }
                else {
                    document.getElementById(feld).value = zahl;
                }
            }
        }
    }

    if (typeof add == 'function') {
        function add(feld) {
            var zahl = document.getElementById(feld).value;
            if (zahl < 12) {
                zahl++;
                document.getElementById(feld).value = zahl;
            } else {
                document.getElementById(feld).value = 12;
            }
        }
    }


    Event.observe(window, "load", function () {

        //load window
        if ($("quickOrderLink")) {
            Event.observe($("quickOrderLink"), "click", function () {

                $$("body")[0].insert("<div id='qOrderBg'></div>", { position: "top" });
                //$$("body")[0].insert("<div id='ajax-preloader'>Artikel wird geladen ...</div>", { position: "top" });
                //console.log($('loading'));
                $('loading').setStyle({display: 'block'});



                new Ajax.Request('/quickorder/index/showForm/',
                    {
                        onSuccess: function (response) {
                            //$('qLoaderBg').remove();
                            $('loading').hide();
                            $$("body")[0].insert({ bottom: '<div id="quickOrderHeader">' + response.responseText + '</div>'});

                            var obj = new qOrder();
                            obj.setMainElement($('quickOrderHeader'));

                            $('qOrderBg').observe("click", function () {
                                obj.removeWindow();
                            });

                            $$('.qClose').each(function (element) {
                                element.observe("click", function (el) {
                                    obj.removeWindow();
                                });
                            });

                            var initQo = new initQuickOrder();
                            initQo.initActions($('quickOrderHeader'));

                        }
                    });
            });
            pageTracker._trackEvent('Bestellschein', 'Aufruf', 'Klick auf Bestellschein');
        }

        if ($('cartQuickOrder')) {
            var initQo2 = new initQuickOrder();
            initQo2.initActions($('cartQuickOrder'));
            initQo2.setCloseWindowOnSubmit(false);

        }
    });
