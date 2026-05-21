jQuery(document).ready(function($){
	
	$('.formlayer-form').on('submit', function(e){
		e.preventDefault();
		
		let $form = $(this);
		$submit_Btn = $form.find('.formlayer-submit-btn'),
		$status = $form.find('.formlayer-form-status'),
		formId = $form.data('form-id');
		
		$submit_Btn.prop('disabled', true).addClass('loading');
		$status.html('').removeClass('formlayer-success-message formlayer-error-message');
		
		var formData = new FormData(this);
		
		// Client-side Validation
		var errors = [];
		
		// Email Confirmation Match
		$form.find('.formlayer-email-confirm').each(function(){
			var matchName = $(this).data('match');
			var originalVal = $form.find('input[name="' + matchName + '"]').val();
			if($(this).val() !== originalVal){
				errors.push('Emails do not match.');
			}
		});

		// Digit Limit Validation
		$form.find('input[type="number"][maxlength]').each(function(){
			var limit = parseInt($(this).attr('maxlength'));
			if($(this).val().length > limit){
				errors.push('Value exceeds maximum digit limit of ' + limit);
			}
		});

		// URL Validation
		$form.find('input[data-validate-url="1"]').each(function(){
			var val = $(this).val();
			if(val && !val.match(/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/)){
				errors.push('Please enter a valid URL.');
			}
		});

		// URL HTTPS Only Validation
		$form.find('input[data-https-only="1"]').each(function(){
			var val = $(this).val();
			if(val && !val.startsWith('https://')){
				errors.push('Only HTTPS URLs are allowed.');
			}
		});

		if(errors.length > 0){
			$status.addClass('formlayer-error-message').html(errors.join('<br>'));
			$submit_Btn.prop('disabled', false).removeClass('loading');
			return;
		}

		formData.append('action', 'formlayer_submit_form');
		formData.append('nonce', formlayer_data.nonce);
		formData.append('form_id', formId);
		
		$.ajax({
			url: formlayer_data.ajax_url,
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response){
				$submit_Btn.prop('disabled', false).removeClass('loading');
				
				if(response.success){
					var settings = response.data.settings || {};
					var confirmation = settings.confirmations || { type: 'message', message: 'Thank you! Your form has been submitted successfully.', hide_form: true };
					
					if(confirmation.type === 'redirect' && confirmation.redirect_url){
						$status.addClass('formlayer-success-message').html('Redirecting...');
						window.location.href = confirmation.redirect_url;
					}else{
						if(confirmation.hide_form !== false){
							$form.find('.formlayer-form-fields-wrapper').hide();
						}
						$form[0].reset();
						$status.addClass('formlayer-success-message').html(confirmation.message || 'Thank you! Your form has been submitted successfully.');
					}
				}else{
					$status.addClass('formlayer-error-message').html(response.data.message || 'An error occurred.');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				$submit_Btn.prop('disabled', false).removeClass('loading');
				console.error('Submission error:', textStatus, errorThrown, jqXHR.responseText);
				
				// User friendly message instead of technical parsererror
				var msg = 'An unexpected error occurred. Please try again.';
				if(textStatus === 'timeout') msg = 'Request timed out. Please check your connection.';
				
				$status.addClass('formlayer-error-message').html(msg);
			}
		});
	});
	// Rating Field Interaction
	$('.formlayer-rating .dashicons').on('click', function(){
		var value = $(this).data('value');
		var $container = $(this).closest('.formlayer-rating');
		$container.find('input').val(value);
		$container.find('.dashicons').removeClass('active');
		$(this).addClass('active').prevAll().addClass('active');
	});

	$('.formlayer-rating .dashicons').on('mouseenter', function(){
		$(this).addClass('hover').prevAll().addClass('hover');
		$(this).nextAll().removeClass('hover');
	}).on('mouseleave', '.formlayer-rating .dashicons', function(){
		$('.formlayer-rating .dashicons').removeClass('hover');
	});
});
