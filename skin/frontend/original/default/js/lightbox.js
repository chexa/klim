
$(function (){

  $('body').append('<div id="overlay"></div>');
  $('body').append('<div id="popup"></div>');

  $('.lightbox').click(function(){ShowImage(this.href);return false});
  $('#overlay').click(function () { 
		    $('#overlay').fadeOut();
		    $('#popup').hide();
		    $("#feedback-box").hide(); 
		    return false;
		});
		
		$('body').keypress(function(event) {
		    if (event.keyCode == '27') {
			$('#overlay').fadeOut();
                        $('#popup').hide();
			$("#feedback-box").hide(); 
                        return false;
		    }	    
		});
});
 
function ShowImage(url){
 
	var yScroll = $.browser.msie ? document.documentElement.scrollTop : self.pageYOffset;

	var windowHeight = $.browser.msie ? document.documentElement.clientHeight : window.innerHeight;

	var posTop = Math.round((windowHeight/2) + yScroll);
 

  $('#popup').css('display','none');
  var img = new Image();					
 

	$(img).load(function(){
	
		$('#popup').html(this);
		  
		$('#popup').append('<a href="#" id="close"></a>');
		$('#close').click(function () { 
			$('#overlay').fadeOut();
			$('#popup').hide();
			return false;
		  });
		$('#popup img').click(function () { 
                    $('#overlay').fadeOut();
                    $('#popup').hide();
                    return false;
                });
		
		
											    

		$('#popup').css({top:posTop+'px',marginLeft:'-'+Math.round($("#popup").innerWidth()/2)+'px',marginTop:'-'+Math.round($("#popup").innerHeight()/2)+'px'});
		$('#overlay').css({
						width:		$(window).width(),
						height:		$(document).height(),
						opacity:  0.6
					}).fadeIn(400,function() {
		$('#popup').fadeIn('normal').css('opacity','1');
		});
  }).attr('src', url);
}
 
function overlayFit()
{
  $('#overlay').css({
    width: $(window).width(),
    height: $(document).height()
    });
}

function popupFit(){
 
	var yScroll = $.browser.msie ? document.documentElement.scrollTop : self.pageYOffset;
  var windowHeight = $.browser.msie ? document.documentElement.clientHeight : window.innerHeight;
  var posTop = Math.round((windowHeight/2) + yScroll);

  $('#popup').stop().animate({top: posTop+'px'},500);
 
}
 
window.onresize = overlayFit;
window.onscroll = popupFit;
