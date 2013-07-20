/*
 * FormStyle - jQuery plugin 0.1.0
 *
 * Copyright (c) 2009 Wei Kin Huang, TimeDelimited.com
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
( function($j) {
	$j.fn.formstyle = function(options) {
		var defaults = {};
		var options = $j.extend(defaults, options);

		return this.each( function() {
			$j('input:checkbox', this).checkboxstyle();
			$j('input:radio', this).radiostyle();
			$j('select', this).selectstyle();
			$j('input[type=file]', this).filestyle();
		});
	};
})(jQuery);

( function($j) {
	$j.fn.checkboxstyle = function(options) {
		var defaults = {};
		var options = $j.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			var chek_id=$j(_this).attr('id');
			var wrapper = $j(_this).wrap('<a href="#"></a>').parent('a').addClass('form-style').attr('id', chek_id);

			$j(wrapper).addClass('checkbox');

			$j(wrapper).click( function() {
				if (!_this.disabled) {
					_this.checked = !_this.checked;
				}

				$j(_this).change();
				return false;
			});

			$j('label[for=' + $j(_this).attr('id') + ']').click( function() {
				if (!_this.disabled) {
					_this.checked = !_this.checked;
				}

				$j(_this).change();
				return false;
			});
			$j(_this).parents('label').click( function() {
				if (!_this.disabled) {
					_this.checked = !_this.checked;
				}

				$j(_this).change();
				return false;
			});

			$j(_this).change( function() {
				(this.checked) ? $j(wrapper).addClass('checked') : $j(wrapper).removeClass('checked');
			});

			if (_this.disabled) {
				$j(wrapper).addClass('disabled');
			}
			if (_this.checked) {
				$j(wrapper).addClass('checked');
			}
		});
	};
})(jQuery);

( function($j) {
	$j.fn.radiostyle = function(options) {
		var defaults = {};
		var options = $j.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			var wrapper = $j(_this).wrap('<a href="#"></a>').parent('a').addClass('form-style');

			$j(wrapper).addClass('radio');

			$j(wrapper).click( function() {
				if (!_this.disabled) {
					_this.checked = true;
				}

				$j(_this).change();
				return false;
			});

			$j('label[for=' + $j(_this).attr('id') + ']').click( function() {
				if (!_this.disabled) {
					_this.checked = true;
				}

				$j(_this).change();
				return false;
			});

			$j(_this).change( function() {
				$j('input:radio[name="' + $j(this).attr('name') + '"]').not(this).parent('a').removeClass('checked');
				(this.checked) ? $j(wrapper).addClass('checked') : $j(wrapper).removeClass('checked');
			});

			if (_this.disabled) {
				$j(wrapper).addClass('disabled');
			}
			if (_this.checked) {
				$j(wrapper).addClass('checked');
			}
		});
	};
})(jQuery);



( function($j) {
	$j.fn.selectstyle = function(options) {
		var defaults = {};
		var options = $j.extend(defaults, options);

		return this.each( function() {
			var _this = this;
			selId=$j(this).attr('id');
			defaultVal = $j(this).val();
			selOnChange = $j(this).attr("onchange");
			var wrapper = $j(_this).wrap('<div></div>').parent('div').addClass('form-style');
			$j(wrapper).addClass('select').append('<a href="#" class="selector"></a>').append('<a href="#" class="expander"></a>').append('<ul class="options"></ul>').append('<input type="hidden" id="'+selId+'" value="'+defaultVal+'" />');
			var selector = $j(wrapper).find('a.selector').get(0);
			var expander = $j(wrapper).find('a.expander').get(0);
			var optgroup = $j(wrapper).find('ul.options').get(0);
			
			if(selOnChange) { 
				$j("input#sort").bind('change', function () {
					setLocation(this.value);
				});
			}

			function generateOptions() {
				$j(optgroup).html('');
				
				$j('option', _this).each( function(i) {
					ocl=$j(this).attr('class');
					optval=$j(this).val();
					$j(optgroup).append('<li><a href="#" rel="' + i + '" class="' + ocl + '" value="' + optval + '" >' + this.text + '</a></li>');
				});
				$j('a:even', optgroup).addClass('even');
			}

			function expandOptions() {
				if ($j(optgroup).css('display') != 'none') {
					$j(expander).removeClass('open');
					$j(optgroup).slideUp(10);
				} else {
					$j(expander).addClass('open');
					$j(optgroup).slideDown(10);
					var selected_offset = parseInt($j('a.selected', optgroup).position().top);
					$j(optgroup).animate( {
						scrollTop :selected_offset
					});
				}
				return false;
			}

			generateOptions();

			$j(expander).click(expandOptions);
			$j(selector).click(expandOptions);
			$j('label[for=' + $j(_this).attr('id') + ']').click(expandOptions);

			$j(_this).change( function() {
				$j('a:eq(' + this.selectedIndex + ')', optgroup).click();
				
			});
			

			$j('a', optgroup).click( function() {
				$j(selector).removeClass('price-up price-down');
				$j('a.selected', optgroup).removeClass('selected');
				$j(this).addClass('selected');
				_this.selectedIndex = parseInt($j(this).attr('rel'));
				
				
				//$j(selector).html($j(this).html()).addClass($j(this).attr('class'));
				$j(selector).html($j(this).html()).attr('value',$j(this).attr('value')).addClass($j(this).attr('class'));
				$j(expander).removeClass('open');
				$j(optgroup).slideUp(10);
				
				$j('input#sort').val($j(selector).attr('value'));
				//$j('input#sort').change();
				return false;
			});
			

			$j('a:eq(' + _this.selectedIndex + ')', optgroup).click();
			
			$j(optgroup).find('a').not('.selected').click( function() {
				$j('input#sort').change();
			});
			
			$j(document).click(
				function() {
				$j('div.select').children('ul.options').hide();
				}
				);
				
		});
	};
})(jQuery);

( function($j) {
	$j.fn.filestyle = function(options) {
		var settings = {
			width :371,
			imagewidth :111,
			imageheight :20
		};
		$j.extend(settings, options);
		return this.each( function() {
			var _this = this;
			var wrapper = $j(_this).wrap('<div></div>').parent('div').addClass('form-style').attr( {
				name :$j(_this).attr('name')
			});
			$j(wrapper).addClass('file').prepend('<input type="text" readonly="readonly" name="' + $j(_this).attr('name') + '" />');
			$j(_this).wrap('<div class="file-button"></div>');

			$j(_this).change( function() {
				var v = $j(_this).val();
				v = v.split(/[\/\\]/);
				var l = v.length;
				$j('input[type=text]', wrapper).val(v[l - 1]);
			});
		});
	};
})(jQuery);