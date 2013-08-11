$j = jQuery.noConflict();
var cartIsChanged = false;

//QTY Plus Minus
function add(feld){
   zahl = document.getElementById(feld).value;
   if(zahl <= 12){ 
	   zahl++;
     document.getElementById(feld).value = zahl;	
	 $j('#'+feld).change();
   }else{
    document.getElementById(feld).value = 12;
   }
}

function handleChangeQty (el) {
	if (cartIsChanged === false) {
		if ($j(el).closest('.cart-table').size() > 0) {
			cartIsChanged = true;
		}
	}
}

function del(feld){
   zahl = document.getElementById(feld).value;
   if(zahl <= 0){
     document.getElementById(feld).value = 0;
	 $j('#'+feld).change();
   }
   else{
     zahl = document.getElementById(feld).value;
     zahl--;
	  if(zahl == 0){
	    document.getElementById(feld).value = 0;
	  }	
	  else{
        document.getElementById(feld).value = zahl;
		$j('#'+feld).change();
	  }
   }
}

function checkGoogleMapsForm() {
  var strFehler='';
     
  if (document.forms[0].q.value=="")
    strFehler += "Bitte geben Sie ein Ort oder Postleitzahl ein\n";
  if (strFehler.length>0) {
    alert(strFehler);    
    return(false);
  }else {
    document.forms[0].q.value = document.forms[0].q.value+' Schmuck -nagel -tattoo -accessoires -piercing';
    document.googlemaps.action = 'http://google.de/maps'; 
  }
   
}
function initSearch() {
  //$j('#search').value='Suchwort';
  //document.forms[0].search.value = 'Produktsuche';
  //if (){
	//document.getElementById("search").value = 'Produktsuche';
	//document.write("<p>Suchfeld gefunden</p>");
  //}else {
	//document.write("<p>Kein Suchfeld gefunden</p>");
  //}
}

function initMenu() {
  /*Init link-prev Linkt back to Product Overview*/
  $j('.link-prev').html('Zurück zur Übersicht');

  
  $j('#left-nav li').has('ul').append('<span class="nav-indicator"></span>');
	
	$j('#left-nav li span.nav-indicator').click(
		function() {
		
		var checkElement = $j(this).parent().find('ul');
		if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
			checkElement.slideUp('normal', function(){$j(this).parent().removeClass('submenu-act');});
			
			
			$j(this).removeClass('nav-indicator-open');
			
			return false;
		}
		if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
			checkElement.slideDown('normal');
			
			
			$j(this).addClass('nav-indicator-open');
			return false;
		}
		}
	);
}
function setMassegCookie() {
  jetzt=new Date();
  //Auszeit=new Date(jetzt.getTime()+Dauer*86400000);
  document.cookie="readingRetailerMassage=1; path=/"
}
 
function initMessages() {
  $j('.messages').addClass("popup");
  $j('.order-info').removeClass("popup");
  $j('.cms-enable-cookies .messages').removeClass("popup");  
  $j('.catalogsearch-result-index .messages').removeClass("popup");
  $j('.checkout-cart-index .messages').removeClass("popup"); 
  //$j('.popup').prepend('<a id="popup-close" href="javascript:void(0)">close</a>');
  $j('.popup').append('<a id="popup-ok-button" onclick="setMassegCookie();" class="popup-ok-button button-link" href="javascript:void(0)" >OK</a>');
                                               
  
  $j('.popup').css({
		//top:  document.viewport.getScrollOffsets().top + 100 + 'px',
    //left:  $j(window).width()/2 - 250 + 'px',
    top:		$j(window).height()/5 + 'px',
    display: 'block'
	}).fadeIn(400);
 
  //Retailer Massage Popup
  var massageCockie = document.cookie
  // Wenn massageCockie nicht undefiniert ist und "readingRetailerMassage"
  // enthält, wurde der Cookie erfolgreich gesetzt:
  if (massageCockie && massageCockie.indexOf("readingRetailerMassage") < 0) {
    $j('.retailermassage').addClass("retailerpopup");
    //Delite Massage from follow pages 
    $j('.retailermassage').addClass("popup");
    $j('.core-index-index .retailermassage').removeClass("retailerpopup");  
    $j('.core-index-index .retailermassage').removeClass("popup");
    $j('.customer-account-logoutsuccess .retailermassage').removeClass("retailerpopup");  
    $j('.customer-account-logoutsuccess .retailermassage').removeClass("popup");  
       
    $j('.retailerpopup').append('<a id="popup-ok-button" class="popup-ok-button button-link" href="javascript:void(0)" onclick="setMassegCookie();" >Hinweis schließen</a>');
    $j('.retailerpopup').css({                                                                                                    
  		//top:  document.viewport.getScrollOffsets().top + 100 + 'px',
      //left:  $j(window).width()/2 - 250 + 'px',
      top:		$j(window).height()/3 + 'px',
      display: 'block'
  	}).fadeIn(400);
    return true;
  }   
}


//$j.getScript('/skin/frontend/jewellery/default/js/jquery.blockUI.js', function(){
		
		
//		$j.blockUI({message:'Seite wird geladen...',css:{background:'#fff',border:'5px solid #e0e0e0',padding:'3px'},overlayCSS:{background:'#000',opacity:.5}});
//				$j(window).load(function(){
//				$j.unblockUI();
				 
//				});

		//$j(document).ajaxStart($j.blockUI).ajaxStop($j.unblockUI);

//});

function showLoadWindow ()
{
	$j('#wait-overlay').css({
									width:		$j(window).width(),
									height:		$j(document).height(),
									opacity:  0.6
								}).fadeIn(400);
	$j('#loading').show();
}



function hideLoadWindow ()
{
	$j('#wait-overlay').hide();
	$j('#loading').hide();
}

	var changeQty = false;
	

	
	$j(window).load(function() {

        $j('#search_mini_form').submit(function () {
            var val = $j(this).find('.input-text input').val();
            if (val.length > 0 && val != 'Produktsuche') {
                showLoadWindow();
            }
        });

		$j('#update_cart_form').submit(function () {
			changeQty = false;
			showLoadWindow();
		})
	
	$j('.checkout-btn input').click(function () {
		window.onbeforeunload = null;
		changeQty = false;
		showLoadWindow();
	})

	$j('.checkout-btn').click(function () {
		window.onbeforeunload = null;
		changeQty = false;
		showLoadWindow();
	})
	
	$j('#update_cart_form').submit(function () {
		window.onbeforeunload = null;
		changeQty = false;
	})
	
	$j('input[name="estimate_method"]').change(function () {
		window.onbeforeunload = null;
		changeQty = false;
	})
	
	$j('.update-cart').attr('onclick', 'changeQty = false; '+ $j('.update-cart').attr('onclick'));
	$j('.checkout-btn input').attr('onclick', 'changeQty = false; '+ $j('.checkout-btn input').attr('onclick'));
	
	$j('.update-cart').click(function () {
		window.onbeforeunload = null;
		changeQty = false;
	})
	
	$j('.product-qty').live('change', function () {
		changeQty = true;
	})
	
	if ( $j('#update_cart_form').size() > 0 )
	{
		
		window.onbeforeunload = function () {
			 if ( changeQty === true )
			 {
				setTimeout(function () { hideLoadWindow() }, 1000);
				return "Damit Ihre Änderungen im Warenkorb übernommen werden, müssen Sie diesen aktualisieren1!";
			 }
			else
			{
				return  ;
			}		
		}
		
		//$j('body').unload( function () { showLoadWindow(); } );
		/*$j(window).unload(function () {
			showLoadWindow();
		}) */
	}
	 
	
		$j('.etalage_small_thumbs li').live('mouseover', function () {
			$j(this).animate({opacity : 1}, 300);
		});
		$j('.etalage_small_thumbs li').live('mouseleave', function () {
			if ( $j(this).is('.etalage_smallthumb_active') ) return false;
			$j(this).animate({opacity : 0.7}, 300);
		})
		
		$j('.cntrZoom div').css('opacity', 0.6);
		
		$j('.cntrZoom div').live('mouseover', function () {
			$j(this).animate({opacity : 1}, 300);
		});
		$j('.cntrZoom div').live('mouseleave', function () {
			$j(this).animate({opacity : 0.6}, 300);
		})
		
				$j('#example3').etalage({
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
						
						$j('#example3 .etalage_thumb_active a').trigger('click');

					} 
				});
				
				function etalage_click_callback(image_anchor){
				}

				$j('.zoom-message').live('click', function () {
					$j(this).next().find('.etalage_thumb_active a').trigger('click');
					//$j('#example3 .etalage_thumb_active a').trigger('click');
				})
				
				$j('#example3').find('img').load(function () {
					$j('#example3 a').imageZoom({speed:800});
				}) 
				
    //feedback lightbox
		$j("#feedback-button").click(function(){
			 $j('#overlay').css({
									width:		$j(window).width(),
									height:		$j(document).height(),
									opacity:  0.6
								}).fadeIn(400,function() {
					$j('#feedback-box').fadeIn('normal').css('opacity','1');
					});
	  });

    $j('.homepage').click(function(){
    			window.location.replace('/');
    return false;
    });
    $j('.homepage').hover(function(){
      $j('.homepage').replaceWith('<a href="/" title="zur Startseite">Startseite</a>');
    });
	
		$j("#close-btn").click(function(){
			$j('#overlay').fadeOut();
			$j("#feedback-box").hide();
			return false;
	  });
	  
	  //order-info
	  if ($j('.block-related').length > 0) {
		  $j('.block-related').before('<ul class="order-info messages hide"><li class="notice-msg"><ul><li><span>Bitte wählen Sie mindestens einen Artikel aus und legen Sie diesen "in den Warenkorb"</span></li></ul></li></ul>');
    } else {
      $j('.info-block').before('<ul class="order-info messages hide"><li class="notice-msg"><ul><li><span>Bitte wählen Sie mindestens einen Artikel aus und legen Sie diesen "in den Warenkorb"</span></li></ul></li></ul>');
    }
    
    
	  $j(".order-btn").click(function(){
			//$j('#pricetable').fadeOut();
			//$j('#messages').css('display','block');
      $j('.messages').removeClass("hide");   
			
			//$j("#feedback-box").hide();
			window.location.replace('#product_addtocart_form');
			return false;
	  });
	  
	 //rating
		$j('#design-rating li').click(function(){
			rating = $j(this).text();
			$j(this).parent().find("li.current").css("width",rating*20+"px");
			$j('#design-value').val(rating)
		});
		$j('#nutzung-rating li').click(function(){
			rating = $j(this).text();
			$j(this).parent().find("li.current").css("width",rating*20+"px");
			$j('#nutzung-value').val(rating)
		});
		$j('#inhalt-rating li').click(function(){
			rating = $j(this).text();
			$j(this).parent().find("li.current").css("width",rating*20+"px");
			$j('#inhalt-value').val(rating)
		});
		
		if($j.browser.msie && $j.browser.version=="6.0"){
			$j('.input-btn input, li, .user-buttons input, input.button-link, .weiter-btn, .product-table  tr').hover(
			  function(){ 
				$j(this).addClass("hover");
			  }, 
			  function(){ 
				$j(this).removeClass("hover");
			  } 
			  ); 
		}
		
		//initSearch();
		initMenu();
		initMessages();
		
    $j(".popup-ok-button").live('click', function(){
			$j(".popup").hide();
			$j('.messages').removeClass("popup");
			return false;
	  });
			
		$j('#content ul.menu').superfish({
			 delay: 200,
			 speed:  'fast'
		});
		$j('#content ul.keywords').superfish({
			delay: 200,
			animation:   {opacity:'show',height:'show'}
			
		});

		$j('#arrow').click(
		function(){
			$j(this).toggleClass("up-arrow");
			//$j('.toggle-box').toggleClass('open');
			
			ht=$j('.toggle-box').height();
			hti=$j('.toggle-box div').height();
			
			if(ht==55)
			$j('.toggle-box').animate({height: hti},'normal');
			else
			$j('.toggle-box').animate({height: '55px'},'normal');
		});
		
		ht=$j('.toggle-box').height();
		hti=$j('.toggle-box div').height();
		
		$j('.toggle-box p').hover(
		function(){
		  ht=$j('.toggle-box').height();
			hti=$j('.toggle-box div').height();
			
			if(ht==55)
			$j('.toggle-box').animate({height: hti},'normal');
			//else
			//$j('.toggle-box').animate({height: '55px'},'normal');
						
			$j('#arrow').addClass("up-arrow");
			//$j('.toggle-box').animate({height: hti},'normal');
			//},
			//function(){
			//$j('#arrow').toggleClass("up-arrow");
			//$j('.toggle-box').animate({height: '55px'},'normal');
		});
		
    $j('.toggle-box p').click(
		function(){
		  ht=$j('.toggle-box').height();
			hti=$j('.toggle-box div').height();
			
			if(ht==55)
			$j('.toggle-box').animate({height: hti},'normal');
			else
			$j('.toggle-box').animate({height: '55px'},'normal');
						
			$j('#arrow').toggleClass("up-arrow");
			//$j('.toggle-box').animate({height: hti},'normal');
			//},
			//function(){
			//$j('#arrow').toggleClass("up-arrow");
			//$j('.toggle-box').animate({height: '55px'},'normal');
		});
		
		
		if ($j.isFunction($j.fn.checkboxstyle)) {
		    $j('input:checkbox').checkboxstyle();
        }
		if ($j.isFunction($j.fn.radiostyle)) {
            $j('input:radio').radiostyle();
        }
        if ($j.isFunction($j.fn.selectstyle)) {
            	$j('.sort select').selectstyle();
        }

		//$j('input[type=file]').filestyle();
		
		
		$j('.product-table input').focus(function(){
			$j(this).parent().parent().addClass('focus');
      
		});
		$j('.product-table input').blur(function(){
			$j(this).parent().parent().removeClass('focus');
		});
		
		$j('#content div').has('select').css('overflow','visible').addClass('clearfix');
		
		
		//$j('.zoomWrapper').append('<div class="zoom-message">Klick, um Bild zu vergrößern</div>');
		
	
		
// magbify Zoom
//http://www.jnathanson.com/index.cfm?page=jquery/magnify/magnify

    $j(".mousetrap").click(function(){
			$j("#stage").hide();
			return false;
	  });		
		/* $j('.ad-gallery').adGallery({
			effect: 'fade',
			thumb_opacity: 0.5,
			display_next_and_prev: false,
			callbacks: {
						afterImageVisible: function(){
								$j(".ad-image a").magnify({
									hideEvent: 'mouseout',
									showEvent:'mouseover',
									lensWidth: 100, 
									lensHeight: 80,
									stageCss: { width: '488px', height: '290px', border: '1px solid #9a9a9a'},
									
									onAfterShow: function(){
											$j(".mousetrap").bind('mouseover',function(){
												$j('.zoom-message').fadeIn(500);
											});

									},
									onAfterHide: function(){$j('#stage').hide();$j('.zoom-message').hide();}
							   
							   });
							$j(".mousetrap").live('click',function(){
                                    if ( $j("#quick-window:visible").size() > 0 )
                                    {
                                        //$j("#quick-window .ad-image a").trigger('click');
                                        $j("#quick-window .ad-image a").trigger('click');
                                    }
                                     else
                                    {
                                        $j(".ad-image a").trigger('click');
                                    }

								});
							
							$j(".ad-image a").imageZoom({speed:800});
							
					   }
					}
		 }); */
		 
		$j('.lager').tinyTips('', 'title');

		$j('#fax').click(clickFaxCheckbox);
		
		$j('#datei').click(clickFileCheckbox);

		$j('#fax').parent().click(clickFaxCheckbox);
		
		$j('#datei').parent().click(clickFileCheckbox);

		if ($j('.my-account').length > 0) {
		    mein_konto_pos=$j('.my-account').position();
		    $j('.account-dashboard .inner-side').css('top', mein_konto_pos.top);
        }
		
		
		$j('#sub-cat-nav').wrapInner('<div class="holder" />');
		
		$j(window).scroll(function () {
			if ($j(this).scrollTop() > 274) {
				$j('#sub-cat-nav').addClass('fixed-toolbar');
			} else {
				$j('#sub-cat-nav').removeClass('fixed-toolbar');
			}
		});

		
		$j('body').append('<div id="wait-overlay"></div>');
		$j('body').append('<div id="loading">Ihre Daten werden verarbeitet...</div>');
		$j('#wait-overlay, #loading').hide();

        initDeletClickInCart();
		
 });
 
function initDeletClickInCart() {
    $j('.delete-link').click(function(){
        if (  confirm('Der Artikel wird aus dem Warenkorb entfernen!') ) {
            window.onbeforeunload = null;
            $j('#wait-overlay').css({
                width:		$j(window).width(),
                height:		$j(document).height(),
                opacity:  0.6
            }).fadeIn(400);

            $j('#loading').show();
        }
        else
        {
            return false;
        }
    });
}

function clickFaxCheckbox() {
    $j('#als-datei').hide();
    $j('#fax').addClass('checked');
    $j('#datei').removeClass('checked');
}

function clickFileCheckbox() {
    $j('#als-datei').show();
    $j('#datei').addClass('checked');
    $j('#fax').removeClass('checked');
}


	/*$j(function() {
			var zIndexNumber = 10000;
			$j('div.form-style.select').each(function() {
			$j(this).css('zIndex', zIndexNumber);
			zIndexNumber -= 10;
			});
			});

			
			$j(function() {
			var zIndexNumber = 1000;
			$j('.inner-content .block').each(function() {
			$j(this).css('zIndex', zIndexNumber);
			zIndexNumber -= 10;
			});
			});*/


			
			





