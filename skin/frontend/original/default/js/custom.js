	function initMenu() {
	
	
	$('#left-nav li').has('ul').append('<span class="nav-indicator"></span>');
	
	$('#left-nav li span.nav-indicator').click(
		function() {
		
		var checkElement = $(this).parent().find('ul');
		if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
			checkElement.slideUp('normal', function(){$(this).parent().removeClass('submenu-act');});
			
			
			$(this).removeClass('nav-indicator-open');
			
			return false;
		}
		if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
			checkElement.slideDown('normal');
			
			
			$(this).addClass('nav-indicator-open');
			return false;
		}
		}
	);
	

	
	}


	$(document).ready(function() { 
		//feadback lightbox
		$("#feedback-button").click(function(){
			 $('#overlay').css({
									width:		$(window).width(),
									height:		$(document).height(),
									opacity:  0.6
								}).fadeIn(400,function() {
					$('#feedback-box').fadeIn('normal').css('opacity','1');
					});
	  });

		$("#close-btn").click(function(){
			$('#overlay').fadeOut();
			$("#feedback-box").hide();
			return false;
	  });
	  
	 //rating
		$('#design-rating li').click(function(){
			rating = $(this).text();
			$(this).parent().find("li.current").css("width",rating*20+"px");
			$('#design-value').val(rating)
		});
		$('#nutzung-rating li').click(function(){
			rating = $(this).text();
			$(this).parent().find("li.current").css("width",rating*20+"px");
			$('#nutzung-value').val(rating)
		});
		$('#inhalt-rating li').click(function(){
			rating = $(this).text();
			$(this).parent().find("li.current").css("width",rating*20+"px");
			$('#inhalt-value').val(rating)
		});
		
		if($.browser.msie && $.browser.version=="6.0"){
			$('.input-btn input, li, .user-buttons input, input.button-link, .weiter-btn, .product-table  tr').hover( 
			  function(){ 
				$(this).addClass("hover"); 
			  }, 
			  function(){ 
				$(this).removeClass("hover"); 
			  } 
			  ); 
		}
		
		
		initMenu();
			
		$('#content ul.menu').superfish({
			 delay: 200,
			 speed:  'fast'
		});
		$('#content ul.keywords').superfish({
			delay: 200,
			animation:   {opacity:'show',height:'show'}
			
		});
		
		
		$('.breadcrumb-nav a').after('<span>&#62;</span>');
		
		
		

		$('#arrow').click(
		function(){
			$(this).toggleClass("up-arrow");
			//$('.toggle-box').toggleClass('open');
			
			ht=$('.toggle-box').height();
			hti=$('.toggle-box div').height();
			
			if(ht==35)
			$('.toggle-box').animate({height: hti},'normal');
			else
			$('.toggle-box').animate({height: '35px'},'normal');
		});
		
		
		
		$('input:checkbox').checkboxstyle();
		$('input:radio').radiostyle();
		$('.sort select').selectstyle();
		//$('input[type=file]').filestyle();
		
		
		$('.product-table select').focus(function(){
			$(this).parent().parent().addClass('focus');
		});
		$('.product-table select').blur(function(){
			$(this).parent().parent().removeClass('focus');
		});
		
		$('#content div').has('select').css('overflow','visible').addClass('clearfix'); 
		
		
		 $('.ad-gallery').adGallery({
			effect: 'fade',
			thumb_opacity: 0.5,
			display_next_and_prev: false,
			callbacks: {
						afterImageVisible: function(){
								
								$(".ad-image a").magnify({
									lensWidth: 80, 
									lensHeight: 80,
									stageCss: { width: '488px', height: '290px', border: '1px solid #9a9a9a' }
							   
							   });
							$(".mousetrap").live('click',function(){
									$(".ad-image a").trigger('click');
								});
						   $(".ad-image a").imageZoom({speed:800});
					   }
					}
		 });
		 
		$('.lager').tinyTips('', 'title');
		
		
		  
		$('#fax').click(function(){
			$('#als-datei').hide();
			$(this).addClass('checked');
			$('#datei').removeClass('checked');
		});
		
		$('#datei').click(function(){
			$('#als-datei').show();
			$(this).addClass('checked');
			$('#fax').removeClass('checked');
		});
		$('#datei').click();
		
		
		
}); 
	



	/*$(function() {
			var zIndexNumber = 10000;
			$('div.form-style.select').each(function() {
			$(this).css('zIndex', zIndexNumber);
			zIndexNumber -= 10;
			});
			});

			
			$(function() {
			var zIndexNumber = 1000;
			$('.inner-content .block').each(function() {
			$(this).css('zIndex', zIndexNumber);
			zIndexNumber -= 10;
			});
			});*/


			
			





