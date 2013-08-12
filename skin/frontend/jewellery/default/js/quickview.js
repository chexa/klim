$j = jQuery.noConflict();
var ProductInfo = Class.create();
ProductInfo.prototype = {
    settings: {
        'loadingMessage': 'Artikel wird geladen ...',
        'products' : {
            1: 101,
            2: 102,
            3: 103,
            4: 104,
            5: 105
        },
        'currentProduct' : 2,
        'initElements' : true
    },

    hasArrows : function ()
    {
        return Object.keys(this.settings.products).length > 0;
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
        this.createWindow();

        var that = this;
        var products = {};
        if (this.settings.initElements) {
            $$(selector).each(function(el, index){
                el.observe('click', that.loadInfo.bind(that));
                el.observe('click', function () {
                    that.setCurrentProductNum(index);
                });
                products[index] = el.href;
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
		//console.log(el);
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
        //if (this.hasNextStr()) {
            qWindow.innerHTML += '<div class="qvStrNext qvPrevNext"></div>';
        //}

        //if (this.hasPrevStr()) {
            qWindow.innerHTML += '<div class="qvStrPrev qvPrevNext"></div>';
        //}

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
            prodNum = this.settings.products[this.getCurrentProductNum() + 1] ? (this.getCurrentProductNum() + 1) : 1;
        } else {
            prodNum = this.settings.products[this.getCurrentProductNum() - 1] ? (this.getCurrentProductNum() - 1) : Object.keys(this.settings.products).length;
        }

        //this.setCurrentProductNum(prodNum);

        new Ajax.Request(this.settings.products[prodNum], {
            onSuccess: function(response) {
                $('quick-window').remove();
                var newProd = new ProductInfo('', '', {
                    initElements : false,
                    products : that.getProducts(),
                    currentProduct : prodNum
                });
                newProd.hideLoading();
                newProd.fillContent(response.responseText, 'aa');
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
        /*$('quick-window').setStyle({
            top:  document.viewport.getScrollOffsets().top + 50 + 'px',
            left:  document.body.clientWidth/2 - $('quick-window').getWidth()/2 + 'px',
            display: 'block'
        });*/
        var elem = $j('.quick-view-content:first');
        var elemWrap = $j('#quick-window:first');

        var bScroll = $j(window).scrollTop() + 30;
       // console.log(bScroll);
        
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
		$j('.quick-view-content img').load(function () {
		iterator++;
			if ( $j('.quick-view-content img').length  == iterator ) 
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
							$j('#qZoomer a').imageZoom({speed:800});
				})
				
			}
		
			
		})
			
	/*	 $j('.quick-view-content .ad-gallery').adGallery({
			effect: 'fade',
			thumb_opacity: 0.5,
			display_next_and_prev: false,
			callbacks: {
						afterImageVisible: function(){

                                $j(".quick-view-content .ad-image a").imageZoom({speed:800});
                                new ProductInfo('.quick-view-content .ajax', '.quick-view-content .product-image', {});
                                $j('.quick-view-content .ad-image-main').append('<div id="zmImage" class="zoom-message">Klick, um Bild zu vergrößern</div>');
									
								
							/*	$j(".quick-view-content .ad-image a").magnify({
									hideEvent: 'mouseout',
									showEvent:'mouseover',
									lensWidth: 100,
									lensHeight: 80,
									stageCss: { width: '488px', height: '290px', border: '1px solid #9a9a9a'},
									
									onAfterShow: function(){
											$j(".mousetrap").bind('mouseover',function(){
												$j('#zmImage').fadeIn(500);
											});
									},
									onAfterHide: function(){$j('.quick-view-content #stage').hide(); $j('#zmImage').hide();}
							   
							   }); */
					/*		$j(".mousetrap").live('click',function(){
									$j(".quick-view-content .ad-image a").trigger('click');
								});
							
							$j(".quick-view-content .ad-image a").imageZoom({speed:800});

							
					   }
					}
		 }); */

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
    }
}

Event.observe(window, 'load', function() {
    new ProductInfo('.ajax', '.product-image', {
    });
});