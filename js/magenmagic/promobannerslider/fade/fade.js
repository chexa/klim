(function ($) {

	$.widget("alexb.fadeGallery", {
        counter: 0,
        options: {
            speed : 1,
            auto  : true
        },
        _create: function () {
            var self = this, o = this.options, el = this.element;
            el.find("*:first").addClass("currFade");
            var current = el.find("*:first");

            var img = current.find("img");
            img.load(function () {
                el.height(current.height());
                if ( el.find(".itemFade").size() < 2 ) return false;
                setInterval ( function () {
                    self.counter++;
                    if ( self.counter == o.speed)
                    {
                        self.scroll();
                        self.counter = 0;
                    }
                }, 1000);
            })
        },
        scroll : function () {
            var self = this, o = this.options, el = this.element;

            var current = el.find(".currFade");
            if ( current.size() == 0 )
            {
                current = el.find("*:first").addClass("currFade");
            }

            var next = current.next();
            if ( next.size() == 0 )
            {
                next = el.find("*:first");
            }

            next.show();
            self._preload(current);
            self.counter = 0;


            next.css("z-index", "2");
            var crHeight = next.height();
			
			next.fadeIn(600);

            el.height(crHeight);
            current.fadeOut(600, function () {
                self.counter = 0;
                current.css("display","block").css("z-index", "1");
				current.hide();
                //el.height(next.height());
                $(this).removeClass("currFade");
                next.addClass("currFade").css("z-index", "3");
            });

        },
        _preload : function (el)
        {
            var preloadElement = el.next();
            if ( preloadElement.size() == 0 ) return false;
            preloadElement = preloadElement.next();
            if ( preloadElement.size() == 0 ) return false;
            if ( preloadElement.find("img").size() != 0 ) return false;

            var imageSrc = preloadElement.attr("rel");
            var image = new Image();
            image.src = imageSrc;

            preloadElement.append(image);

        }

    });

}) (jQuery);