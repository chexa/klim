/*
 * FormStyle - jQuery plugin 0.1.0
 *
 * Copyright (c) 2009 Wei Kin Huang, TimeDelimited.com
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
( function($) {
	$.fn.formstyle = function(options) {
		var defaults = {};
		var options = $.extend(defaults, options);

		return this.each( function() {
			$('input:checkbox', this).checkboxstyle();
			$('input:radio', this).radiostyle();
			$('select', this).selectstyle();
			$('input[type=file]', this).filestyle();
		});
	};
})(jQuery);

( function($) {
	$.fn.checkboxstyle = function(options) {
		var defaults = {};
		var options = $.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			var chek_id=$(_this).attr('id');
			var wrapper = $(_this).wrap('<a href="#"></a>').parent('a').addClass('form-style').attr('id', chek_id);

			$(wrapper).addClass('checkbox');

			$(wrapper).click( function() {
				if (!_this.disabled) {
					_this.checked = !_this.checked;
				}

				$(_this).change();
				return false;
			});

			$('label[for=' + $(_this).attr('id') + ']').click( function() {
				if (!_this.disabled) {
					_this.checked = !_this.checked;
				}

				$(_this).change();
				return false;
			});

			$(_this).change( function() {
				(this.checked) ? $(wrapper).addClass('checked') : $(wrapper).removeClass('checked');
			});

			if (_this.disabled) {
				$(wrapper).addClass('disabled');
			}
			if (_this.checked) {
				$(wrapper).addClass('checked');
			}
		});
	};
})(jQuery);

( function($) {
	$.fn.radiostyle = function(options) {
		var defaults = {};
		var options = $.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			var wrapper = $(_this).wrap('<a href="#"></a>').parent('a').addClass('form-style');

			$(wrapper).addClass('radio');

			$(wrapper).click( function() {
				if (!_this.disabled) {
					_this.checked = true;
				}

				$(_this).change();
				return false;
			});

			$('label[for=' + $(_this).attr('id') + ']').click( function() {
				if (!_this.disabled) {
					_this.checked = true;
				}

				$(_this).change();
				return false;
			});

			$(_this).change( function() {
				$('input:radio[name="' + $(this).attr('name') + '"]').not(this).parent('a').removeClass('checked');
				(this.checked) ? $(wrapper).addClass('checked') : $(wrapper).removeClass('checked');
			});

			if (_this.disabled) {
				$(wrapper).addClass('disabled');
			}
			if (_this.checked) {
				$(wrapper).addClass('checked');
			}
		});
	};
})(jQuery);

( function($) {
	$.fn.selectstyle = function(options) {
		var defaults = {};
		var options = $.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			var wrapper = $(_this).wrap('<div></div>').parent('div').addClass('form-style');
			$(wrapper).addClass('select').append('<a href="#" class="selector"></a>').append('<a href="#" class="expander"></a>').append('<ul class="options"></ul>');
			var selector = $(wrapper).find('a.selector').get(0);
			var expander = $(wrapper).find('a.expander').get(0);
			var optgroup = $(wrapper).find('ul.options').get(0);

			function generateOptions() {
				$(optgroup).html('');
				
				$('option', _this).each( function(i) {
					ocl=$(this).attr('class');
					$(optgroup).append('<li><a href="#" rel="' + i + '" class="' + ocl + '">' + this.text + '</a></li>');
				});
				$('a:even', optgroup).addClass('even');
			}

			function expandOptions() {
				if ($(optgroup).css('display') != 'none') {
					$(expander).removeClass('open');
					$(optgroup).slideUp(10);
				} else {
					$(expander).addClass('open');
					$(optgroup).slideDown(10);
					var selected_offset = parseInt($('a.selected', optgroup).position().top);
					$(optgroup).animate( {
						scrollTop :selected_offset
					});
				}
				return false;
			}

			generateOptions();

			$(expander).click(expandOptions);
			$(selector).click(expandOptions);
			$('label[for=' + $(_this).attr('id') + ']').click(expandOptions);

			$(_this).change( function() {
				$('a:eq(' + this.selectedIndex + ')', optgroup).click();
			});
			

			$('a', optgroup).click( function() {
				$(selector).removeClass('price-up price-down');
				$('a.selected', optgroup).removeClass('selected');
				$(this).addClass('selected');
				_this.selectedIndex = parseInt($(this).attr('rel'));
				
				
				$(selector).html($(this).html()).addClass($(this).attr('class'));
				$(expander).removeClass('open');
				$(optgroup).slideUp(10);
				return false;
			});
			
			$('a:eq(' + _this.selectedIndex + ')', optgroup).click();
			
			$(document).click(
				function() {
				$('div.select').children('ul.options').hide();
				}
				);
				
		});
	};
})(jQuery);

( function($) {
	$.fn.filestyle = function(options) {
		var settings = {
			width :371,
			imagewidth :111,
			imageheight :20
		};
		$.extend(settings, options);
		return this.each( function() {
			var _this = this;
			var wrapper = $(_this).wrap('<div></div>').parent('div').addClass('form-style').attr( {
				name :$(_this).attr('name')
			});
			$(wrapper).addClass('file').prepend('<input type="text" readonly="readonly" name="' + $(_this).attr('name') + '" />');
			$(_this).wrap('<div class="file-button"></div>');

			$(_this).change( function() {
				var v = $(_this).val();
				v = v.split(/[\/\\]/);
				var l = v.length;
				$('input[type=text]', wrapper).val(v[l - 1]);
			});
		});
	};
})(jQuery);