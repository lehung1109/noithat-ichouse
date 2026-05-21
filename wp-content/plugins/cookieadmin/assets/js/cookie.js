// This file is a part of the admin side of CookieAdmin

jQuery(document).ready(function($){
	
	var law = '';
	var mediaUploader;
	
	// Handling the General Setting position toggles
	function toggle_type_options(){
		let jEle = $(this),
		type_value = $(this).val();
		
		if(!type_value){
			return;
		}
		
		let box_layout = $('.cookieadmin_box_layout').closest('label'),
		footer_layout = $('.cookieadmin_footer_layout').closest('label');
		
		$('input[name=cookieadmin_position]').prop('checked', false);
		
		switch(type_value){
			case 'footer':
				$('.consent-position').slideDown();
				$('.consent-modal-layout').slideDown();
				box_layout.hide();
				footer_layout.show();
				break;
				
			case 'popup':
				$('.consent-position').slideUp();
				$('.consent-modal-layout').slideUp();
				break;
				
			default:
				$('.consent-position').slideDown();
				$('.consent-modal-layout').slideDown();
				footer_layout.hide();
				box_layout.show();
				break;
		}	
	}

	$('input[name="cookieadmin_layout"]').on('change', toggle_type_options);
	
	setTimeout( function(){
		$('.updated, .error').not('.no-autohide').fadeOut('slow');
	}, 5000);

	//==== Notice Section Preview Live Change

	$('#cookieadmin_notice_title_layout').on('input', function () {
		const titleValue = $(this).val().trim();
		$('#cookieadmin_notice_title').text(titleValue);
	});

	$('#cookieadmin_notice_layout').on('input', function () {
		const detailValue = $(this).val().trim();
		$('#cookieadmin_notice').text(detailValue);
	});

	$('#cookieadmin_consent_inside_bg_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('.cookieadmin_consent_inside').css('background-color', color);
	});

	$('#cookieadmin_consent_inside_border_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('.cookieadmin_consent_inside').css('border', "1px solid" + color);
	});

	$('#cookieadmin_notice_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_notice').css('color', color);
	});

	$('#cookieadmin_notice_title_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_notice_title').css('color', color);
	});

	//====== Notice Buttons Preview

	$('#cookieadmin_customize_btn').on('input', function () {
		const textData = $(this).val().trim();
		$('#cookieadmin_customize_button').text(textData);
		$('#cookieadmin_customize_modal_button').text(textData);
	});

	$('#cookieadmin_reject_btn').on('input', function () {
		const textData = $(this).val().trim();
		$('#cookieadmin_reject_button').text(textData);
		$('#cookieadmin_reject_modal_button').text(textData);
	});

	$('#cookieadmin_accept_btn').on('input', function () {
		const textData = $(this).val().trim();
		$('#cookieadmin_accept_button').text(textData);
		$('#cookieadmin_accept_modal_button').text(textData);
	});

	$('#cookieadmin_save_btn').on('input', function () {
		const textData = $(this).val().trim();
		$('#cookieadmin_prf_modal_button').text(textData);
	});

	$('#cookieadmin_customize_btn_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_customize_button').css('color', color);
		$('#cookieadmin_customize_modal_button').css('color', color);
	});

	$('#cookieadmin_accept_btn_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_accept_button').css('color', color);
		$('#cookieadmin_accept_modal_button').css('color', color);
	});

	$('#cookieadmin_reject_btn_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_reject_button').css('color', color);
		$('#cookieadmin_reject_modal_button').css('color', color);
	});

	$('#cookieadmin_save_btn_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_prf_modal_button').css('color', color);
	});

	$('#cookieadmin_customize_btn_bg_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_customize_button').css('background-color', color);
		$('#cookieadmin_customize_modal_button').css('background-color', color);
	});

	$('#cookieadmin_accept_btn_bg_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_accept_button').css('background-color', color);
		$('#cookieadmin_accept_modal_button').css('background-color', color);

	});

	$('#cookieadmin_reject_btn_bg_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_reject_button').css('background-color', color);
		$('#cookieadmin_reject_modal_button').css('background-color', color);
	});

	$('#cookieadmin_save_btn_bg_color_box').on('input', function () {
		const color = $(this).val().trim();
		$('#cookieadmin_prf_modal_button').css('background-color', color);
	});

	//======= Preference Section Preview

	$('#cookieadmin_preference_title_layout').on('input', function () {
		const titleValue = $(this).val().trim();
		$('#cookieadmin_preference_title').text(titleValue);
	});

	$('#cookieadmin_preference_layout').on('input', function () {
		const detailValue = $(this).val().trim();
		$('#cookieadmin_preference').text(detailValue);
	});

	$("#cookieadmin_cookie_modal_bg_color_box").on('input', function () {
		const color = $(this).val().trim();
		$(".cookieadmin_cookie_modal").css('background-color', color);
	});

	$("#cookieadmin_cookie_modal_border_color_box").on('input', function () {
		const color = $(this).val().trim();
		$(".cookieadmin_cookie_modal").css('border', "1px solid" + color);
	});

	$("#cookieadmin_details_wrapper_color_box").on('input', function () {
		const color = $(this).val().trim();
		$(".cookieadmin_details_wrapper").css('color', color);
	});

	$("#cookieadmin_preference_title_color_box").on('input', function () {
		const color = $(this).val().trim();
		$("#cookieadmin_preference_title").css('color', color);
	});
	
	//set default or set values in input and preview on page load.
	for(law in cookieadmin_policy){
		
		law = cookieadmin_policy[cookieadmin_policy["set"]];
		
		$("#cookieadmin_consent_type").find("#"+cookieadmin_policy["set"]).attr("selected", true);
		
		$("#cookieadmin_consent_expiry").val(law.cookieadmin_days);
		$("#cookieadmin_layout_"+law.cookieadmin_layout).prop("checked", true).trigger("change");
		
		if(!!law.cookieadmin_position){
			$("#cookieadmin_position_"+law.cookieadmin_position).prop("checked", true);
		}else{
			$(".consent-position").closest('label').hide();
		}
		$("#cookieadmin_modal_" + law.cookieadmin_modal).prop("checked", true);
		$(".cookieadmin_cookie_modal").addClass("cookieadmin_" + law.cookieadmin_modal);

		$(".cookieadmin_consent_inside").css('background-color', law.cookieadmin_consent_inside_bg_color);
		$("#cookieadmin_notice_title").css('color', law.cookieadmin_notice_title_color);
		$("#cookieadmin_notice").css('color', law.cookieadmin_notice_color);
		$(".cookieadmin_consent_inside").css('border', "1px solid" + law.cookieadmin_consent_inside_border_color);

		$(".cookieadmin_cookie_modal").css('background-color', law.cookieadmin_cookie_modal_bg_color);
		$("#cookieadmin_preference_title").css('color', law.cookieadmin_preference_title_color);
		$(".cookieadmin_details_wrapper").css('color', law.cookieadmin_details_wrapper_color);
		$(".cookieadmin_cookie_modal").css('border', "1px solid" + law.cookieadmin_cookie_modal_border_color);
    
		// $("#cookieadmin_notice_title_layout").val(law.cookieadmin_notice_title);
		$("#cookieadmin_notice_title").html(law.cookieadmin_notice_title);
		
		// $("#cookieadmin_notice_layout").val(law.cookieadmin_notice);
		$("#cookieadmin_notice").html(law.cookieadmin_notice);
		
		// $("#cookieadmin_preference_title_layout").val(law.cookieadmin_preference_title);
		$("#cookieadmin_preference_title").html(law.cookieadmin_preference_title);
		
		// $("#cookieadmin_preference_layout").val(law.cookieadmin_preference);
		$("#cookieadmin_preference").html(law.cookieadmin_preference);

		$(".cookieadmin_customize_btn").text(law.cookieadmin_customize_btn);
		$(".cookieadmin_customize_btn").css('background-color', $("#cookieadmin_customize_btn_bg_color").val());
		$(".cookieadmin_customize_btn").css('color', $("#cookieadmin_customize_btn_color").val());

		$(".cookieadmin_reject_btn").text(law.cookieadmin_reject_btn);
		$(".cookieadmin_reject_btn").css('background-color', $("#cookieadmin_reject_btn_bg_color").val());
		$(".cookieadmin_reject_btn").css('color', $("#cookieadmin_reject_btn_color").val());

		$(".cookieadmin_accept_btn").text(law.cookieadmin_accept_btn);
		$(".cookieadmin_accept_btn").css('background-color', $("#cookieadmin_accept_btn_bg_color").val());
		$(".cookieadmin_accept_btn").css('color', $("#cookieadmin_accept_btn_color").val());

		$(".cookieadmin_save_btn").text(law.cookieadmin_save_btn);
		$(".cookieadmin_save_btn").css('background-color', $("#cookieadmin_save_btn_bg_color").val());
		$(".cookieadmin_save_btn").css('color', $("#cookieadmin_save_btn_color").val());

		$(".act").css('color', law.cookieadmin_links_color);

		const sliders = $("#cookieadmin_wrapper .cookieadmin_slider");
		$(sliders[0]).css('background-color', law.cookieadmin_slider_off_bg_color);
		$(sliders[1]).css('background-color', law.cookieadmin_slider_off_bg_color);
		$(sliders[2]).css('background-color', law.cookieadmin_slider_off_bg_color);

		//Also set layout of consents
		if(!!law.cookieadmin_position){
			$.each(law.cookieadmin_position.split("_"), function(i,v){
				$(".cookieadmin_law_container").addClass("cookieadmin_" + v);
			});
		}
		
		if(!!law.cookieadmin_layout){
			$.each(law.cookieadmin_layout.split("_"), function(i,v){
				$(".cookieadmin_law_container").addClass("cookieadmin_" + v);
			});
		}
		
		cookieadmin_policy['set'] == 'cookieadmin_gdpr' ? $(".setting-prior").show() : $(".setting-prior").hide();
		
		break;
	}
	
	function show_modal(){
		if($(".cookieadmin_cookie_modal").css("display") === "none"){
			$(".cookieadmin_cookie_modal").css("display", "flex");
		}
		else{
			$(".cookieadmin_cookie_modal").css("display", "none");
		}
	}
	
	function cookieadminShowPreview(){
		$('#cookieadmin_show_preview').toggle();
		$('.cookieadmin_law_container').toggle();
		$('#cookieadmin_hide_preview').toggle();
	}
	$('#cookieadmin_show_preview, #cookieadmin_hide_preview').on('click', cookieadminShowPreview);
	
	$('.cookieadmin_customize_btn').on('click', function(){
		show_modal();
	});
	
	$(".cookieadmin_close_pref").on("click", function(){
		$(".cookieadmin_cookie_modal").hide();
	});
	
	function cookieadminChangeLaw(){
		
		var law = $("#cookieadmin_consent_type").find(":selected").attr("name");
		
		$("#cookieadmin_consent_expiry").val(cookieadmin_policy[law].cookieadmin_days);
		
		$("[id^=cookieadmin_layout_]").prop("checked", false);
		$("#cookieadmin_layout_"+cookieadmin_policy[law].cookieadmin_layout).prop("checked", true);
		
		if(cookieadmin_policy[law].cookieadmin_layout == "box"){
			$(".cookieadmin_footer_layout").closest('label').hide();
			$(".cookieadmin_box_layout").closest('label').show();
		}else{
			$(".cookieadmin_footer_layout").closest('label').show();
			$(".cookieadmin_box_layout").closest('label').hide();
		}
		
		$("[id^=cookieadmin_position_]").prop("checked", false);
		$("#cookieadmin_position_"+cookieadmin_policy[law].cookieadmin_position).prop("checked", true);
		
		$("[id^=cookieadmin_modal_]").prop("checked", false);
		$("#cookieadmin_modal_"+cookieadmin_policy[law].cookieadmin_modal).prop("checked", true);
		
		$("#cookieadmin_notice_title_layout").val(cookieadmin_policy[law].cookieadmin_notice_title);
		$("#cookieadmin_notice_layout").val(cookieadmin_policy[law].cookieadmin_notice);

		$("#cookieadmin_notice_title_color_box").val(cookieadmin_policy[law].cookieadmin_notice_title_color);
		$("#cookieadmin_notice_title_color").val(cookieadmin_policy[law].cookieadmin_notice_title_color);
		$("#cookieadmin_notice_color_box").val(cookieadmin_policy[law].cookieadmin_notice_color);
		$("#cookieadmin_notice_color").val(cookieadmin_policy[law].cookieadmin_notice_color);
		$("#cookieadmin_consent_inside_bg_color_box").val(cookieadmin_policy[law].cookieadmin_consent_inside_bg_color);
		$("#cookieadmin_consent_inside_bg_color").val(cookieadmin_policy[law].cookieadmin_consent_inside_bg_color);
		$("#cookieadmin_consent_inside_border_color_box").val(cookieadmin_policy[law].cookieadmin_consent_inside_border_color);
		$("#cookieadmin_consent_inside_border_color").val(cookieadmin_policy[law].cookieadmin_consent_inside_border_color);

		$("#cookieadmin_customize_btn").val(cookieadmin_policy[law].cookieadmin_customize_btn);
		$("#cookieadmin_customize_btn_color_box").val(cookieadmin_policy[law].cookieadmin_customize_btn_color);
		$("#cookieadmin_customize_btn_color").val(cookieadmin_policy[law].cookieadmin_customize_btn_color);
		$("#cookieadmin_customize_btn_bg_color_box").val(cookieadmin_policy[law].cookieadmin_customize_btn_bg_color);		
		$("#cookieadmin_customize_btn_bg_color").val(cookieadmin_policy[law].cookieadmin_customize_btn_bg_color);

		$("#cookieadmin_reject_btn").val(cookieadmin_policy[law].cookieadmin_reject_btn);
		$("#cookieadmin_reject_btn_color_box").val(cookieadmin_policy[law].cookieadmin_reject_btn_color);
		$("#cookieadmin_reject_btn_color").val(cookieadmin_policy[law].cookieadmin_reject_btn_color);
		$("#cookieadmin_reject_btn_bg_color_box").val(cookieadmin_policy[law].cookieadmin_reject_btn_bg_color);
		$("#cookieadmin_reject_btn_bg_color").val(cookieadmin_policy[law].cookieadmin_reject_btn_bg_color);

		$("#cookieadmin_accept_btn").val(cookieadmin_policy[law].cookieadmin_accept_btn);
		$("#cookieadmin_accept_btn_color_box").val(cookieadmin_policy[law].cookieadmin_accept_btn_color);
		$("#cookieadmin_accept_btn_color").val(cookieadmin_policy[law].cookieadmin_accept_btn_color);
		$("#cookieadmin_accept_btn_bg_color_box").val(cookieadmin_policy[law].cookieadmin_accept_btn_bg_color);
		$("#cookieadmin_accept_btn_bg_color").val(cookieadmin_policy[law].cookieadmin_accept_btn_bg_color);

		$("#cookieadmin_save_btn").val(cookieadmin_policy[law].cookieadmin_save_btn);
		$("#cookieadmin_save_btn_color_box").val(cookieadmin_policy[law].cookieadmin_save_btn_color);
		$("#cookieadmin_save_btn_color").val(cookieadmin_policy[law].cookieadmin_save_btn_color);
		$("#cookieadmin_save_btn_bg_color_box").val(cookieadmin_policy[law].cookieadmin_save_btn_bg_color);
		$("#cookieadmin_save_btn_bg_color").val(cookieadmin_policy[law].cookieadmin_save_btn_bg_color);
		
		$("#cookieadmin_preference_title_layout").val(cookieadmin_policy[law].cookieadmin_preference_title);
		$("#cookieadmin_preference_layout").val(cookieadmin_policy[law].cookieadmin_preference);

		$("#cookieadmin_preference_title_color_box").val(cookieadmin_policy[law].cookieadmin_preference_title_color);
		$("#cookieadmin_preference_title_color").val(cookieadmin_policy[law].cookieadmin_preference_title_color);
		$("#cookieadmin_details_wrapper_color_box").val(cookieadmin_policy[law].cookieadmin_details_wrapper_color);
		$("#cookieadmin_details_wrapper_color").val(cookieadmin_policy[law].cookieadmin_details_wrapper_color);
		$("#cookieadmin_cookie_modal_bg_color_box").val(cookieadmin_policy[law].cookieadmin_cookie_modal_bg_color);
		$("#cookieadmin_cookie_modal_bg_color").val(cookieadmin_policy[law].cookieadmin_cookie_modal_bg_color);
		$("#cookieadmin_cookie_modal_border_color_box").val(cookieadmin_policy[law].cookieadmin_cookie_modal_border_color);
		$("#cookieadmin_cookie_modal_border_color").val(cookieadmin_policy[law].cookieadmin_cookie_modal_border_color);

		$("#cookieadmin_slider_on_bg_color_box").val(cookieadmin_policy[law].cookieadmin_slider_on_bg_color);
		$("#cookieadmin_slider_on_bg_color").val(cookieadmin_policy[law].cookieadmin_slider_on_bg_color);
		$("#cookieadmin_slider_off_bg_color_box").val(cookieadmin_policy[law].cookieadmin_slider_off_bg_color);
		$("#cookieadmin_slider_off_bg_color").val(cookieadmin_policy[law].cookieadmin_slider_off_bg_color);

		$("#cookieadmin_links_color_box").val(cookieadmin_policy[law].cookieadmin_links_color);
		$("#cookieadmin_links_color").val(cookieadmin_policy[law].cookieadmin_links_color);
		
		law == 'cookieadmin_gdpr' ? $(".setting-prior").show() : $(".setting-prior").hide();
	}
	$("#cookieadmin_consent_type").on("change", cookieadminChangeLaw);

	function cookieadminSubmitConsent(e){		
		var checked = false;
		
		if (!$("input[name=cookieadmin_position]:checked").length && !$("#cookieadmin_layout_popup").prop("checked")) {
			alert('Please select a notice position.');
			e.preventDefault(); // Prevent form submission
		}
	}
	$("#consent_submenu").submit(cookieadminSubmitConsent);
	
	function cookieadminPreloadCookies(){
		let notice = $('.cookieadmin-collapsible-notice');

		if($('[name="cookieadmin_preload[]"]:checked').length > 1){
			if(notice.css('display') == 'none'){
				notice.show();
			}
		} else {
			if(notice.css('display') !== 'none'){
				notice.hide();
			}
		}
	}
	$('[name="cookieadmin_preload[]"]').on('click', cookieadminPreloadCookies);
	
	function cookieadminScanCookies(){
		
		let cookieadmin_btn_val = $(this).prop('value');
		var this_btn = $(this);
		this_btn.prop("disabled", true).prop('value', 'Scanning...');
		
		$.ajax({
			url: cookieadmin_policy.admin_url,
			method: "POST",
			data : {
				action : 'cookieadmin_ajax_handler',
				cookieadmin_act : 'scan_cookies',
				cookieadmin_security : cookieadmin_policy.cookieadmin_nonce,
			},
			success: function(result){
				
				if(result.success){
					if(typeof cookieadmin_pro_policy === 'object'){
						alert(cookieadmin_pro_policy.lang.background_scan);
					}else{
						alert(cookieadmin_policy.lang.scan_completed);
						window.location.reload();
					}
				}else{
					alert(result.message);
				}
				
			},	
			error: function(xhr, status, error) {
				console.log('CookieAdmin AJAX Error:', status, error);
				console.log('CookieAdmin response: ' + xhr.responseText); // Check the error message
				alert("Request failed: " + error);
			},
			complete: function() {
				this_btn.prop("disabled", false).prop("value", cookieadmin_btn_val);
			}
		});
	}
	$(".cookieadmin-scan").on("click", cookieadminScanCookies);
	
	function cookieadminSaveCookie(){
		
		$('#cookieadmin-message').removeClass().addClass('spinner is-active').text('').css('height','auto');
		$(this).prop("disabled", true);
		
		//set 0 to add real id to delete
		cookie_id = $(this).attr("cookieadmin_cookie_id");
		
		let cookie_info = {
			name: $("#cookieadmin-dialog-cookie-name").val(),
			id: cookie_id,
			description: $("#cookieadmin-dialog-cookie-desc").val(),
			duration: $("#cookieadmin-dialog-cookie-duration").val(),
			type: $("#cookieadmin-dialog-cookie-category").val()
		};
		
		$.each(cookie_info, function (i, val){
			if(!val){
				cookie_info = null;
				return false;
			}
		});
    
		if(!cookie_info){
			$(this).prop("disabled", false);
			$('#cookieadmin-message').removeClass().addClass('cookieadmin-error-message').text('Please fill all the fields');
			return false;
		}
		
		// Used to call add cookie, in the Pro file.
		if(cookie_id == 0 && typeof cookieadminProSaveCookie === 'function'){
			cookieadminProSaveCookie(cookie_info);
			return;
		}
		
		$.ajax({
			url: cookieadmin_policy.admin_url,
			method: "POST",
			data : {
				action : 'cookieadmin_ajax_handler',
				cookieadmin_act : 'cookieadmin-edit-cookie',
				cookie_info : cookie_info,
				cookieadmin_security : cookieadmin_policy.cookieadmin_nonce,
			},
			success: function(result){
				
				if(result.success){
					cookieadminAddCookieToTable(cookie_info);
					$('#cookieadmin-message').removeClass().addClass('cookieadmin-success-message').text(result.message);
				}else{
					alert(result.message);
				}
			},
			error: function(xhr, status, error) {
				console.log('CookieAdmin AJAX Error:', status, error);
				console.log('CookieAdmin response: ' + xhr.responseText); // Check the error message
				alert("Request failed: " + error);
			}
		});
		$(this).prop("disabled", false);		
	}
	$("#cookieadmin_dialog_save_btn").on("click", cookieadminSaveCookie);
	
	$("input[type=color]").on("input", function(){
		elemt = $(this).attr("id").replace("_box", "");
		$("#"+elemt).val($(this).val());
		
		// Updating the background color of the reconsent icon list
		if(elemt === 'cookieadmin_re_consent_bg_color'){
			$('.cookieadmin-reconsent-icon').css('background-color', $(this).val());
		}
	});
	
	
	function cookieadminDeleteCookie(){
		
		if(confirm("Are you sure you want to delete ?")){
			
			cookie_raw_id = $(this).attr("id").replace("delete_", "");
			var row = $(this).parents("tr")
			
			$.ajax({
				url: cookieadmin_policy.admin_url,
				method: "POST",
				data : {
					action : 'cookieadmin_ajax_handler',
					cookieadmin_act : 'cookieadmin-delete-cookie',
					cookie_raw_id : cookie_raw_id,
					cookieadmin_security : cookieadmin_policy.cookieadmin_nonce,
				},
				success: function(result){
					if (result.success) {
						row.remove();
						delete categorized_cookies[cookie_raw_id];
					}else{
						alert(result.message);
					}
				},
				error: function(xhr, status, error) {
					console.log('CookieAdmin AJAX Error:', status, error);
					console.log('CookieAdmin response: ' + xhr.responseText); // Check the error message
					alert("Request failed: " + error);
				}
			});
		}
	}
	$('.cookieadmin-metabox-holder').on("click", ".cookieadmin_delete_icon", cookieadminDeleteCookie);
	
	function cookieadminOpenCookieDialog(){
		
		const modal = document.getElementById('edit-cookie-modal');
		
		if(modal.classList.contains("active")){
			modal.classList.remove('active');
		}else{
			modal.classList.add('active');
			
			if($(this).hasClass('cookieadmin-add-cookie')){
				$("#cookieadmin_dialog_save_btn").attr("cookieadmin_cookie_id", 0);
				$('.cookieadmin_modal-header').children()[0].innerHTML = 'Add Cookie';
				$('.cookieadmin_modal-container').find('input,textarea,select').val('');
			}else{
				
				$('.cookieadmin_modal-header').children()[0].innerHTML = 'Edit Cookie';
				var cookie_id = $(this).attr("id").replace("edit_", "");
				
				$("#cookieadmin_dialog_save_btn").attr("cookieadmin_cookie_id", cookie_id);
				$("#cookieadmin-dialog-cookie-name").val(categorized_cookies[cookie_id]['cookie_name']);
				$("#cookieadmin-dialog-cookie-desc").val(categorized_cookies[cookie_id]['description']);
				$("#cookieadmin-dialog-cookie-duration").val(categorized_cookies[cookie_id]['expires']);
				if(!!categorized_cookies[cookie_id]['category']){
					$("#cookieadmin-dialog-cookie-category").val(categorized_cookies[cookie_id]['category']);
				}else{
					$("#cookieadmin-dialog-cookie-category").val("unknown");
				}
			}
			
			$('.cookieadmin-metabox-holder').on('mousedown.cookieadmin-close-dialog2', function(e){
				if ( !$(".cookieadmin_modal-container").is(e.target) && $(".cookieadmin_modal-container").has(e.target).length === 0 && !$(".cookieadmin_modal-container").is(e.target) && $(".cookieadmin_modal-container").has(e.target).length === 0) {
					modal.classList.remove('active');
					$('#cookieadmin-message').removeClass().addClass('cookieadmin-success-message').text('');
					$('.cookieadmin-metabox-holder').off('mousedown.cookieadmin-close-dialog2');
				}
			});
		}		
	}
	$(document).on("click", ".cookieadmin_edit_icon, .cookieadmin_dialog_modal_close_btn, .cookieadmin-add-cookie", cookieadminOpenCookieDialog);
	
	
	function cookieadminExpandCollapseCookiesList() {
	  var $tbody = $(this).closest('tbody');
	  var $rows = $tbody.find('tr:not(:first-child)');

	  if ($rows.is(':visible')) {
  		$rows.slideUp();
  		$tbody.addClass('collapsed');
	  } else {
  		$rows.slideDown();
  		$tbody.removeClass('collapsed');
	  }
	}
	$('.cookieadmin-metabox-holder').on('click', '.cookieadmin-cookie-categorized tbody > tr:first-child', cookieadminExpandCollapseCookiesList);

	function cookieadminUploadReconsentIcon(e){

		e.preventDefault();

		if(mediaUploader){
            mediaUploader.open();
            return;
    }

		mediaUploader = wp.media({
            title: 'Select or Upload Icon',
            button: {text: 'Use this icon'},
            multiple: false,
            library: {type: 'image'}
        });

		mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#cookieadmin_reconsent_img_url').val(attachment.url);
        });

        mediaUploader.open();
		
	}
	$('.cookieadmin-metabox-holder').on('click', '#cookieadmin_upload_icon_btn', cookieadminUploadReconsentIcon);
	
	// Content Blocking Toggle
	$("#cookieadmin_content_blocking").on("change", function(){
		if($(this).prop("checked")){
			$(".cookieadmin-content-blocking-options").fadeIn(250);
		}else{
			$(".cookieadmin-content-blocking-options").fadeOut(250);
		}
	});
	
	// Tooltip
	let cookieadminToolTip;
	
	function cookieadminCreateTooltip(){
		cookieadminToolTip = $('<div>', {
			class: 'cookieadmin-tooltip'
		}).appendTo('body');
	}

	$('.cookieadmin-tooltip-box').on('mouseenter', function(e){
		
		if(!cookieadminToolTip){
			cookieadminCreateTooltip();
		}
		
		var tip = $(this).attr('data-tip') || '';
		cookieadminToolTip.text(tip).css('opacity', 1);
		
	}).on('mousemove', function(e){
		
		if(!cookieadminToolTip){
			return;
		}
		
		var offset = 12;
		var tooltipRect = cookieadminToolTip[0].getBoundingClientRect();
		
		var top = e.clientY - tooltipRect.height - offset;
		var left = e.clientX + offset;
		
		// Flip below if not enough space above
		if(top < 8){
			top = e.clientY + offset;
		}
		
		// Prevent right overflow
		if(left + tooltipRect.width > window.innerWidth - 8){
			left = e.clientX - tooltipRect.width - offset;
		}
		
		cookieadminToolTip.css({
			top: top + 'px',
			left: left + 'px'
		});
	})
	.on('mouseleave', function(){

		if(!cookieadminToolTip){
			return;
		}
		
		cookieadminToolTip.css('opacity', 0);
	});
	
	// Recommended plugins install/activate handler
	$(document).on('click', '.cookieadmin-plugin-install-btn, .cookieadmin-plugin-activate-btn', function(e){
		e.preventDefault();
		var $btn = $(this);
		var slug = $btn.data('slug');
		var is_install = $btn.hasClass('cookieadmin-plugin-install-btn');
		var originalText = $btn.text();
		
		$btn.text(cookieadmin_policy.lang.processing).prop('disabled', true);
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cookieadmin_ajax_handler',
				cookieadmin_act: is_install ? 'install_recommended_plugin' : 'activate_recommended_plugin',
				cookieadmin_security: cookieadmin_policy.cookieadmin_nonce,
				plugin: slug,
			},
			dataType: 'json',
			success: function(data){
				if(data.success){
					$btn.replaceWith('<span class="cookieadmin-badge cookieadmin-success">' + cookieadmin_policy.lang.active + '</span>');
				}else{
					$btn.text(originalText).prop('disabled', false);
					alert(data.data && data.data.message ? data.data.message : cookieadmin_policy.lang.install_failed);
				}
			},
			error: function(){
				$btn.text(originalText).prop('disabled', false);
				alert(cookieadmin_policy.lang.error_occurred);
			},
		});
	});
	
	
});

function cookieadminAddCookieToTable(cookie_info){

	var row = '';

	categorized_cookies[cookie_info.id] = !categorized_cookies[cookie_info.id] ? {} : categorized_cookies[cookie_info.id];

	categorized_cookies[cookie_info.id]['id'] = cookie_info.id;
	categorized_cookies[cookie_info.id]['cookie_name'] = cookie_info.name;
	categorized_cookies[cookie_info.id]['description'] = cookie_info.description;
	categorized_cookies[cookie_info.id]['expires'] = cookie_info.duration;
	categorized_cookies[cookie_info.id]['category'] = cookie_info.type;
	
	if(cookie_info.duration > 0){
		cookie_info.duration += ' ' + cookieadmin_policy.lang.days;
	}else{
		cookie_info.duration = cookieadmin_policy.lang.session;
	}
	
	row = '<tr>';
	row += '<td>' + cookie_info.name + '</td>';
	row += '<td>' + cookie_info.description + '</td>';
	row += '<td>' + cookie_info.duration + '</td>';
	row += '<td> <span class="dashicons dashicons-edit cookieadmin_edit_icon" id="edit_'+cookie_info.id+'"></span> <span class="dashicons dashicons-trash cookieadmin_delete_icon" id="edit_'+cookie_info.id+'"></span> </td>';
	row += '</tr>';
	
	jQuery("#edit_"+cookie_info.id).parents("tr").remove();
	tbody = "#" + cookie_info.type.toLowerCase() + "_tbody";
	jQuery(tbody).find('.cookieadmin-empty-row').remove();
	jQuery(tbody).append(row);
	
	if(!categorized_cookies[cookie_id]){
		jQuery('.cookieadmin_modal-container').find('input,textarea,select').val('');
	}
}
