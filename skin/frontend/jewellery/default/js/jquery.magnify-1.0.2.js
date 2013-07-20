
/*
Magnify, by Josh Nathanson
An image magnifier plugin for jQuery
Copyright 2008, see license at end of file
version 1.0.2

name	 magnify
type	 jQuery
param	 options                  hash                    object containing config options
param	 options[lensWidth]       int                     width of lens in px
param	 options[lensHeight]      int                     height of lens in px
param	 options[showEvent]       string                  event upon which to display large image
param 	 options[hideEvent] 	  string                  event upon which to hide large image
param 	 options[stagePlacement]  string                  placement of stage, may be 'right' or 'left'
param	 options[preload]         boolean                 preload large image or not
param	 options[loadingImage]    string                  path to 'loading' image, i.e. ajax spinner
param	 options[stageCss]        object                  addt'l styles for stage                               
param	 options[lensCss]	  	  object				  addt'l styles for lens
param	 options[onBeforeShow]	  function                pass in function to execute before large image is shown
param	 options[onAfterShow]	  function				  pass in function to execute after large image is shown
param	 options[onBeforeHide]	  function				  pass in function to execute before large image is hidden
param 	 options[onAfterHide]	  function				  pass in function to execute after large image is hidden

*** all function params get passed back the settings object as an argument ***

*/


jQuery.fn.magnify = function( options ) {

	var settings = {
		lensWidth: 100,
		lensHeight: 80,
		showEvent: 'hover',
		hideEvent: 'mouseover',
		stagePlacement: 'right',
		preload: true,
		loadingImage: '',
		stageCss: {},
		lensCss: {},
		onBeforeShow: function() {},
		onAfterShow: function() {},
		onBeforeHide: function() {},
		onAfterHide: function() {}
	};
	
	options ? jQuery.extend( settings, options ) : null;
	
	return this.each(function() {
	
		var img = jQuery("img:first", this),
			smallimage = new Smallimage( img ),
			a = jQuery(this),
			top_p = a.parent().position(),
			lens = new Lens(),
			largeimage = new Largeimage( a[0].href ),
			largeimageloaded = false,
			smallimagedata = {},
			stage = null, 
			scale = 1, 
			running = false, 
			loader = null,
			noimage = false,
			self = this;

		smallimage.loadimage();
	
		img.bind( settings.showEvent, activate );

        img.bind( "mouseout", function () {
            jQuery(this).removeClass('hoverElem');
        });

        var timer;

		function activate( e ) {
           /* if(timer) {
                    clearTimeout(timer);
                    timer = null
            }*/

            jQuery(this).addClass('hoverElem');

           // timer = setTimeout(function() {
                if ( ! jQuery(e.currentTarget).is('.hoverElem') ) return false;
                if ( !running ) {
                    running = true;
                    settings.onBeforeShow.apply( smallimage, [ settings ] );
                    if (!largeimage) { // a re-show
                        largeimage = new Largeimage( a[0].href );
                    }
                    lens.activate( e );
                    lens.setposition( e );
                    jQuery( lens.node )
                        .bind( 'mousemove', lens.setposition )
                        .bind( settings.hideEvent, deactivate );
                    if ( largeimageloaded ) { // preload true
              stage = new Stage();
                        stage.activate();

                    }
                    else {
                        // preload false or re-show
                        largeimage.loadimage();
                    }
                    settings.onAfterShow.apply( smallimage, [ settings ] );
                    a[0].blur();
                    return false;
                }
                else deactivate( e );
           // }, 50);
		}
		
		function deactivate( e ) {
			settings.onBeforeHide.apply( self, [ settings ] );
			jQuery( lens.node ).unbind( 'mousemove' ).unbind( 'mouseover' ).remove();
			jQuery( stage.node ).remove();
			jQuery( largeimage.node ).remove();
			largeimage = null;
			largeimageloaded = false;
			settings.onAfterHide.apply( self, [ settings ] );
			running = false;
			
			// a brutal hack to prevent the annoying re-firing of mouseover 
			// when mouseout over top of small image.
			// unbind and rebind show event after 50ms.
			img.unbind( settings.showEvent );
			var s = setTimeout(function() {
                img.bind( settings.showEvent, activate );
            }, 50);
		}		
	
		/********* classes: Smallimage, Largeimage, Lens, Stage, Loader ***********/
		
		function Smallimage( image ) {
			this.node = image[0];						
			
			this.loadimage = function() {
				this.node.src = img[0].src;
			};
			
			this.node.onload = function() {
				smallimagedata.w = jQuery( this ).width();
				smallimagedata.h = jQuery( this ).height();
				smallimagedata.pos = jQuery( this ).offset();
				
				smallimagedata.wp = jQuery( this ).parent().parent().parent().width();
				smallimagedata.posp =jQuery( this ).parent().parent().parent().offset();
				smallimagedata.pos.rp = smallimagedata.wp + smallimagedata.posp.left + 16;
												
				smallimagedata.pos.r = smallimagedata.w + smallimagedata.pos.left;
				smallimagedata.pos.b = smallimagedata.h + smallimagedata.pos.top;
			
				if ( settings.preload ) {
					largeimage.loadimage();
				}
			};
			
			return this;
		}
		
		function Largeimage( url ) {
			
			this.url = url;
			this.node = new Image();
			
			
			this.loadimage = function() {
				if (!this.node) 
					this.node = new Image();
				this.node.style.position = 'absolute';
				this.node.style.display = 'none';
				this.node.style.left = '-5000px';
				loader = new Loader();	
				document.body.appendChild( this.node );
				
				this.node.src = this.url; // fires off async
				
				//this.node.style.border= '1px solid #000';
				
			};
						
			this.node.onload = function() {
				// context is largeimage.node
				
				largeimageloaded = true;
				// set to block, get width & height, then hide again
				this.style.display = 'block';
				var w = jQuery(this).width(),
					h = jQuery(this).height();
					
					wratio=w/488;
					hratio=h/290;
					
					wratioint= Math.ceil(w/488);
					hratioint= Math.ceil(h/290);
					
					img_posside=(488*wratioint-w)/2;

					img_postop=(290*hratioint-h)/2;
					img_postop=Math.abs(img_postop);

					if(wratio<2.77)
					this.style.marginLeft=img_posside+'px';
					if(hratio<5.56)
					this.style.marginTop=img_postop+'px';
					
				this.style.display = 'none';
				scale = ( w / smallimagedata.w + h / smallimagedata.h ) / 2;
				
				jQuery( loader.node ).remove();
				
				if ( running ) { // loaded on show event, so must activate stage
					stage = new Stage();
					stage.activate();
				}
			};			
			
			this.node.onerror = function() {
				jQuery( loader.node ).remove();
				noimage = true;
				if ( running ) {
					stage = new Stage();
					stage.activate();
				}
			};
			return this;
		}
					
		Largeimage.prototype.setposition = function() {
			this.node.style.left = Math.ceil( -scale * parseInt(lens.getoffset().left)) + 'px';
			this.node.style.top = Math.ceil( -scale * parseInt(lens.getoffset().top)) + 'px';
			
			// todo :  Changed
			/*wl=jQuery(largeimage.node).width();
			hl=jQuery(largeimage.node).height();
			aspect=wl/hl;
			if(wl<488&&wl>233)
			this.node.style.left = Math.ceil(-scale*parseInt(lens.getoffset().left)) + img_posside +'px';
			if(wl<233)
			this.node.style.left = Math.ceil(-scale*parseInt(lens.getoffset().left)) + settings.lensWidth/2 +'px';            
			if(aspect>1&&wl>233&&wl<488)
			this.node.style.left = Math.ceil(-scale*parseInt(lens.getoffset().left)) + img_posside + settings.lensWidth*0.5 +'px';*/
			
		};
		
		function Lens() {
			this.node = document.createElement("div");

			jQuery( this.node )
				.css({ 		
					width: settings.lensWidth + 'px',
					height: settings.lensHeight + 'px',
					backgroundColor: 'white',
					opacity: 0.6,
					position: 'absolute',
					border: '1px dashed #bbbbbb',
					zIndex: 10001,
					cursor: 'pointer'
				})
				.css( settings.lensCss ).addClass('mousetrap');
				
				
				
			return this;
		}
		
		Lens.prototype.activate = function() {
			document.body.appendChild( this.node );
			this.node.style.display = 'block';
			
			
			lensh=settings.lensHeight;
			smh=smallimagedata.h;
			if(lensh>smh)
			this.node.style.height=smh + 'px';
			
		};
		
		Lens.prototype.setposition = function( e ) {
			
				
			// todo :  Changed
			var self = e.type == 'mousemove'
					? this
					: this.node,
				lensleft = e.pageX - settings.lensWidth / 2,
				lenstop = e.pageY - settings.lensHeight / 2,
				realwidth =  parseInt(self.style.width) 
								+ parseInt(self.style.borderLeftWidth) 
								+ parseInt(self.style.borderRightWidth),
				realheight = parseInt(self.style.height) 
								+ parseInt(self.style.borderTopWidth)
								+ parseInt(self.style.borderBottomWidth),
				incorner = incorner();
			
			if ( !incorner ) {
				// use 'self' so the context is right whether setting mouseover or on mousemove
				if ( againstleft() ) {
					self.style.top = lenstop + 'px';
					self.style.left = smallimagedata.pos.left + 'px';
				}
				else if ( againstright() ) {
					//console.log(self.style.width);
					self.style.top = lenstop + 'px';
					self.style.left = smallimagedata.pos.r - realwidth + 'px';
				}
				else if ( againsttop() ) {
					self.style.left = lensleft + 'px';
					self.style.top = smallimagedata.pos.top + 'px';
				}
				else if ( againstbottom() ) {
					self.style.left = lensleft + 'px';
					self.style.top = smallimagedata.pos.b - realheight + 'px';
				}
				else {
					self.style.top = lenstop + 'px';
					self.style.left = lensleft + 'px';
				}
			}
			else {
				self.style.top = incorner == 'topleft' || incorner == 'topright'
					? smallimagedata.pos.top + 'px'
					: smallimagedata.pos.b - realheight + 'px';
				self.style.left = incorner == 'topleft' || incorner == 'bottomleft'
					? smallimagedata.pos.left + 'px'
					: smallimagedata.pos.r - realwidth + 'px';
			}		
			
			largeimage.setposition();
			
			function againstleft() {
				return lensleft < smallimagedata.pos.left;
			}
			
			function againstright() {
				return lensleft + realwidth	> smallimagedata.pos.r;
			}
			
			function againsttop() {
				return lenstop < smallimagedata.pos.top;
			}
			
			function againstbottom() {
				return lenstop + realheight	> smallimagedata.pos.b;
			}
			
			function incorner() {
				if ( againstbottom() && againstright() )
					return 'bottomright';
				else if ( againstbottom() && againstleft() )
					return 'bottomleft';
				else if ( againsttop() && againstright() )
					return 'topright';
				else if ( againsttop() && againstleft() )
					return 'topleft';
				else
					return false;
			}
		};
		
		Lens.prototype.getoffset = function() {
			var o = {};
			o.left = parseInt(this.node.style.left) - parseInt(smallimagedata.pos.left) + 'px';
			o.top =  parseInt(this.node.style.top) - parseInt(smallimagedata.pos.top) + 'px';
			return o;
		};
		
		function Stage() {
			this.node = document.createElement("div");
			var cssKlasse = document.createAttribute("id");
      cssKlasse.nodeValue = "stage";
      this.node.setAttributeNode(cssKlasse);
			jQuery( this.node )

				.css({
					position: 'absolute',
					width: Math.round(settings.lensWidth * scale) + 'px',
					height: Math.round(settings.lensHeight * scale) + 'px',

					zIndex: 15000,
					overflow: 'hidden',
					border: '1px solid #999999',
					display: 'none',
					backgroundColor:'white'
				})
				.css( settings.stageCss );

			this.realheight = parseInt(this.node.style.height)
								+ parseInt(this.node.style.borderTopWidth)
								+ parseInt(this.node.style.borderBottomWidth);
			this.realwidth =  parseInt(this.node.style.width)
								+ parseInt(this.node.style.borderLeftWidth)
								+ parseInt(this.node.style.borderRightWidth);
			var st = jQuery.browser.safari
						? document.body.scrollTop
						: document.documentElement.scrollTop;
			var screenbottom = document.documentElement.clientHeight + st;


			this.node.style.top = smallimagedata.pos.top + this.realheight > screenbottom
									? screenbottom - this.realheight - 10 + 'px'
									: smallimagedata.pos.top - top_p.top -1 + 'px';
			this.node.style.left = settings.stagePlacement == 'right'
							? smallimagedata.pos.rp + 'px'
							: smallimagedata.pos.left - this.realwidth - 10 + 'px';
			return this;
		}
		
		Stage.prototype.activate = function() {
			if ( noimage ) 
				this.node.appendChild( document.createTextNode('Large Image Not Found.') );
			else {				
				if ( !this.node.firstChild ) 
					this.node.appendChild( largeimage.node );
				largeimage.node.style.display = 'block';
				largeimage.setposition();
			}
			document.body.appendChild( this.node );
			jQuery( this.node ).fadeIn( 300 );
		};
		
		function Loader() {
			this.node = settings.loadingImage 
				? new Image()
				: document.createElement("div");
			jQuery( this.node )
				.appendTo("body")
				.css({
					top: smallimagedata.pos.top + 0 + 'px',
					left: smallimagedata.pos.left + 10 + 'px',
					position: 'absolute'
				});
			settings.loadingImage
				? this.node.src = settings.loadingImage
				: this.node.appendChild( document.createTextNode( '' ));
			return this;
			/*settings.loadingImage
				? this.node.src = settings.loadingImage
				: this.node.appendChild( document.createTextNode( 'loading ...' ));
			return this;
			*/
		}		
	});
};

/*
+-----------------------------------------------------------------------+
| Copyright (c) 2008 Josh Nathanson                  |
| All rights reserved.                                                  |
|                                                                       |
| Redistribution and use in source and binary forms, with or without    |
| modification, are permitted provided that the following conditions    |
| are met:                                                              |
|                                                                       |
| o Redistributions of source code must retain the above copyright      |
|   notice, this list of conditions and the following disclaimer.       |
| o Redistributions in binary form must reproduce the above copyright   |
|   notice, this list of conditions and the following disclaimer in the |
|   documentation and/or other materials provided with the distribution.|
|                                                                       |
| THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
| "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
| LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
| A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
| OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
| SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
| LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
| DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
| THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
| OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
|                                                                       |
+-----------------------------------------------------------------------+
*/