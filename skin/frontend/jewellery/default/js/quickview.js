$j = jQuery.noConflict();
var ProductInfo = Class.create();
ProductInfo.prototype = {
    settings: {
        'loadingMessage': 'Artikel wird geladen ...',
        'products' : {},
        'currentProduct' : 1,
        'initElements' : true
    },

	getProductsLength: function ()
	{
		return Object.keys(this.settings.products).length;
	},

    hasArrows : function ()
    {
        return this.getProductsLength() > 1;
    },

    getCurrentProductNum: function ()
    {
        return this.settings.currentProduct;
    },

    setCurrentProductNum: function (num)
    {
        this.settings.currentProduct = num;
    },

    setProducts: function (obj)
    {
        this.settings.products = obj;
    },

    getProducts: function (obj)
    {
        return this.settings.products;
    },

    initialize: function(selector, x_image, settings)
    {
        Object.extend(this.settings, settings);
        var that = this;
        var products = {};
        if (this.settings.initElements) {
            $$(selector).each(function(el, index){
                el.observe('click', that.loadInfo.bind(that));
                el.observe('click', function () {
                    that.setCurrentProductNum(index);
                });
                products[index] = {href: el.href, src: el.up().select('img')[0].src, title: el.up().select('img')[0].alt};
            })
            $$(x_image).each(function(el, index){
                el.observe('mouseover', that.showButton);
                el.observe('mouseout', that.hideButton);
            })
        } else {
            products = this.settings.products;
            this.setCurrentProductNum(this.settings.currentProduct);
        }


        that.setProducts(products);
		that.createWindow();

    },
    
    createLoader: function()
    {
        var loader = new Element('div', {id: 'ajax-preloader'});
        loader.innerHTML = "<p class='loading'>"+this.settings.loadingMessage+"</p>";
        document.body.appendChild(loader);
        $('ajax-preloader').setStyle({
            position: 'absolute',
            top:  document.viewport.getScrollOffsets().top + 200 + 'px',
            left:  document.body.clientWidth/2 - 75 + 'px'
        }); 
		$j('#wait-overlay').css('filter', 'alpha(opacity=60)');
        $j('#wait-overlay').css({
									width:		$j(window).width(),
									height:		$j(document).height(),
									opacity:  0.6
								}).fadeIn(400); 
				$('wait-overlay').observe('click', this.hideWindow.bind(this));
    },
    
    destroyLoader: function()
    {
        if ( $('ajax-preloader')) {
            $('ajax-preloader').remove();
        }
    },
    
    showButton: function(e)
    {
        el = this;
        while (el.tagName != 'P' && el.up) { 
		    el = el.up();
        }
		if ( $(el).getElementsBySelector )
		{
			$(el).getElementsBySelector('.ajax')[0].setStyle({
				display: 'block'
			})
		}
    },
    
    hideButton: function(e)
    {
       el = this;
        while (el.tagName != 'P' && el.up) {
            el = el.up();
        }
		if ( $(el).getElementsBySelector )
		{
			$(el).getElementsBySelector('.ajax')[0].setStyle({
				display: 'none'
			})
		}
    },
    
    createWindow: function()
    {
        var that = this;
        var className = 'quick-window-elem' + (this.hasArrows() ? ' quick-window-elem-arrows' : '');
        var qWindow = new Element('div', {id: 'quick-window', 'class': className});
        qWindow.innerHTML = '<div id="quickview-header"><a href="javascript:void(0)" id="quickview-close">close</a></div><div class="quick-view-content"></div>';
        if (this.hasArrows()) {
            qWindow.innerHTML += '<div class="qvStrNext qvPrevNext"></div>';
            qWindow.innerHTML += '<div class="qvStrPrev qvPrevNext"></div>';
        }

        document.body.appendChild(qWindow);
        $('quickview-close').observe('click', this.hideWindow.bind(this));

        $$('.qvPrevNext').each(function (el) {
            el.observe('click', that.bindPrevNext.bind(that))
        })
		try
		{
			$('wait-overlay').observe('click', this.hideWindow.bind(this));         
		} catch (err) {};
        
    },

    bindPrevNext : function (el)
    {
        this.showLoading();
        var that = this;
        var isNext = el.target.className.indexOf('qvStrNext') != -1;
        var prodNum = 1;
        if (isNext) {
            prodNum = this.settings.products[that.getCurrentProductNum() + 1] ? (that.getCurrentProductNum() + 1) : 1;
        } else {
            prodNum = this.settings.products[that.getCurrentProductNum() - 1] ? (that.getCurrentProductNum() - 1) : Object.keys(that.settings.products).length;
        }

        new Ajax.Request(that.settings.products[prodNum].href, {
            onSuccess: function(response) {
                $('quick-window').remove();
                var newProd = new ProductInfo('', '', {
                    initElements : false,
                    products : that.getProducts(),
                    currentProduct : prodNum
                });
                newProd.hideLoading();
                newProd.fillContent(response.responseText, that.settings.products[prodNum].href);
            }
        });
    },

    showLoading: function ()
    {
        var ovl = new Element('div', {'class': 'loadOvl'});
        ovl.setStyle({'filter' : 'alpha(opacity=60)'});
        ovl.setStyle({
            width:		$j(window).width() + 'px',
            height:		$j(document).height() + 'px',
            opacity:  0.6,
            display : 'block'
        });

        document.body.appendChild(ovl);

        $('loading').setStyle({
            display: 'block'
        });
    },

    hideLoading: function ()
    {
        $$('.loadOvl')[0].remove();

        $('loading').setStyle({
            display: 'none'
        });
    },

    showWindow: function()
    {
        var elem = $j('.quick-view-content:first');
        var elemWrap = $j('#quick-window:first');

        var bScroll = $j(window).scrollTop() + 30;
        
        elem.show();
        elemWrap.show();
        elemWrap.css('top','0');
        elemWrap.css('left','50%');
        elemWrap.css('margin-left', - elem.width() / 2);
        elemWrap.css('margin-top', bScroll);
    },
    
    setContent: function(content)
    {
        $$('.quick-view-content')[0].appendChild(content);

        var that = this;
        that.showWindow();
        that.destroyLoader();
		var iterator = 0;
		$j('.quick-view-content .ajaxFORM img').load(function () {
		iterator++;
			if ( $j('.quick-view-content .ajaxFORM img').length  == iterator )
			{
					$j('.quick-view-content .lager').tinyTips('', 'title');
					$j('.quick-view-content .Zoomer').attr('id', 'qZoomer'); 
					$j('#qZoomer').etalage({
						thumb_image_width: 233,
					   thumb_image_height: 233,
						source_image_width: 750,
						source_image_height: 750,
						show_icon: true,
						icon_offset: 20,
						zoom_area_width: 484,
						zoom_area_height: 292,
						zoom_area_distance: 3, 
						small_thumbs: 3,
						smallthumb_inactive_opacity: 0.7,
						smallthumbs_position: 'right',
						autoplay: false,
						keyboard: false,
						zoom_easing: false, 
						show_descriptions: true,
						description_location: "bottom",
						click_callback : function () {
								$j('#qZoomer .etalage_thumb_active a').trigger('click');
							} 
					});
					
						$j('#qZoomer').find('img').load(function () {
							if ($j('#qZoomer a').imageZoom) {
								$j('#qZoomer a').imageZoom({speed:800});
							}

				})
				
			}
		})

    },
    
    clearContent: function()
    {
        $$('.quick-view-content')[0].replace('<div class="quick-view-content"></div>');
    },
    
    hideWindow: function()
    {
        this.clearContent();
        $('quick-window').hide();
        $j('#wait-overlay, #loading').fadeOut();
		$j('.jquery-image-zoom').remove();
    },

	createJS : function (jsFile)
	{
		var newScript = document.createElement('script');
			newScript.type = 'text/javascript';
			newScript.src = '/skin/frontend/jewellery/default/js/'+jsFile;
		return newScript;
	},
	
    loadInfo: function(e)
    {
        e.stop();
		if (typeof event !== 'undefined')
		{
			Event.stop(event);
		}		
		
        var that = this;
        this.createLoader();

        $$(".quick-window-elem").each(function(el, index){
            el.style.display = 'none';
        })

        var el = e.element();
        while (! el.href) {
            el = el.up();
        }

        new Ajax.Request(el.href, {
            onSuccess: function(response) {
                that.fillContent(response.responseText, e.element().href);
            }
        });
    },

    fillContent: function (text, href)
    {
        var that = this;
        var div = document.createElement('div');
        div.innerHTML = text;
        div.className = 'qoWrap';
        // 1 pageTracker._trackPageview('http://www.standard-schmuck.de/zahnstocher');
        //Diese Aufruf liefert die URL für den Quickview
        pageTracker._trackPageview(href);
        that.clearContent();
        that.setContent(div);
		that.drawSlider();
		that.initCarousel();
    },

	drawSlider: function () {
		var html = '';
		html += '<div class="qbSlider"><a class="prev nextPrev qArrows qArrowsL"></a><div class="qbSliderIn">' +
			'<ul>';

		for (var i = 0; i < this.getProductsLength(); i++) {
			html += this.drawSliderItem(this.getProducts()[i]);
		}

		html += '</ul>' +
			'</div><a class="next nextPrev qArrows"></a></div>';

		$$('.quick-view-content')[0].select('.inner-content')[0].insert(html);
	},

	drawSliderItem: function (data)
	{
		var html = '';
		html += '<li><img src="' + data.src + '" alt="' + data.title + '" title="' + data.title + '" /></li>'
		return html;
	},

	initCarousel: function ()
	{
		var that = this;
		$j(".qbSlider .qbSliderIn").jCarouselLite({
			btnNext: ".qbSlider .next",
			btnPrev: ".qbSlider .prev",
			visible: 6,
			start: this.getCurrentProductNum()
		});

		var first = $j(".qbSlider .qbSliderIn");
		$j('.qbSlider li').eq(that.getCurrentProductNum()).addClass('activeItemInView');

		$j('.qbSlider li').click(function () {
			that.showLoading();
			var num = parseInt($j(this).attr('rel'));
			new Ajax.Request(that.settings.products[num].href, {
				onSuccess: function(response) {
					$('quick-window').remove();
					var newProd = new ProductInfo('', '', {
						initElements : false,
						products : that.getProducts(),
						currentProduct : num
					});
					newProd.hideLoading();
					newProd.fillContent(response.responseText, that.settings.products[num].href);
				}
			});
			return false;
		})

	}

}

Event.observe(window, 'load', function() {
    new ProductInfo('.ajax', '.product-image', {
    });
});