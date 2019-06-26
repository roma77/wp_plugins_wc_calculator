(function ($) {
	"use strict";
	var EditCalc = {

		calc_number: function (input) {
			
			if (input <= '0') {
				$('input[name="calculate1"]').css({'border': '2px solid #ffa5a5'});
				return false;
			}
	
			var number_1 = parseInt($('input[name="calculate1"]').attr('data-number-1'));
			var number_2 = parseInt($('input[name="calculate1"]').attr('data-number-2'));
			var product_price = parseFloat($('input[name="calculate1"]').attr('data-price'));
			var result_price = ((number_1 + number_2 + product_price) / input).toFixed(2);
			
			$('.result_1').text('Результат: ' + result_price);
			
		},
		
		calc_text: function (input) {
			
			if (input <= '0') {
				$('input[name="calculate2"]').css({'border': '2px solid #ffa5a5'});
				return false;
			}
			
			var text = $('input[name="calculate2"]').attr('data-text');
			
			if (text.length > 0) {
				
				var arr = text.split(' ');
				
				if (arr.length >= input) {
					$('.result_2').text(arr[input-1]);
				} else {
					var quotient = Math.floor(input/arr.length);
					var result = (input - (quotient * arr.length)) - 1;
					$('.result_2').text(arr[result]);
				}
				
			} else {
				$('.result_2').text('Текст не введён');
			}

		},

		init: function () {
			$('.js_calculator_1').on('click', function () {
				
				var calculate1 = $('input[name="calculate1"]').val();
				if ( calculate1 !== '' ) {
					EditCalc.calc_number( calculate1 );
				} else {
					
					$('input[name="calculate1"]').css({'border': '2px solid #ffa5a5'});
					$('.result_1').text('');
				}
			});
			
			$('.js_calculator_2').on('click', function () {
				
				var calculate2 = $('input[name="calculate2"]').val();
				if ( calculate2 !== '' ) {
					EditCalc.calc_text( calculate2 );
				} else {
					
					$('input[name="calculate2"]').css({'border': '2px solid #ffa5a5'});
					$('.result_2').text('');
				}
			});
	
			jQuery(document).on('input', '.input_calc', function () {
				jQuery(this).removeAttr('style');
				$('.result_1').text('');
				$('.result_2').text('');
			}); 
			
		}
	};

	$(document).ready(function () {
		EditCalc.init();
	});
})(jQuery);