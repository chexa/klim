
$j(function (){

  $j('body').append('<div id="overlay"></div>');
  $j('body').append('<div id="popup"></div>');

  $j('.lightbox').click(function(){ShowImage(this.href);return false});
  $j('#overlay').click(function () {
		    $j('#overlay').fadeOut();
		    $j('#popup').hide();
		    $j("#feedback-box").hide();
		    return false;
		});
		
		$j('body').keypress(function(event) {
		    if (event.keyCode == '27') {
			$j('#overlay').fadeOut();
                        $j('#popup').hide();
			$j("#feedback-box").hide();
                        return false;
		    }	    
		});
});
 
function ShowImage(url){
 
	var yScroll = $j.browser.msie ? document.documentElement.scrollTop : self.pageYOffset;

	var windowHeight = $j.browser.msie ? document.documentElement.clientHeight : window.innerHeight;

	var posTop = Math.round((windowHeight/2) + yScroll);
 

  $j('#popup').css('display','none');
  var img = new Image();					
 

	$j(img).load(function(){
	
		$j('#popup').html(this);
		  
		$j('#popup').append('<a href="#" id="close"></a>');
		$j('#close').click(function () {
			$j('#overlay').fadeOut();
			$j('#popup').hide();
			return false;
		  });
		$j('#popup img').click(function () {
                    $j('#overlay').fadeOut();
                    $j('#popup').hide();
                    return false;
                });
		
		
											    

		$j('#popup').css({top:posTop+'px',marginLeft:'-'+Math.round($j("#popup").innerWidth()/2)+'px',marginTop:'-'+Math.round($j("#popup").innerHeight()/2)+'px'});
		$j('#overlay').css({
						width:		$j(window).width(),
						height:		$j(document).height(),
						opacity:  0.6
					}).fadeIn(400,function() {
		$j('#popup').fadeIn('normal').css('opacity','1');
		});
  }).attr('src', url);
}
 
function overlayFit()
{
  $j('#overlay').css({
    width: $j(window).width(),
    height: $j(document).height()
    });
}

function popupFit(){
 
	var yScroll = $j.browser.msie ? document.documentElement.scrollTop : self.pageYOffset;
  var windowHeight = $j.browser.msie ? document.documentElement.clientHeight : window.innerHeight;
  var posTop = Math.round((windowHeight/2) + yScroll);

  $j('#popup').stop().animate({top: posTop+'px'},500);
 
}
 
window.onresize = overlayFit;
window.onscroll = popupFit;
