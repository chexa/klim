﻿jQuery.fn.imageZoom=function(conf){var config=jQuery.extend({speed:200,dontFadeIn:1,hideClicked:1,imageMargin:15,className:'jquery-image-zoom',loading:''},conf);config.doubleSpeed=config.speed/4; return this.click(function(e){var clickedElement=jQuery(e.target);var clickedLink=clickedElement.is('a')?clickedElement:clickedElement.parents('a');clickedLink=(clickedLink&&clickedLink.is('a')&&clickedLink.attr('href').search(/(.*)\.(jpg|jpeg|gif|png|bmp|tif|tiff)$/gi)!=-1)?clickedLink:false;var clickedImg=(clickedLink&&clickedLink.find('img').length)?clickedLink.find('img'):false;if(clickedLink){clickedLink.oldText=clickedLink.text();clickedLink.setLoadingImg=function(){if(clickedImg){clickedImg.css({opacity:'0.5'})}else{clickedLink.text(config.loading)}};clickedLink.setNotLoadingImg=function(){if(clickedImg){clickedImg.css({opacity:'1'})}else{clickedLink.text(clickedLink.oldText)}};var displayImgSrc=clickedLink.attr('href');if(jQuery('div.'+config.className+' img[src="'+displayImgSrc+'"]').length){return false}var preloadOnload=function(){clickedLink.setNotLoadingImg();var dimElement=clickedImg?clickedImg:clickedLink;var hideClicked=clickedImg?config.hideClicked:0;var offset=dimElement.offset();var imgzoomBefore={width:dimElement.outerWidth(),height:dimElement.outerHeight(),left:offset.left,top:offset.top};var imgzoom=jQuery('<div><img src="'+displayImgSrc+'" alt="" /></div>').css('position','absolute').appendTo(document.body);var imgzoomAfter={width:imgzoom.outerWidth(),height:imgzoom.outerHeight()};var windowDim={width:jQuery(window).width(),height:jQuery(window).height()};if(imgzoomAfter.width>(windowDim.width-config.imageMargin*2)){var nWidth=windowDim.width-config.imageMargin*2;imgzoomAfter.height=(nWidth/imgzoomAfter.width)*imgzoomAfter.height;imgzoomAfter.width=nWidth}if(imgzoomAfter.height>(windowDim.height-config.imageMargin*2)){var nHeight=windowDim.height-config.imageMargin*2;imgzoomAfter.width=(nHeight/imgzoomAfter.height)*imgzoomAfter.width;imgzoomAfter.height=nHeight}imgzoomAfter.left=(windowDim.width-imgzoomAfter.width)/2+jQuery(window).scrollLeft();imgzoomAfter.top=(windowDim.height-imgzoomAfter.height)/2+jQuery(window).scrollTop();var closeButton=jQuery('<a href="#">Schließen</a>').appendTo(imgzoom).hide();if(hideClicked){clickedLink.css('visibility','hidden')}imgzoom.addClass(config.className).css(imgzoomBefore).animate(imgzoomAfter,config.speed,function(){closeButton.fadeIn(config.doubleSpeed)});var hideImgzoom=function(){closeButton.fadeOut(config.doubleSpeed,function(){imgzoom.animate(imgzoomBefore,config.speed,function(){clickedLink.css('visibility','visible');imgzoom.remove()})});return false};imgzoom.click(hideImgzoom);closeButton.click(hideImgzoom)};var preload=new Image();preload.src=displayImgSrc;if(preload.complete){preloadOnload()}else{clickedLink.setLoadingImg();preload.onload=preloadOnload}return false}})};$j(document).keydown(function(e){if(e.keyCode==27){$j('div.jquery-image-zoom a').click()}});