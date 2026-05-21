var softaculous_pro_show_themes_loaded = 0;
var softaculous_image_loading_page = 1; 
var softaculous_next_step_loading = false;
var softaculous_pro_onboarding_errors = false;

var softaculous_pro_stop_progress = false;
var softaculous_pro_last_progress_cache = null;
var softaculous_pro_last_req_time = 0;
var softaculous_pro_ajax_progress_var = 0;

jQuery(document).ready(function(){
	
	let url = new URL(window.location.href);
	let step = url.searchParams.get('step');
	
	if(step == 'start' || !step){
		softaculous_pro_set_mode();
	}
	
	if(step === 'import_theme'){
		jQuery('.softaculous-pro-wizard-sidebar').addClass('compact');
		jQuery('.softaculous-pro-setup-logo').hide();
		jQuery('.active_step').find('span').css('min-width', '0');
		
		softaculous_pro_the_themes_window();
	}
	
	var feature_holder = jQuery('.softaculous-pro-features');
	feature_holder.each(function(){
		var feature_checked = jQuery(this).find('input').is(":checked");
		if(feature_checked){
			jQuery(this).addClass("feature-border");
		}
	});
	
	feature_holder.click(function(){
		var feature_disabled = jQuery(this).find('input').is(":disabled");
		if(feature_disabled && !jQuery(this).hasClass("spro-erro")){
			var selected_erro = jQuery(this).find('input').attr('spro-erro');
			const para = jQuery('<p>'+selected_erro+'</p>').css('color', 'red');
			jQuery(this).find('.softaculous-pro-features-text').find('p').replaceWith(para);
			jQuery(this).addClass("spro-erro");
		}
	});
	
	if(jQuery('.softaculous-pro-wizard').length > 0){
		window.addEventListener('popstate', function(event){
			// Get the current URL
			let url = new URL(window.location.href);
			let step = url.searchParams.get('step');
			
			if(step){
				softaculous_pro_set_active_step(step);
			}

			// When using forward or back button in browser
			if(step === 'images'){
				// This will ensure that the theme is always loaded when coming from the images
				softaculous_pro_show_themes_loaded = 0;
				jQuery('.softaculous-switch-template-mode .active_mode').removeClass('active_mode');
				if(softaculous_ai_config.suggested_tags.length > 0){
					jQuery('.softaculous-search-images').val(softaculous_ai_config.suggested_tags[0]).trigger('input');
				}
			}
			
			if(step === 'import_theme'){
				softaculous_pro_the_themes_window();
			}
			
		});
	}

	// Ensure that it should also work in js
	jQuery(".softaculous-pro-wizard-steps a").each(function () {
		const stepOrder = ["start", "contact", "features", "images", "import_theme"];
		let click_ele = jQuery(this);
	
		click_ele.on("click", function (e) {
			let description = jQuery('#softaculous-business-desc').val();
			const click_step = click_ele.children("span").data("step");
			if (description.length < 300) {
				e.preventDefault();
			}
		});
	});

	jQuery(document).on('click', '.softaculous-alert-dismissable', function(){
		jQuery(this).parent().remove();
	})

	softaculous_pro_ai_start_handler();
	softaculous_pro_ai_image_handler(step);
	softaculous_pro_ai_description_handler();
	
	// Onboarding template js
	softaculous_pro_templates_fn(jQuery);
	
	if(softaculous_pro_setup_info && softaculous_pro_setup_info.type){
		softaculous_pro_setup_info.type = softaculous_pro_setup_info.type.replace("-", "");
		jQuery('#cat_button_'+softaculous_pro_setup_info.type).trigger("click");
	}
	
	jQuery(window).on('resize', function(){
		if( !('is_maunal' in softaculous_pro_setup_info) || softaculous_pro_setup_info['is_maunal'] == false){
			softaculous_pro_scaleIframe();
			softaculous_pro_scaleSingleTemplate();
		}
	});

	jQuery('.softaculous-switch-template-mode div').on('click', function(){
		var ele = jQuery(this);
		var mode = ele.data('mode');
		
		if(ele.hasClass('active_mode')){
			return;
		}

		jQuery('.active_mode').removeClass('active_mode');
		ele.addClass('active_mode');

		if(mode == 'manual'){
			jQuery('#softaculous-pro-templates').attr('data-type', 'manual').empty();	
			softaculous_ai_config.template_mode = 'manual';
		}else{
			jQuery('#softaculous-pro-templates').attr('data-type', 'ai').empty();
			softaculous_ai_config.template_mode = 'ai';
		}

		jQuery.ajax({
			type: 'post',
			url: soft_pro_obj.ajax_url,
			data: {
				action: 'softaculous_switch_template_mode',
				mode: mode,
				softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			}
		});
		
		softaculous_pro_the_themes_window();

	})

	jQuery('.softaculous-pro-add-social').on('click', function(){
		jQuery(this).siblings('.softaculous-pro-social-links-profile').toggle();
	})

	softaculous_pro_import_scroll();
});

function softaculous_pro_set_mode(){
	var ai_desc = jQuery("#softaculous-ai-description-field");
	
	softaculous_ai_config.template_mode = jQuery('#softaculous-ai-mode').is(':checked') ? 'ai' : 'manual';
	
	if(softaculous_ai_config.template_mode == 'manual'){
		ai_desc.hide();
	}else{
		ai_desc.show();
	}
}

function softaculous_pro_the_themes_window(){
	
	if(!softaculous_pro_show_themes_loaded){
		if(softaculous_ai_config.template_mode == 'manual'){
			//jQuery('.softaculous-switch-template-mode div[data-mode="manual"]').trigger('click');
			softaculous_pro_show_manual_themes();
		}else{
			//jQuery('.softaculous-switch-template-mode div[data-mode="ai"]').trigger('click');
			softaculous_pro_show_themes();
		}
	}
	softaculous_pro_import_scroll();
}

function softaculous_pro_set_active_step(step) {
	
	// Active Tab
	jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');

	if(step ==='import_theme'){
		jQuery('.softaculous-pro-steps-holder ol li [data-step="'+step+'"]').closest('li').addClass('active_step');
		jQuery('.softaculous-pro-wizard-sidebar').addClass('compact');
		jQuery('.softaculous-pro-setup-logo').hide();
		jQuery('.active_step').find('span').css('min-width', '0');
	}else{
		jQuery('.softaculous-pro-wizard-sidebar').removeClass('compact');
		jQuery('.softaculous-pro-setup-logo').show();
		jQuery('.softaculous-pro-steps-holder ol li [data-step="'+step+'"]').closest('li').addClass('active_step');

	}
	
	// Active Panel
	jQuery('.softaculous-pro-wizard .softaculous-pro-wizard-content').attr('data-active-panel', step);
}

function softaculous_pro_next_handler(element){ 

	jQuery('.softaculous-pro-error-incounter').remove();
	softaculous_next_step_loading = true;
	jQuery('.next-handler-icon').hide();
	jQuery('.softaculous-next-loading').show();
  
	jQuery(element).siblings('.step_prev_btn').css({
		'pointer-events': 'none'
	})

	var ele = jQuery(element);
	var nextStep = ele.data('step');	
	var currentStep = ele.closest('[data-panel]').data('panel');
	var post_data = null;	
	
	if(softaculous_ai_config.template_mode == 'manual' && nextStep == 'images'){
		nextStep = 'import_theme';
	}

	if(currentStep === 'start'){

		if(jQuery("#onboarding_done_confirm").length > 0 && !jQuery("#onboarding_done_confirm").is(":checked")){
			jQuery('.softaculous-next-loading').hide();
			jQuery('.next-handler-icon').show();
			alert(softwp_onboarding_lang.conf_data_loss);
			return false;
		}

		var business_lang = jQuery('.softaculous-content #locale').val();		
		let { descriptions, desc_active } = softaculous_ai_config;
		var description = descriptions[desc_active];

		if(softaculous_ai_config.template_mode == 'ai' && description.trim().length < 200){
			softaculous_next_step_loading = false;
			jQuery('.softaculous-pro-description-error').show();
			jQuery('.softaculous-next-loading').hide();
			jQuery('.next-handler-icon').show();
			jQuery(`[data-panel=${currentStep}]`).find('.step_prev_btn').css({
				'pointer-events': 'all'
			})
			//jQuery(element).attr('disabled', 'disabled');
			return;
		}
		
		jQuery('.softaculous-pro-description-error').hide();
		//jQuery(element).removeAttr('disabled');
		
		post_data = {
			business_title: jQuery('#softaculous-site-name').val(),
			business_type: jQuery('#softaculous-business-type').val(),
			mode: softaculous_ai_config.template_mode,
			business_lang: business_lang.length === 0 ? 'en' : business_lang,
			active_desc: desc_active,
			business_description: softaculous_ai_config.template_mode == 'ai' ? descriptions : []
		};
	}

	if(currentStep  === 'contact'){
		var email = jQuery('#softaculous-site-email').val();
		var phone = jQuery('#softaculous-site-phone').val();
		var address = jQuery('#softaculous-site-address').val();
	
		// Social links
		var socials = ["facebook", "twitter", "instagram", "linkedin", "youtube"];
		post_data = {
			business_email: email,
			business_phone: phone,
			business_address: address
		};

		// Loop through socials
		socials.forEach(function(social) {
			post_data["business_social_" + social] = jQuery("#softaculous-site-social-" + social).val();
		});

	}

	if(currentStep === 'features'){
		var selected_feats = [];
		var feature_holder = jQuery('.softaculous-pro-features');
		
		feature_holder.each(function(){
			var feature_checked = jQuery(this).find('input').is(":checked");
			if(feature_checked){
				var selected_feat = jQuery(this).attr('data-slug');
				selected_feats.push(selected_feat);
			}
		});
		post_data = {features: selected_feats, step: currentStep};
	}

	if(currentStep == 'images'){
		softaculous_pro_show_themes_loaded = 0
		jQuery('.softaculous-switch-template-mode .active_mode').removeClass('active_mode');
		post_data = {selected_images : softaculous_ai_config.selected_images}
	}

	if(nextStep === 'images'){
		var description = jQuery('#softaculous-business-desc').val();	

		// Do this according to the active_description
		if(description.length < 200){
			jQuery('.softaculous-ai-warning').show();
			return;
		}else{
			jQuery('.softaculous-ai-warning').hide();
		}

		softaculous_pro_get_specific_info('image_suggestions');

		if(softaculous_ai_config.suggested_tags.length > 0){
			jQuery('.softaculous-search-images').val(softaculous_ai_config.suggested_tags[0]).trigger('input');
		}
	}

	// TODO go to next on success
	// Save the current URL
	if(post_data){
		jQuery.ajax({
			type: 'post',
			url: soft_pro_obj.ajax_url,
			data: {
				action: 'softaculous_pro_setup_info',
				step: currentStep,
				softaculous_pro_nonce: softaculous_pro_ajax_nonce,
				data: post_data,
			},
			success: function (response) {
				
				var res = JSON.parse(response);
				
				if('success' in res){
					
					let currentUrl = window.location.href;
					// Get the current URL
					let url = new URL(currentUrl);

					// Add a query parameter
					url.searchParams.set('step', nextStep);
					// Replace the current URL without refreshing the page
					window.history.pushState({ path: currentUrl }, '', url);
					softaculous_pro_set_active_step(nextStep);
					jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');
					jQuery('.softaculous-pro-steps-holder ol li [data-step="'+nextStep+'"]').closest('li').addClass('active_step');
					jQuery('.next-handler-icon').show();
					jQuery('.softaculous-next-loading').hide();
					softaculous_next_step_loading = false;
          
					if(nextStep === 'import_theme'){
						if('setup_info' in res){
							softaculous_pro_setup_info['theme_pid'] = res['setup_info']['theme_pid'];
						}
						softaculous_pro_the_themes_window();
					}
					
					// Cache the templates: Only hit the URLs
					if ('preview_urls' in res.setup_info && typeof res.setup_info.preview_urls === 'object') {
						jQuery.ajax({
							type: 'post',
							url: soft_pro_obj.ajax_url,
							timeout: 5000, // 5 seconds timeout
							data: {
								action: 'softaculous_pro_cache_iframe_urls',
								softaculous_pro_nonce: softaculous_pro_ajax_nonce,
								urls: res.setup_info.preview_urls,
							},
							success: function (response) {
								console.log('Iframe cache URLs sent successfully');
							},
							error: function (xhr) {
								console.log('AJAX error:', xhr.responseText);
							}
						});
					}
				}else{
					softaculous_next_step_loading = false;
					jQuery('.softaculous-next-loading').hide();
					jQuery('.next-handler-icon').show();
					jQuery(`[data-panel=${currentStep}]`).find('.step_prev_btn').css({
						'pointer-events': 'all'
					})
					softaculous_pro_error_html(res?.error || undefined , currentStep)
				}
			},
			error:function (response){
				softaculous_next_step_loading = false;
				jQuery('.softaculous-next-loading').hide();
				jQuery('.next-handler-icon').show();
				jQuery(`[data-panel=${currentStep}]`).find('.step_prev_btn').css({
					'pointer-events': 'all'
				})
				softaculous_pro_error_html(response?.statusText || undefined , currentStep)
			}
		});
	}
}

var softaculous_pro_cache_stream_iframes = {}
function softaculous_pro_stream_iframes(preview_urls, pid, element) {
	
	if(typeof preview_urls === 'object' && Object.keys(preview_urls).length < 1){
		return;
	}
	
	const formData = new FormData();
	formData.append('action', 'softaculous_pro_stream_iframes');
	formData.append('softaculous_pro_nonce', softaculous_pro_ajax_nonce);
	formData.append('urls', JSON.stringify(preview_urls)); // ensure it's a string

	fetch(soft_pro_obj.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.body.getReader())
	.then(reader => {
		const decoder = new TextDecoder();
		let buffer = '';

		function processChunk(text) {
			buffer += text;
			const lines = buffer.split('\n');
			buffer = lines.pop(); // keep the last partial line

			for (const line of lines) {
				if (line.startsWith('DATA::')) {
					const data = JSON.parse(line.replace('DATA::', ''));
					
					// console.log(data);
					if('error' in data){
						console.log('Error Loading demos' + data.error);
						return;
					}
					
					// console.log(data);
					// Create blob for iframe src
					const blob = new Blob([data.html], { type: 'text/html' });
					const blobUrl = URL.createObjectURL(blob);
					const slug = data.slug;
					
					if(!(pid in softaculous_pro_cache_stream_iframes)){
						softaculous_pro_cache_stream_iframes[pid] = {};
					}
					
					if(!(slug in softaculous_pro_cache_stream_iframes[pid])){
						softaculous_pro_cache_stream_iframes[pid][slug] = {};
					}
					
					softaculous_pro_cache_stream_iframes[pid][slug][data.page] = blobUrl;
					
					// Also support for pages
					var iframe = jQuery(element).find(`iframe[data-slug="${slug}"][data-page="${data.page}"]`);
					
					if(iframe.length < 1){
						iframe = jQuery(element).find(`iframe[data-slug="${slug}"]`);
					}
					
					iframe.attr('src', blobUrl);
					iframe.closest('.softaculous-pro-theme-details').find('a.softaculous-pro-ai-show-demo').attr('href', blobUrl);
				}
			}
		}

		function read() {
			reader.read().then(({ done, value }) => {
				if (done) return;
				processChunk(decoder.decode(value, { stream: true }));
				read();
			});
		}

		read();
	});
}

function softaculous_pro_prev_handler(element) {
	var ele = jQuery(element);
	var currentStep = ele.closest('[data-panel]').data('panel');
	var prevStep = ele.data('step');
	let currentUrl = window.location.href;
	let url = new URL(currentUrl);
	
	if(!softaculous_next_step_loading) {
		jQuery(`[data-panel=${prevStep}]`).find('.step_prev_btn').css({
			'pointer-events': 'all'
		})
	}

	url.searchParams.set('step', prevStep);
	window.history.pushState({ path: currentUrl }, '', url);
	softaculous_pro_set_active_step(prevStep);
	jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');
	jQuery('.softaculous-pro-steps-holder ol li [data-step="'+prevStep+'"]').closest('li').addClass('active_step');

}

function softaculous_pro_selected_features(element) {
	var ele_parent = jQuery(element).parent().parent();
	if(jQuery(element).is(":checked")){
		ele_parent.addClass("feature-border");
	}else{
		ele_parent.removeClass("feature-border");
	}
}

function softaculous_pro_modal(sel, ai_installation = false){
	
	var page_names = [];
	
	jQuery('.softaculous_pro_img_screen').each(function() {
		
		var is_selected = jQuery(this).find('input').is(':checked');
		if(is_selected){
			var page_name = jQuery(this).attr('page-name');
		}
		
		page_names.push(page_name);
	});
	
	page_names.forEach(function(value) {
		var input = jQuery('<input>').attr('type', 'hidden').attr('name', 'to_import[]').val(value);
		jQuery('#softaculous-pro-import-form').append(input);
	});
	
	if(page_names.length == 0){
		alert(softwp_onboarding_lang.select_atleast_one);
	} else{
		jQuery(".softaculous-pro-wizard-inner[data-panel='import_theme']").hide();
		var modal = jQuery(sel);
		modal.show();
		modal.find('.softaculous-pro-done').hide();
		modal.find('.softaculous-pro-import').show();

		var spro_temp_form_data = jQuery('#softaculous-pro-import-form').serialize();
		softaculous_pro_handle_templates(spro_temp_form_data, ai_installation);
	}
	
}

function softaculous_pro_handle_templates(spro_temp_form_data, ai_installation = false ){
	jQuery('#softaculous-pro-templates-holder').remove();
	jQuery('#SproTemplatesModal').hide();
	
	var message = softwp_onboarding_lang.checkRequirements;
	
	softaculous_pro_create_html(message, 10, true); // Start progress at 1%
	
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_start_install_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce,
		success: function(response){
			if(!response.success && response.data && response.data.form){
				softaculous_handle_ftp_form(response.data.form, spro_temp_form_data);
				return;
			}

			// Install plugin gives too much output, hence match the data
			var data = response.match(/<softaculous\-pro\-xmlwrap>(.*?)<\/softaculous\-pro\-xmlwrap>/is);
			
			if(data){
				data = data[1];
			}
			data = JSON.parse(data);
			softaculous_pro_selected_plugin(data, spro_temp_form_data, ai_installation);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
}

function softaculous_pro_selected_plugin(data, spro_temp_form_data, ai_installation=false){
	
	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	var message = softwp_onboarding_lang.downloading_installing_plugins;
	softaculous_pro_onboarding_progress();
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_selected_plugin_install',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce+'&ai_installation='+ai_installation,
		dataType: 'json',
		success: function(data){
			softaculous_pro_download_template(data, spro_temp_form_data, ai_installation);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	})
}

function softaculous_pro_download_template(data, spro_temp_form_data, ai_installation=false){
	
	var failed = [];
	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	if(typeof data === 'object' && 'failed_plugin' in data){
		failed.push(data['failed_plugin']);
	}
	var message = softwp_onboarding_lang.downloading_template;
	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_download_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce + '&ai_installation='+ ai_installation,
		dataType: 'json',
		success: function(data){
			data.failed_plugin = failed;
			softaculous_pro_import_template(data, spro_temp_form_data, ai_installation);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
  
}

function softaculous_handle_ftp_form(form, form_data){
	// Handling FTP Form
	jQuery('body').append(form);

	var ftp_modal = jQuery('#request-filesystem-credentials-dialog');
	ftp_modal.show();

	// Handling the close btn of the FTP form.
	ftp_modal.find('.cancel-button').on('click', function(event){
	event.preventDefault();
		ftp_modal.hide();
		alert(softwp_onboarding_lang.wordpress_require_ftp);
	});

	ftp_modal.on('submit', 'form', function(event){
		event.preventDefault();

		let serialized_data = jQuery(event.target).serialize();
		form_data += '&'+serialized_data;
		ftp_modal.hide();
		softaculous_pro_handle_templates(form_data);
	});
}

// Generate post
function softaculous_pro_generate_post(){
	
	var post_data = {
		site_name : jQuery('#softaculous-site-name').val(),
		description : jQuery('#softaculous-business-desc').val(),
	}
	
	jQuery.ajax({
		type: 'post',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_generate_post',
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			data: post_data,
		},
		success: function(response) {
			// console.log(response);
		}
		
	});
  
}

// Import template
function softaculous_pro_import_template(data, spro_temp_form_data, ai_installation= false){

	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	
	var message = softwp_onboarding_lang.importTemplate;
	
	if(ai_installation){
		softaculous_pro_generate_post();
	}
	
	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_import_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce + '&ai_installation='+ ai_installation,
		dataType: 'json',
		success: function(data){
			var modal = jQuery('.progress-bar');
			if(typeof data === 'object' && 'error' in data){
				softaculous_pro_show_error(data['error']);
				return false;
			}
	
			if(typeof data === 'object' && 'done' in data){
				console.log('You are all set');
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
  
}

function softaculous_pro_show_error(err){
  
  // Stop the ajax for progress
	softaculous_pro_stop_progress_calls();

	var html = '<div class="setup-error"><div class="setup-error-message"><span class="dashicons dashicons-info-outline"></span><h1>Error</h1></div><ul>';

	for(var x in err){
		html += '<li>'+err[x]+'</li>';
	}

	html += '</ul></div>';

	jQuery('.softaculous-pro-wizard-content').append(html);
	jQuery('#softaculous-pro-error-template').html(html).show();
	jQuery('.progress-bar').hide();

}

function softaculous_pro_create_html(message, finalPercentage, slowAnimation, logs) {
	
	// TODO : Call an AJAX to get the progress
	
	// Check if progress bar already exists
	var progressBar = jQuery('.progress-bar');
	if (progressBar.length > 0) {
		// Update the message and animate the progress
		jQuery('.progress-indicator').text(message);
		softaculous_pro_animateProgress(progressBar.find('.progress-float-r'), progressBar.find('.setup-progress-counter'), finalPercentage, slowAnimation);
	} else {
		// Create the progress bar
		var html = `<div class="progress-bar">
			<h1 class="progress-text">${softwp_onboarding_lang.buildWebsite}</h1>
			<div class="setup-progress-bar">
				<span class="progress-indicator">${message}</span>
				<span class="progress-float-r">0%</span>
				<div class="progress-bar-par">
					<div class="setup-progress-counter" style="width: 0%; background-color: blue"></div>
				</div>
				<div class="skeleton-loader" style="display:none;">
					<div class="skeleton-loader-shadow"></div>
					<div class="skeleton-row">
						<div class="skeleton-row-heading" style="width:50px; height:50px;border-radius:100%;"></div>
						<div class="skeleton-row-para" style="width:70%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-img" style="width:100%;"></div>
					</div>
					<div class="skeleton-row">
						<div class="skeleton-row-heading" style="width:50px; height:50px;border-radius:100%;"></div>
						<div class="skeleton-row-para" style="width:70%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-img"  style="width:100%;"></div>
						</div>
					</div>
				</div>					
				<div class="softaculous-pro-done" style="display: none">
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.site_url}" target="_blank"
				>Visit Website</a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.admin_url}" target="_blank"
				>WordPress Dashboard</a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.admin_url}admin.php?page=assistant" target="_blank"
				>Assistant</a>
			</div>
		</div>
		<div class="spro-setup-progress-logs">
			<b> Some Error Occurred: </b>
			<ul class="failed-progress-logs">
			</ul>
		</div>`;
		
		jQuery('.softaculous-pro-wizard-content').append(html);
		softaculous_pro_animateProgress(jQuery('.progress-float-r'), jQuery('.setup-progress-counter'), finalPercentage, slowAnimation);
	}
	
	if(logs && typeof ele === 'object'){
		jQuery('.spro-setup-progress-logs').show();
		Object.entries(logs[0]).map(entry => {
			jQuery('.failed-progress-logs').append('<li class="spro-failed-ins-li">'+ entry[1] + '</li>');
		});
	}
}
	
var softProAnimProgressInterval = {};
function softaculous_pro_animateProgress($progressText, $progressBar, finalPercentage, slowAnimation) {
	var currentPercentage = parseInt($progressText.text());
	var increment = 1; 
	var duration = 10;
	
	if (slowAnimation) {
		duration = 100;
	}
	
	clearInterval(softProAnimProgressInterval);
	softProAnimProgressInterval = setInterval(function() {
		if (currentPercentage >= finalPercentage) {  
			clearInterval(softProAnimProgressInterval);
		} else {
			currentPercentage += increment;
			$progressText.text(currentPercentage + '%');
			$progressBar.animate({ width: currentPercentage + '%' }, duration).css('background-color', 'blue');
		}
		
	}, duration);
}

// Start step handler
function softaculous_pro_ai_start_handler(){

	jQuery('#softaculous-business-type').on('focus', function() {
		jQuery('.softaculous-category-holder').show();
		var generated_list = jQuery('.softaculous-pro-categories-dropdownlist');
    
		if(jQuery(this).val().trim().length < 1){
			jQuery('.softaculous-pro-categories-dropdownlist').html('');
			jQuery('.softaculous-pro-categories-default-dropdownlist').show();
		}else if(jQuery(this).val().trim().length > 3 && generated_list.is(':empty')){
			softaculous_pro_render_autocomplete(jQuery(this).val().trim());
		}
	});
  
  var typeTimer;
  
	jQuery('#softaculous-business-type').on('keyup', function() {
		var search = jQuery(this).val().toLowerCase();
    if(search.length > 3){

			clearTimeout(typeTimer);

			typeTimer = setTimeout(function() {
				softaculous_pro_render_autocomplete(search);
			}, 200);

			jQuery('.clear-btn').show();
			jQuery('.softaculous-pro-categories-default-dropdownlist').hide();

		}else{	
			jQuery('.softaculous-pro-categories-dropdownlist').html('');
			jQuery('.softaculous-pro-categories-default-dropdownlist').show();
			jQuery('.clear-btn').hide();
		}
	});

	jQuery(document).on('click.soft_category_btn', '.softaculous-category_btn', function() {
		let selectedText = jQuery(this).text().trim();
		jQuery('#softaculous-business-type').val(selectedText);
		jQuery('.softaculous-category-holder').hide();
		jQuery('.clear-btn').show();
	});

	jQuery('.clear-btn').on('click', function() {
		jQuery('#softaculous-business-type').val('');
		jQuery('.clear-btn').hide();
		jQuery('.softaculous-category-holder').show();
		jQuery('.softaculous-pro-categories-default-dropdownlist').show();
	});
	
	jQuery(document).on('click', function(e) {
		if (!jQuery(e.target).closest('.softaculous-dropdown').length) {
			jQuery('.softaculous-category-holder').hide();
		}
	});

}

// Image step handler
function softaculous_pro_ai_image_handler(step){
	
	var imgHolder = jQuery('.softaculous-pro-wizard-inner[data-panel="images"]');
	
	if(softaculous_ai_config.suggested_tags.length > 0 && step === 'images'){
		let imageInterval = setInterval(() => {
			let image_input = jQuery('.softaculous-search-images');
			if (image_input.length) {
				image_input.val(softaculous_ai_config.suggested_tags[0]).trigger('input');
				clearInterval(imageInterval);
			}
		}, 100);
	}

	jQuery('.softaculous-ai-images-suggestions button').on('click', function(){
		var bEle = jQuery(this);
		
		if(bEle.hasClass('softaculous-upload-image') || bEle.hasClass('active')){
			return;
		}
		
		bEle.parent().find('.active').removeClass('active');
		bEle.addClass('active');
		jQuery('.softaculous-ai-images-rows, .softaculous-selected-images-rows').removeClass('softaculous-active-section');

		if(bEle.data('slug') === 'selected_image'){
			jQuery('.softaculous-selected-images-rows').addClass('softaculous-active-section');
		}else{
			jQuery('.softaculous-ai-images-rows').addClass('softaculous-active-section');
		}

	});
	
	imgHolder.on('click', '.softaculous-ai-single-image', function(){
		var imgEle = jQuery(this);
		var img_id = String(imgEle.data('id')); // Ensure it's a string
		var isChecked = imgEle.find('.softaculous-ai-checkbox').prop('checked');
		var image_url = imgEle.find('img').attr('src');

		if(!isChecked){
			imgEle.removeClass('selected');
			softaculous_ai_config.selected_images = softaculous_ai_config.selected_images.filter(img => String(img.image_id) !== img_id);
			softaculous_pro_update_image_count();

		} else {
			imgEle.addClass('selected');
			var img_obj = { image_id: img_id, image_url: image_url };
			softaculous_ai_config.selected_images = softaculous_ai_config.selected_images.filter(img => String(img.image_id) !== img_id);
			softaculous_ai_config.selected_images.push(img_obj);
			softaculous_pro_update_image_count();
		}

		softaculous_pro_update_images();
	});
	
	// Upload images
	jQuery(".softaculous-upload-image").click(function (e) {
		var par = jQuery(this).parent();
		e.preventDefault();

		var mediaUploader = wp.media({
			title: "Select Image",
			button: {
				text: "Use this Image"
			},
			multiple: false
		});

		mediaUploader.on("select", function () {
			var attachment = mediaUploader.state().get("selection").first().toJSON();
			var image_id = String(attachment.id);
			var image_url = attachment.url;
			var img_obj = {image_id: image_id , image_url: image_url};
			
			softaculous_ai_config.selected_images.push(img_obj);
			softaculous_pro_update_images();
			softaculous_pro_update_image_count();
			
			jQuery('.softaculous-selected-images-rows').prepend(softaculous_image_upload_message());
			jQuery('.softaculous-ai-images-suggestions [data-slug="selected_image"]').click();

			setTimeout(function () {
				jQuery('.softaculous-pro-image-success').fadeOut(500, function () { jQuery(this).remove(); });
			}, 4000);
		});

		mediaUploader.open();
	});

	imgHolder.on('click', '.softaculous-remove-selected', function(){
		var element = jQuery(this);
		var img_element = element.siblings('img');
		
		var image_id = img_element.attr('data-selected-id');
		var ai_images = jQuery('.softaculous-ai-images-rows');

		softaculous_ai_config.selected_images = softaculous_ai_config.selected_images.filter(function(img) {
			return !img.image_id || img.image_id != image_id;
		});
		
		softaculous_pro_update_image_count();
		element.closest('.softaculous-selected-image').remove();

		ai_images.find('label.softaculous-ai-single-image').each(function() {
			var element_id = jQuery(this).data('id');
			if(element_id == image_id){
				jQuery(this).removeClass('selected');
			}
		});
		
		if (softaculous_ai_config.selected_images.length < 1) {
			jQuery('.softaculous-selected-images-rows').html(`<p class='softaculous-placeholder'>You have not selected any image.</p>`);
		}
	});

	let debounceTimer;
	jQuery('.softaculous-search-images').on('input', function(){
		softaculous_image_loading_page = 1;
		clearTimeout(debounceTimer);
		let search_query = jQuery(this).val();
		debounceTimer = setTimeout(function(){
			jQuery('.softaculous-ai-images-suggestions [data-slug="suggested_image"]').click();
			softaculous_generate_pexel_images(search_query, true);
		}, 600);
	});

	jQuery('.softaculous-search-images').on('click', function(){
		// Need to check if the sugesstions empty or not
		var parent = jQuery(this).closest('.softaculous-ai-image-search');

		if(!parent.hasClass('show_suggestion') && softaculous_ai_config.suggested_tags.length > 0){
			parent.addClass('show_suggestion');
			parent.find('.softaculous-ai-image-suggestion-list').show();
		}
		
	});

	jQuery(document).on('click', function(e) {
		if (!jQuery(e.target).closest('.softaculous-ai-image-search').length) {
			jQuery('.softaculous-ai-image-search').removeClass('show_suggestion');
			jQuery('.softaculous-ai-image-suggestion-list').hide();
		}
	});

	imgHolder.on('click', '.softaculous_pro_suggestion', function(){
		var element = jQuery(this);
		jQuery('.softaculous-search-images').val(element.text()).trigger('input');
	});

	softaculous_pro_images_scrolled();
}

// Description step handler
function softaculous_pro_ai_description_handler(){

	const buildPagination = (action = 'last') => {
		let { descriptions, desc_active } = softaculous_ai_config;	

		// Remove empty entry from the array
		if (descriptions[0].length <= 0) {
			descriptions.splice(0, 1);
		}

		let index = action === 'next' ? desc_active + 1 : action === 'prev' ? desc_active - 1 : descriptions.length - 1;
		index = Math.max(0, Math.min(index, descriptions.length - 1));
		
		softaculous_ai_config.desc_active = index;
		jQuery('#softaculous-business-desc').val(descriptions[index]);
		jQuery('.softaculous-ai-pagination .currently_active').text(`${index + 1} / ${descriptions.length}`);
	};

	jQuery('.softaculous-ai-pagination .active-next-handler, .softaculous-ai-pagination .active-prev-handler').on('click', function () {
		buildPagination(jQuery(this).hasClass('active-next-handler') ? 'next' : 'prev');
	});

	jQuery('#softaculous-business-desc').on('blur', function () {
		let { descriptions, desc_active } = softaculous_ai_config;
		softaculous_ai_config.descriptions[desc_active] = jQuery(this).val();
	});

	jQuery('.softaculous-ai-svg').on('click', function(){	
		
		var post_data = {
			site_name : jQuery('#softaculous-site-name').val(),
			site_category : jQuery('#softaculous-business-type').val(),
			site_language : jQuery('#locale').val(), // TODO: change name
			description : jQuery('#softaculous-business-desc').val(),
		}
		
		jQuery.ajax({
			type: 'post',
			url: soft_pro_obj.ajax_url,
			data: {
				action: 'softaculous_pro_ai_description',
				softaculous_pro_nonce: softaculous_pro_ajax_nonce,
				data: post_data,
			},
			beforeSend: function(){
				jQuery(".softaculous-ai-svg").addClass('softaculous-ai-loading');
			},
			success: function(response) {
				var res = JSON.parse(response);
				
				if (res && res.description && res.description.length > 0) {
					softaculous_ai_config.descriptions.push(res.description);
					buildPagination();
				} else {
					jQuery('.softaculous-pro-description-error').hide();
					softaculous_pro_error_html(res?.error || undefined , 'description')
				}
			},
			error: function(response) {
				jQuery('.softaculous-pro-description-error').hide();
				softaculous_pro_error_html(response?.statusText || undefined , 'description')
			},
			complete:function(){
				jQuery(".softaculous-ai-svg").removeClass('softaculous-ai-loading');
			}
		});

	});
}

function softaculous_pro_update_images() {
	let html = softaculous_ai_config.selected_images.length > 0 ? softaculous_ai_config.selected_images.map(img => {
			var wp_img_url = wp.media.attachment(img.image_id).get('url')
			if (wp_img_url) {
				return `<div class='softaculous-selected-image'><img src='${wp_img_url}' data-selected-id='${img.image_id}' loading="lazy"><div class="softaculous-remove-selected"><span'>&#10006;</span></div></div>`;
			} else {
				return `<div class='softaculous-selected-image'><img src='${img.image_url}' data-selected-id='${img.image_id}' loading="lazy"><div class="softaculous-remove-selected"><span'>&#10006;</span></div></div>`;
			}
		}).join('')
		: `<p class='softaculous-placeholder'>You have not selected any image.</p>`;

	jQuery('.softaculous-selected-images-rows').html(html);
}

var softaculous_pexel_images_loading = false;
function softaculous_generate_pexel_images(query = '', is_search = false) {

	var search_query = query || "software";
	var per_page = 40;
	
	if(softaculous_pexel_images_loading) return;
	
	softaculous_pexel_images_loading = true;
	
	if(is_search){
		jQuery(".softaculous-ai-images-rows").html('');
	}
	
	for (let i = 0; i < per_page; i++) {
		jQuery(".softaculous-ai-images-rows").append(
			`<label class="softaculous-ai-single-image" data-id=''>
				<div class="softaculous-img-skeleton-loader"></div>
			</label>`
		);
	}

	jQuery.ajax({
		type: 'GET',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_search_images',
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			query: search_query,
			per_page: per_page,
			page: softaculous_image_loading_page 
		},
		success: function(response) {
			
			var res = JSON.parse(response);
			
			if('error' in res){
				softaculous_pro_error_html(res?.error || undefined , 'images')
				return;
			}
			
			if(!res.images){
				softaculous_pro_error_html("Unable to get the image for some reason, please try again!" , 'images')
				return;
			}
			
			jQuery(".softaculous-ai-single-image").slice(-per_page).each(function(index) {
				var photo = res.images.photos[index];
				if (photo) {
					var selected_images = softaculous_ai_config.selected_images.some(img => img.image_id == photo.id);
					var selected_class = selected_images ? "selected" : "";
					var imageHtml = `<input type="checkbox" class="softaculous-ai-checkbox" ${selected_images ? "checked" : ""}>
					<img src="${photo.src.tiny}" alt="${photo.photographer}" loading="lazy">
					<a href="${photo.photographer_url}" target="_blank" rel="noreferrer" class="softaculous-ai-single-image-credit">Photo by ${photo.photographer} on Pexels</a>`;
					jQuery(this).attr("data-id", photo.id).addClass(selected_class).html(imageHtml);
				}
			});
			softaculous_image_loading_page++;
		},
		complete: function() {
			softaculous_pexel_images_loading = false;
		},
		error: function(response){
			softaculous_pro_error_html(response?.statusText || undefined , 'images')
		}
	});
}

// We can use this function for getting any ai info option value
function softaculous_pro_get_specific_info(slug = '') {
	jQuery.ajax({
		type: 'POST',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_get_setup_info',
			slug: slug,
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
		},
		success: function (response) {
			if(response.success){
				if(slug === 'image_suggestions'){
					softaculous_pro_render_new_suggestions(response.data);
					if(response.data.length > 0){
						jQuery('.softaculous-search-images').val(response.data[0]).trigger('input');
						softaculous_pro_images_scrolled();
					}
				}
			}
		},
		error: function (error) {
			console.error(error)
		}
	});
}

function softaculous_pro_render_new_suggestions(data){
	// Dump new data to the softaculous_ai_config.suggested_tags varible
	softaculous_ai_config.suggested_tags = data

	var html = '';
	data.forEach(function(item){
		html += `<span class='softaculous_pro_suggestion'>${item}</span>` 
	});
	jQuery('.softaculous-ai-image-suggestions').html(html);

}

// Using js Because this is much more faster than php
function softaculous_pro_render_autocomplete(val='') {
	val = val || '';
	jQuery.ajax({
		type: 'POST',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_ai_autocomplete',
			val: val,
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
		},
		success: function (response) {
			var parsed_obj = JSON.parse(response);
			var html = '';
			if (parsed_obj && 'tags' in parsed_obj) {
				parsed_obj['tags'].forEach((item) => {
					html += `<div class="softaculous-category_btn" data-target="${item}">${item}</div>`;
				});
				jQuery('.softaculous-pro-categories-dropdownlist ').html(html);
			} else {
				softaculous_pro_error_html(parsed_obj?.error || undefined , 'start')
			}
		},
		error: function (error) {
			softaculous_pro_error_html(error?.statusText || undefined , 'start')
		}
	});
}

function softaculous_pro_images_scrolled(){
	// This is previous code wrap inside the function
	setTimeout(()=>{
		jQuery("[data-active-panel=images]").on("scroll" ,function() {
			var container = jQuery(this);
			var scrollTop = container.scrollTop();
			var containerHeight = container.innerHeight();
			var contentHeight = container[0].scrollHeight;
			var total_images = jQuery('.softaculous-ai-images-rows label').length;
	
			if (scrollTop + containerHeight >= contentHeight - 100 && total_images < 320) {
				let search_value = jQuery('.softaculous-search-images').val();
				softaculous_generate_pexel_images(search_value);
			}
	
			if (total_images >= 320 && jQuery('.softaculous-pro-images-end').length === 0) {
				var html = '<p class="softaculous-pro-images-end">End of search results...</p>';
				jQuery('.softaculous-ai-images-rows').append(html);
			}
		});
	}, 200);
}

// Error handling need to improve further.
function softaculous_pro_error_html(error = 'There has been an error. Please reinitiate the onboarding process or refresh.', ele) {
	
	jQuery('.softaculous-pro-error-incounter').remove();
	
	softaculous_pro_onboarding_errors = true;
	
	var error_html = `<div class="softaculous-pro-error-incounter softaculous-pro-alert softaculous-pro-alert-danger" style='text-align:left; flex:1;'>
			<p style='margin: 0px; font-size:14px;'>${error}</p>
			<span class="softaculous-alert-dismissable dashicons dashicons-no" style='cursor: pointer;'></span>
		</div>`;
	
	jQuery(`[data-panel=${ele}]`).find('.softaculous-pro-wizard-inner-content').append(error_html);
}

function softaculous_image_upload_message(){
	var html =`<div class="softaculous-pro-image-success softaculous-pro-alert softaculous-pro-alert-success" style='text-align:left; width:100%'>
					<p style='margin: 0px; font-size:14px;'>Image(s) selected successfully</p>
					<span class="softaculous-alert-dismissable dashicons dashicons-no" style='cursor: pointer;'></span>
				</div>`;
	return html;
}

// Onboarding template JS functions
function softaculous_pro_update_cat_input(other_cat){
	jQuery('#cat_input').val(other_cat);
}

function softaculous_pro_templates_fn($){

	// Back button handler
	jQuery('.softaculous-pro-back').click(function(){

		if(softaculous_ai_config.template_mode == 'manual'){
			softaculous_pro_show_manual_themes(softaculous_pro_setup_info.type !== undefined ? softaculous_pro_setup_info.type.toLowerCase() : '');
		}else{
			softaculous_pro_show_themes();
		}
		
		jQuery("#spro_import_content").hide();
		jQuery(".softaculous-pro-back").hide();
		softaculous_pro_scaleIframe();
	});

	jQuery('.softaculous-pro-back-theme').click(function(){
		jQuery('#softaculous-pro-templates-holder').show();
		jQuery(this).parent().hide();
		jQuery('#SproTemplatesModal').hide();
	});

	jQuery('#cat_input').keyup(function() {
		
		var query = jQuery(this).val().toLowerCase();
		var cat_displayed = 0;
		
		jQuery(".category_btn").each(function( index ){
			var cslug = jQuery(this).find("input").attr("data-target");
			if(cslug.toLowerCase().includes(query)){
				jQuery(this).show();
				cat_displayed++;
			}else{
				jQuery(this).hide();
			}
			
			if(cat_displayed > 0){
				jQuery("#spro_no_cat_results").hide();
			}else{
				jQuery("#spro_no_cat_results").show();
			}
		});
		
	});

	// Show categories on click
	jQuery('.softaculous-pro-categories').on('click', function(){
		jQuery('.softaculous-pro-dropdown-content').toggle();
		
		jQuery(document).on('click', function (e) {
			if (!jQuery(e.target).closest('.softaculous-pro-categories, .softaculous-pro-dropdown-content').length) {
				jQuery('.softaculous-pro-dropdown-content').hide();
			}
		});
	})

	// Fill the categories
	var chtml = '<div class="softaculous-pro-md-4 softaculous-pro-cat" data-cat="">All</div>';
	for(var x in categories){
		chtml += '<div class="softaculous-pro-md-4 softaculous-pro-cat" data-cat="'+x+'">'+categories[x]['en']+'</div>';
	}

	jQuery('.softaculous-pro-cat-holder').html(chtml);
	jQuery('.softaculous-pro-cat-holder').find('.softaculous-pro-cat').click(function(){
		softaculous_pro_show_manual_themes(jQuery(this).data('cat'));
	});

	jQuery('.softaculous-pro-categories-list').find('.category_btn').click(function(){
		var childEle = jQuery(this).children('input');
		var jEle_parent =  jQuery(this).parent().find(".active_category");
		var real_val = jQuery(this).children('input').val();
		var val = jQuery(this).children('input').val().toLowerCase();
		var inputSection = jQuery(".softaculous-pro-category-input");


		if (jQuery(this).hasClass("active_category")) {
			jQuery(this).removeClass("active_category");
			inputSection.find('input').val('')
			return;
		}
		
		jEle_parent.removeClass("active_category");
		jQuery(this).addClass("active_category");
		
		if(real_val){
			inputSection.find('input').val(real_val)
		}
		softaculous_pro_show_manual_themes(val);
	});

	// Search Clear
	jQuery('.softaculous-pro-sf-empty').click(function(){
		jQuery('.softaculous-pro-search-field').val('');
		softaculous_pro_the_themes_window();
	});

	// Search
	var softacProThemeTimeout = {};
	jQuery('.softaculous-pro-search-field').on('keyup', function(e){
		var iEle = jQuery(this);
		
		clearTimeout(softacProThemeTimeout);
		softacProThemeTimeout = setTimeout(function(){
			if(softaculous_ai_config.template_mode == 'manual'){
				softaculous_pro_show_manual_themes('', iEle.val());
			}else{
				softaculous_pro_show_themes('', iEle.val());
			}
		}, 300);
	});

	// Sort themes
	jQuery('.softaculous-pro-sortby').change(function(){
		softaculous_pro_show_manual_themes(jQuery('.softaculous-pro-current-cat').data('cat'), jQuery('.softaculous-pro-search-field').val());
	});

};

// Show the themes
var softaculousAiLoadedPages = [];
function softaculous_pro_show_themes(cat, search, page){

	if (jQuery('#softaculous-pro-templates').is(':empty')) {
		softaculousAiLoadedPages = [];
		softaculous_pro_pending_iframeCount = 0
	}

	softaculous_pro_show_themes_loaded = 1;
	page = page || 1 ;
	
	var sortby = '';
	jQuery("#softaculous_pro_search").hide();	
	jQuery("#softaculous_pro_theme_title").show();	
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous-pro-single-template").hide();
	jQuery("#softaculous-pro-templates").show();
	jQuery('.softaculous-pro-single-template-header').hide();
	jQuery('.softaculous-pro-categories').hide();
	jQuery('.softaculous-pro-ai-search').removeClass('manual-mode');
	
	if(search == undefined || search.length < 1){
		if (softaculousAiLoadedPages.includes(page)) {
			return;
		}
		softaculousAiLoadedPages.push(page)
	}
		
	var search = search || "";
	var cat = "";
	var num = 12;
	
	var start = num * (page - 1);
	var end = num + start;
	var i = 0;
	
	var allowed_list = [];
	var pid = softaculous_pro_setup_info['theme_pid'] ? softaculous_pro_setup_info['theme_pid']: '';
	var html = '';
	
	if(pid.length < 1){
		jQuery('#softaculous-pro-templates').html('');
		html += `<div class="softaculous-pro-wizard-error softaculous-pro-alert softaculous-pro-alert-danger">
				<p style='margin: 0px; font-size:14px;'>There has been error loading themes please refresh the page or start onboarding process from start or you can procced with the manual mode.</p>
			</div>`
		jQuery('#softaculous-pro-templates').append(html);
		jQuery('.softaculous-pro-theme-loading').hide();
		return;
	}

	jQuery('.softaculous-pro-wizard-error').remove();
	jQuery('.softaculous-pro-theme-loading').show();
	
	if(search.length > 0){
		jQuery("#softaculous-pro-templates").html('');
		search = search.toLowerCase();
		
		for(var x in softaculous_pro_templates['tags']){
			if(x.toLowerCase().indexOf(search) >= 0){
				allowed_list = allowed_list.concat(softaculous_pro_templates['tags'][x]);
			}
		}
	}

	if(allowed_list.length > 0){
		allowed_list = Array.from(new Set(allowed_list));
	}
	
	var sorted = {};
	var rsorted = {};
	
	themes = softaculous_pro_templates['ai_list'];
	var theme_counter = start + 1;
	var pid = ('theme_pid' in softaculous_pro_setup_info) ? softaculous_pro_setup_info['theme_pid']: '';
	var preview_urls = {};
	
	for(var x in themes){

		// Is it a searched item
		if(search.length > 0 && themes[x].name.toLowerCase().indexOf(search) === -1 && allowed_list.indexOf(themes[x].thid) === -1){
			continue;
		}
		
		if(i >= start && i < end){
			softaculous_pro_show_theme_tile(themes[x], x, theme_counter);
			
			if(
				pid in softaculous_pro_cache_stream_iframes && 
				x in softaculous_pro_cache_stream_iframes[pid] &&
				'home' in softaculous_pro_cache_stream_iframes[pid][x]
			){
				var iframe = jQuery('#softaculous-pro-templates').find(`iframe[data-slug="${x}"]`);
				var blob = softaculous_pro_cache_stream_iframes[pid][x]['home'];
				
				iframe.attr('src', blob);
				iframe.closest('.softaculous-pro-theme-details').find('a.softaculous-pro-ai-show-demo').attr('href', blob);
			}else{
				preview_urls[x] = x;
			}
			
			softaculous_pro_pending_iframeCount++;
			theme_counter++;
		}
		
		i++;
		
	}
	
	// Stream iframes urls
	softaculous_pro_stream_iframes(preview_urls, pid, jQuery('#softaculous-pro-templates'));
	
	jQuery('.softaculous-pro-theme-details').click(function(){
		softaculous_pro_scaleIframe();
		var jEle = jQuery(this);
		
		if(jEle.find('.softaculous-pro-skeleton-loading').length > 0){
			return;
		}

		softaculous_pro_show_theme_details(jEle.attr('slug'));
		// jQuery('#softaculous-pro-single-template').attr('data-slug', jEle.attr('slug'));
		
		//Prevent inside click
		jQuery('.softaculous_pro_display_iframe').on('load', function () {
			var iframe = jQuery(this).contents();
			iframe.on('click', function (e) {
				e.preventDefault();
			});
			
		});
	});
	
	var pages = Math.ceil(i/num);
	
	// Render after 2 sec to let append everything
	softaculous_pro_iframe_loadtimeout = setTimeout(() => {
		softaculous_pro_pending_iframeCount = 0;
	}, 120000); // 2 minutes

	setTimeout(() => {
		softaculous_pro_scaleIframe();
	}, 2000);
	
}

function softaculous_pro_show_theme_tile(theme, x, theme_counter) {

	var html = '<div class="softaculous-pro-md-3" style="min-height:200px">' +
				'<div class="softaculous-pro-theme-details" slug="' + theme['slug'] + '" thid="' + theme['thid'] + '">' +
					// Theme screenshot and skeleton
					'<div class="softaculous-pro-theme-screenshot softaculous-pro-theme-iframe" style="width: 100%; overflow: hidden; position: relative;">' +
						// Skeleton loader
						'<div class="softaculous-pro-theme-skeleton softaculous-pro-skeleton-loading" id="skeleton-' + theme['thid'] + '">' +
							'<div class="softaculous-pro-skeleton-header">' + 
								'<div class="first"></div>' + 
								'<div class="second"></div>' + 
								'<div class="third"></div>' +
								'<div class="fourth"></div>' +
							'</div>' +
							'<div class="softaculous-pro-skeletion-body softaculous-pro-skeletion-content">' +
								'<div class="softaculous-pro-skeletion-description"></div>' + 
							'</div>' +
							'<div class="softaculous-pro-skeletion-content">' + 
								'<h4></h4> <h4></h4> <h4></h4> <h4></h4>' + 
							'</div>' +
							'<div class="softaculous-pro-skeleton-footer">' + 
								'<div></div> <div></div> <div></div>' +
							'</div>' +
						'</div>' +
						// iFrame
						'<iframe class="scaled-iframe" style="width: 100%; height: 100%; border: none; transform-origin: top left;" id="iframe-' + theme['thid'] + '" data-slug="' + x + '"></iframe>' +
					'</div>' +
					// Option and demo link
					'<div class="softaculous-pro-ai-theme-name" style="display:none">' + 
						'<span> Option ' + theme_counter + '</span>' + 
						'<a class="softaculous-pro-ai-show-demo" href="" title="Template : '+ theme['name'] +'" target="_blank">' + 
							'<i class="fas fa-edit"></i>' + 
						'</a>' +
					'</div>' +
					// Overlay
					'<div class="softaculous-pro-theme-iframe-overlay"></div>' +
				'</div>' +
			'</div>';

	jQuery('#softaculous-pro-templates').append(html);

	// Attach an event listener to remove the skeleton loader when iframe loads
	jQuery('#iframe-' + theme['thid']).on('load', function () {
		var iframe = jQuery(this);
		
		// Prevent load before src
		var src = iframe.attr('src');
		if(!src || src.length < 1 ){
			return;
		}
					
		jQuery('#skeleton-' + theme['thid']).remove();
		jQuery('.softaculous-pro-ai-theme-name').show();
		softaculous_pro_pending_iframeCount--;
		 
		var parent = iframe.parent();
		var overlay = parent.siblings('.softaculous-pro-theme-iframe-overlay');
		var pencil_icon =  parent.siblings('.softaculous-pro-ai-theme-name').find('.softaculous-pro-ai-show-demo');

		overlay.on('mouseenter', function () {
			iframe[0].contentWindow.postMessage({
				action: 'scrollToBottom'
			}, '*');
		});

		overlay.on('mouseleave', function () {
			iframe[0].contentWindow.postMessage({
				action: 'scrollToTop'
			}, '*');
		});

		pencil_icon.off('click');
		pencil_icon.on('click' , function(e){
			e.stopPropagation()
		})
		
		if (softaculous_pro_pending_iframeCount === 0) {
			clearTimeout(softaculous_pro_iframe_loadtimeout);
		}
	});
}

var softaculous_pro_current_manual_theme_page = 1;
var softaculous_pro_manual_theme_loading = false;
var softaculous_pro_total_manual_pages= null;

// Show the themes
function softaculous_pro_show_manual_themes(cat, search, page){
	
	softaculous_pro_show_themes_loaded = 1;
	
	var sortby = 'latest';

	jQuery("#softaculous_pro_search").show();	
	jQuery("#softaculous_pro_theme_title").show();	
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous-pro-single-template").hide();
	jQuery("#softaculous-pro-templates").show();
	jQuery('.softaculous-pro-single-template-header').show()	
	jQuery('.softaculous-pro-categories').css({'display': 'flex'});
	jQuery('.softaculous-pro-ai-search').addClass('manual-mode');
	
	// Blank html
	if((search && search.length > 0) || (cat && cat.length > 0)){

		jQuery('#softaculous-pro-templates').html('');
	}

	var search = search || "";
	// var cat =   cat || softaculous_pro_setup_info.type || "";
	var cat =   cat || "";
	cat = cat.replace("-", "");
	softaculous_pro_setup_info.type = cat;
	cat = (categories[cat] === undefined && (cat && cat.length) > 0 ? 'others' : cat) || "" ;

	var num = 30;
	var page = page || 1;
	var start = num * (page - 1);
	var end = num + start;
	var i = 0;
	var cat_appender = categories[cat] === undefined ? 'Others' : categories[cat]['en']
	
	if(cat.length > 0){
		jQuery('.softaculous-pro-current-cat').html(cat_appender);
		jQuery('.softaculous-pro-current-cat').data('cat', cat);
	}else{
		jQuery('.softaculous-pro-current-cat').html('All');
		jQuery('.softaculous-pro-current-cat').data('cat', '');
	}
	
	var allowed_list = [];
	
	if(search.length > 0){
		search = search.toLowerCase();
		
		for(var x in softaculous_pro_templates['tags']){
			if(x.toLowerCase().indexOf(search) >= 0){
				allowed_list = allowed_list.concat(softaculous_pro_templates['tags'][x]);
			}
		}
	}
	
	if(allowed_list.length > 0){
		allowed_list = Array.from(new Set(allowed_list));
	}
	
	var themeids = [];
	var sorted = {};
	var rsorted = {};
	
	themes = softaculous_pro_templates['list'];
	
	for(var x in themes){
		themeids.push(parseInt(themes[x].thid));
	}	
	
	if(sortby == "latest"){
		var datatheme = Object.values(themes);
		var rsorted_ids = themeids.sort().reverse();
		for(var x of rsorted_ids){
			for( var y in datatheme){
				if(datatheme[y].thid == x){
					rsorted[datatheme[y].slug] = datatheme[y];
				}
			}
		}
		themes = rsorted;
	
	}else if(sortby == "oldest"){
		var datatheme = Object.values(themes);
		var sorted_ids = themeids.sort();
		for(var x of sorted_ids){
			for( var y in datatheme){
				if(datatheme[y].thid == x){
					sorted[datatheme[y].slug] = datatheme[y];
				}
			}
		}
		
		themes = sorted;
		
	}
	
	for(var x in themes){
		
		// Is it same category
		if(cat.length > 0 && cat != themes[x].category){
			continue;
		}
		
		// Is it a searched item
		if(search.length > 0 && themes[x].name.toLowerCase().indexOf(search) === -1 && allowed_list.indexOf(themes[x].thid) === -1){
			continue;
		}
		
		if(i >= start && i < end){
			//console.log(x+' '+i+' '+start+' '+end);
			softaculous_pro_show_manual_theme_tile(themes[x], x);
		}

		i++;
	}
	
	jQuery('.softaculous-pro-theme-details').click(function(){
		var jEle = jQuery(this);
		softaculous_pro_show_manual_theme_details(jEle.attr('slug'));
	});

	softaculous_pro_total_manual_pages = Math.ceil(i / num);
	
}

function softaculous_pro_show_manual_theme_tile(theme, x){
	var html = '<div class="softaculous-pro-md-4">'+
		'<div class="softaculous-pro-theme-details" slug="'+theme['slug']+'" thid="'+theme['thid']+'">'+
			'<div class="softaculous-pro-theme-screenshot">'+
				'<img src="'+mirror+'/'+theme['slug']+'/screenshot.jpg" loading="lazy" alt="" />'+
			'</div>'+
			'<div class="softaculous-pro-theme-name">'+theme['name']+'</div>'+
		'</div>'+
	'</div>';
	jQuery('#softaculous-pro-templates').append(html);
}

function softaculous_pro_strip_extension(str){
	return str.substr(0,str.lastIndexOf('.'));
}

function softaculous_pro_scaleSingleTemplate(){
	//if( !jQuery('.softaculous-pro-single-template').is(':visible')) return;
	var iframeParent = jQuery('#softaculous-pro-single-template .softaculous-pro-theme-iframe');
	var iframe = iframeParent.children('iframe.softaculous_pro_display_iframe');
	
	var iframeIntrinsicWidth = 1250;
	var iframeIntrinsicHeight = 650;
	
	let scaleX = iframeParent.width() / iframeIntrinsicWidth;
	let scaleY = iframeParent.height() / iframeIntrinsicHeight;
	let scale = iframeParent.width() >= iframeIntrinsicWidth ? Math.max(scaleX, scaleY) : Math.min(scaleX, scaleY);

	iframe.css({
		'transform': `scale(${scale})`,
		'transform-origin': 'top left',
		'width': iframeIntrinsicWidth + 'px',
		'height': (iframeIntrinsicHeight / scale) + 'px'
	});

}

function softaculous_pro_scaleIframe() {
	jQuery('#softaculous-pro-templates .softaculous-pro-theme-iframe').each(function () {
		let iframe = jQuery(this).find('iframe');
		
		let parentWidth = jQuery(this).width();
		let parentHeight = jQuery(window).height();

		let iframeWidth = 1250;
		let iframeHeight = 1200;

		// Calculate scale factor to fit within parent container while keeping the aspect ratio
		let scaleX = parentWidth / iframeWidth;
		let scaleY = parentHeight / iframeHeight;
		let scale = Math.min(scaleX, scaleY);

		// Adjust iframe size and scale
		iframe.css({
			transform: `scale(${scale})`,
			width: `${iframeWidth}px`,
			height: `${iframeHeight}px`,
		});

		// Set the parent container height dynamically
		jQuery(this).css({
			height: `${iframeHeight * scale}px`,
			overflow: 'hidden'
		});
	});
}

// Show the theme details
function softaculous_pro_show_theme_details(slug, retry = true){
	
	var theme = themes[slug];
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous_pro_search").hide();	
	jQuery("#softaculous_pro_theme_title").hide();	
	jQuery("#softaculous-pro-single-template").show();
	jQuery("#softaculous-pro-templates").hide();
	jQuery('img#softaculous_pro_display_image').hide();
	jQuery('.softaculous-pro-theme-iframe').show();
	var hIframe = jQuery('[slug="' + slug + '"]').find('iframe.scaled-iframe').clone();
	
	// Set install value
	jQuery('#softaculous-pro-template-install').val(slug);
			
	// Set name
	jQuery("#softaculous-pro-template-name").html(theme['name']);
			
	// Demo URL
	jQuery("#softaculous-pro-demo").attr("href", softaculous_pro_demo+(theme['name'].replace(' ', '_')));
	
	// Blank screenshots
	jQuery("#softaculous_pro_screenshots").html('');

	jQuery(".single-templates-content .softaculous-pro-theme-iframe").children().not(':first').remove();
	
	// Is the license PRO ?
	if(theme['type'] >= 2){
		jQuery('#softaculous_pro_license_div').css('display', 'inline-block');
	}else{
		jQuery('#softaculous_pro_license_div').hide();
	}

	// Show home image
	jQuery("#softaculous-pro-single-template").find('iframe.scaled-iframe').remove();
	jQuery("#softaculous-pro-single-template").find('.softaculous-pro-theme-iframe').append(hIframe);
	jQuery("#softaculous-pro-single-template").find('iframe.scaled-iframe')
	.addClass('softaculous_pro_display_iframe softaculous_pro_iframe_inview')
	.removeAttr('id')
	.removeAttr('style')
	.attr('data-page', 'home')
	.attr('width', '100%');
	
	hIframe.parent().scrollTop(0);
	hIframe.on('load', function(){
		hIframe.addClass('softaculous-pro-is-loaded');
	});

	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_template_info',
		type: 'POST',
		data: {
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			slug: slug
		},
		dataType: 'json',
		success:function(theme) {
			
			if(!theme || !('screenshots' in theme)){
				if(retry){
					softaculous_pro_show_theme_details(slug, false);
					return;
				}
				
				alert('Something went wrong. Please try again!');
				return;
			}
			
			jQuery("#spro_import_single").addClass("hidden");
			
			var sc = '';
			var iframe_sc = '';
			var iframeUrls = {};
			
			// var test= '';
			// Show the screenshots
			for(var x in theme['screenshots']){
				var page_name = softaculous_pro_strip_extension(theme['screenshots'][x]);
				sc += '<div class="softaculous_pro_img_screen" page="'+x+'" page-name="'+page_name+'">'+
				'<div class="softaculous-pro-link-skeleton">'+page_name+' page is loading ...</div>'+
				'<div class="spro_page_selector" style="display:none;"><input type="checkbox" checked="checked" class="checkbox" id="'+page_name+'">'+
				'<label for="'+page_name+'" class="softaculous_pro_img_name">'+page_name+'</label></div>'+
				'<a href="" class="softaculous_pro_img_views view-'+page_name+' dashicons dashicons-visibility" style="display:none;"></a>'+
				'</div>';

				if(page_name == 'home'){
					continue;
				}
				var iframe_url = '';
				
				if(
					pid in softaculous_pro_cache_stream_iframes && 
					x in softaculous_pro_cache_stream_iframes[pid] &&
					page_name in softaculous_pro_cache_stream_iframes[pid][x]
				){
					iframe_url = 'src="'+ softaculous_pro_cache_stream_iframes[pid][x][page_name] +'"';
					
				}else{
					iframeUrls[page_name] = slug;
				}
				
				iframe_sc += '<iframe class="softaculous_pro_display_iframe" data-page="'+ page_name +'" data-slug="'+ slug +'" width="100%" '+ iframe_url +'></iframe>';
			}
			
			var iframeHolder = jQuery('.single-templates-content .softaculous-pro-theme-iframe');
			var pid = ('theme_pid' in softaculous_pro_setup_info) ? softaculous_pro_setup_info['theme_pid']: '';
			
			// Get blob URLs
			softaculous_pro_stream_iframes(iframeUrls, pid, iframeHolder);
			
			iframeHolder.append(iframe_sc);
			jQuery("#softaculous_pro_screenshots").html(sc);
			jQuery("#spro_import_content").show();
			jQuery(".softaculous-pro-back").show();
			jQuery('.softaculous_pro_img_screen:first').children('a').addClass('spro_img_inview');
			jQuery("#spro_import_content").attr('disabled',true);
			var all_iframes = jQuery('.single-templates-content .softaculous-pro-theme-iframe').find('iframe');
			var loaded = 0;
			var timeoutReached = false;

			var timeout = setTimeout(function() {
				timeoutReached = true;
				jQuery('.softaculous_pro_img_screen:first').children('a').addClass('spro_img_inview');
			}, 300000); // 5 minutes

			all_iframes.each(function() {
				var iframe = jQuery(this);
				iframe.on('load', function () {
					
					// Prevent load before src
					var src = jQuery(this).attr('src');
					if(!src || src.length < 1 ){
						return;
					}
					
					// Show the pages slug according to which page is loaded
					var page_slug = jQuery(this).data('page');
					var loaded_ele = jQuery('.softaculous_pro_img_screen[page-name="' + page_slug + '"]');
					loaded_ele.children('.softaculous-pro-link-skeleton').hide();
					loaded_ele.children('.spro_page_selector').show();
					loaded_ele.children('a').show();

					if (timeoutReached) return; // Skip if timeout already triggered
					loaded++;
					if (loaded == all_iframes.length) {
						clearTimeout(timeout); // Cancel timeout
						jQuery("#spro_import_content").removeAttr('disabled')
						
					}
					// Scale iframe
					softaculous_pro_scaleSingleTemplate();
				});
				
				// In case any iframe already loaded
				if(iframe.length > 0 && iframe[0].contentWindow && iframe[0].contentDocument.readyState === 'complete') {
					iframe.trigger('load');
				}
			});
			
			// Remove loader
			jQuery('.softaculous_pro_iframe_inview').on('load', function() {
				jQuery('.softaculous-pro-loader-container').hide();
			});

			jQuery('#softaculous_pro_screenshots .softaculous_pro_img_views').on('click', function(e){
				e.preventDefault();
				var jEle = jQuery(this);
				var page_name = jEle.parent().attr('page-name');
				
				all_iframes.removeClass('softaculous_pro_iframe_inview');
				
				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}
				
				var iEle = jQuery('iframe.softaculous_pro_display_iframe[data-page="' + page_name + '"]');
				jEle.addClass('spro_img_inview');
				
				// Show iframe
				if (iEle.length > 0 && iEle[0].contentWindow && iEle[0].contentDocument.readyState === 'complete') {
					iEle.addClass('softaculous_pro_iframe_inview');
				}else{
					jQuery(".softaculous-pro-loader-container").show(); 
					iEle.on('load.show_iframe', function(){
						jQuery(".softaculous-pro-loader-container").hide();
						iEle.addClass('softaculous_pro_iframe_inview');
					});
				}
				
			});

			jQuery("#softaculous_pro_screenshots").find('.spro_page_selector').click(function(event){
				var jEle = jQuery(this);
				jEle.siblings('.softaculous_pro_img_views').click();

			});
			
			 // Change event on the checkbox
			jQuery("#softaculous_pro_screenshots").find('.checkbox').change(function() {
				var checked_div = jQuery(this).siblings('.softaculous_pro_img_name');

				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}

				jQuery(this).parent().siblings('.softaculous_pro_img_views').addClass('spro_img_inview');

				if (jQuery(this).is(':checked')) {
					checked_div.addClass("softaculous_pro_img_selected");
				} else {
					checked_div.removeClass("softaculous_pro_img_selected");
				}
			});
			
		}
	});
	
}

function softaculous_pro_show_manual_theme_details(slug){
	
	var theme = themes[slug];
	
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous_pro_search").hide();	
	jQuery("#softaculous_pro_theme_title").hide();	
	jQuery("#softaculous-pro-single-template").show();
	jQuery("#softaculous-pro-templates").hide();
	jQuery('.softaculous-pro-theme-iframe').hide();
	jQuery('img#softaculous_pro_display_image').show();
	
	// Set install value
	jQuery('#softaculous-pro-template-install').val(slug);
			
	// Set name
	jQuery("#softaculous-pro-template-name").html(theme['name']);
			
	// Demo URL
	jQuery("#softaculous-pro-demo").attr("href", softaculous_pro_demo+(theme['name'].replace(' ', '_')));
	
	// Blank screenshots
	jQuery("#softaculous_pro_screenshots").html('');
	
	// Is the license PRO ?
	if(theme['type'] >= 2){
		jQuery('#softaculous_pro_license_div').css('display', 'inline-block');
	}else{
		jQuery('#softaculous_pro_license_div').hide();
	}
	
	var url = mirror+'/'+theme['slug'];
	
	// Show home image
	jQuery("img#softaculous_pro_display_image").attr("src", "");
	jQuery("img#softaculous_pro_display_image").attr("src", url+'/screenshots/home.jpg');
	jQuery("img#softaculous_pro_display_image").parent().scrollTop(0);
	
	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_template_info',
		type: 'POST',
		data: {
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			slug: slug
		},
		dataType: 'json',
		success:function(theme) {
			
			jQuery("#spro_import_single").addClass("hidden");
			
			var sc = '';
			// var test= '';
			// Show the screenshots
			for(var x in theme['screenshots']){
				var page_name = softaculous_pro_strip_extension(theme['screenshots'][x]);
				sc += '<div class="softaculous_pro_img_screen" page="'+x+'" page-name="'+page_name+'">'+
				'<div class="spro_page_selector"><input type="checkbox" checked="checked" class="checkbox" id="'+page_name+'">'+
				'<label for="'+page_name+'" class="softaculous_pro_img_name">'+page_name+'</label></div>'+
				'<a href="'+url+'/screenshots/'+theme['screenshots'][x] +'" class="softaculous_pro_img_views view-'+page_name+' dashicons dashicons-visibility"></a>'+
				'</div>';
				
			}
			
			jQuery("#softaculous_pro_screenshots").html(sc);
			jQuery("#spro_import_content").show();
			jQuery("#spro_import_content").removeAttr('disabled');
			jQuery(".softaculous-pro-back").show();
			jQuery('.softaculous_pro_img_screen:first').children('a').addClass('spro_img_inview');

			jQuery("img#softaculous_pro_display_image").on('load', function() {	
				jQuery(".softaculous-pro-loader-container").hide(); // Hide loader
				jQuery(this).show(); // Show image
			});

			jQuery("#softaculous_pro_screenshots").find('.softaculous_pro_img_views').click(function(e){
				e.preventDefault();
				var jEle = jQuery(this);
				jQuery("img#softaculous_pro_display_image").hide();
				jQuery(".softaculous-pro-loader-container").show();
				
				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}
				
				var newImageSrc = jEle.attr("href");
				jQuery("img#softaculous_pro_display_image").attr("src", newImageSrc);
				jQuery("img#softaculous_pro_display_image").on('load', function() {
					jQuery(".softaculous-pro-loader-container").hide(); // Hide loader
					jQuery(this).show(); // Show image
				});

				// In case the image is cached and loads immediately
				if (jQuery("img#softaculous_pro_display_image")[0].complete) {
					jQuery(".softaculous-pro-loader-container").hide(); // Hide loader
					jQuery("img#softaculous_pro_display_image").show(); // Show image
				}

				jQuery("img#softaculous_pro_display_image").parent().scrollTop(0);
				jEle.addClass('spro_img_inview');
			});

			// need to refactor it its create multiple in html
			jQuery("#softaculous_pro_screenshots").find('.softaculous_pro_img_views').on('mouseenter',function(e){
				var imgUrl = jQuery(this).attr('href');
				if(!jQuery(this).attr('loaded')){
					jQuery('<img>').attr('src', imgUrl).on('load', function() {}).appendTo('body').css('display', 'none');
				}
				jQuery(this).attr('loaded',true);
			});

			jQuery("#softaculous_pro_screenshots").find('.spro_page_selector').click(function(event){
				var jEle = jQuery(this);
				jQuery("img#softaculous_pro_display_image").hide();
				jQuery(".softaculous-pro-loader-container").show();

				var checkbox = jQuery(this).find('.checkbox');
				if (jQuery(event.target).is('.checkbox') || jQuery(event.target).is('.softaculous_pro_img_name')) {
					return;
				}
				jEle.siblings('.softaculous_pro_img_views').trigger('click');
				
				
			});
			 // Change event on the checkbox
			 jQuery("#softaculous_pro_screenshots").find('.checkbox').change(function() {
				var checked_div = jQuery(this).siblings('.softaculous_pro_img_name');

				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}
				var newImageSrc = jQuery(this).parent().siblings('.softaculous_pro_img_views').attr("href");

				jQuery(this).parent().siblings('.softaculous_pro_img_views').addClass('spro_img_inview');
				jQuery("img#softaculous_pro_display_image").attr("src", newImageSrc);

				jQuery("img#softaculous_pro_display_image").on('load', function() {
					jQuery(".softaculous-pro-loader-container").hide(); // Hide loader
					jQuery(this).show(); // Show image
				});

				if (jQuery(this).is(':checked')) {
					checked_div.addClass("softaculous_pro_img_selected");
				} else {
					checked_div.removeClass("softaculous_pro_img_selected");
				}
			});
			
		}
	});
}

function softaculous_pro_onboarding_dismiss(e){
	jQuery.ajax({
		type: 'post',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_onboarding_dismiss',
			dismiss: 1,
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			data: [],
		},
		complete: function (response) {
			window.location = soft_pro_obj.admin_url+"admin.php?page=assistant";
		},
	});
}

function softaculous_pro_import_scroll(){

	jQuery("[data-active-panel='import_theme']").on("scroll", function() {
		var container = jQuery(this);
		var element_visible = container.find('#softaculous-pro-single-template');
		
		if(element_visible.css('display') == 'block'){
			return;
		}

		var scrollTop = container.scrollTop();
		var containerHeight = container.innerHeight();
		var contentHeight = container[0].scrollHeight;	

		if (scrollTop + containerHeight >= contentHeight - 100 ) {
			if(softaculous_ai_config.template_mode == 'manual' && !softaculous_pro_manual_theme_loading ){
				if(softaculous_pro_current_manual_theme_page >= softaculous_pro_total_manual_pages ) return;
				softaculous_pro_manual_theme_loading = true;
				softaculous_pro_current_manual_theme_page++;
				
				softaculous_pro_show_manual_themes( jQuery('.softaculous-pro-current-cat').data('cat') || "",  jQuery("#softaculous_pro_search input").val() || "", softaculous_pro_current_manual_theme_page );
				// small delay to avoid multiple calls
				setTimeout(function(){
					softaculous_pro_manual_theme_loading = false;
				}, 1000); 
			}else if(softaculous_ai_config.template_mode == 'ai' && softaculous_pro_pending_iframeCount < 5 ){
				var nextPage = softaculousAiLoadedPages.length + 1;
				softaculous_pro_show_themes("", "", nextPage);
			}
		}
	});
}

function softaculous_pro_stop_progress_calls(){
	softaculous_pro_stop_progress = true;
  if (softaculous_pro_ajax_progress_var && softaculous_pro_ajax_progress_var.readyState !== 4) {
    softaculous_pro_ajax_progress_var.abort();
    console.log("Progress AJAX call is being aborted...");
  }
}

function softaculous_pro_onboarding_progress() {
    if (softaculous_pro_stop_progress) {
      return;
    }

    const now = Date.now();
    const time_since_last_req = now - softaculous_pro_last_req_time;
    const delay = time_since_last_req < 1000 ? 1000 - time_since_last_req : 0;

    setTimeout(() => {
        softaculous_pro_last_req_time = Date.now();

        softaculous_pro_ajax_progress_var = jQuery.ajax({
            url: soft_pro_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'softaculous_pro_get_progress',
                softaculous_pro_nonce: softaculous_pro_ajax_nonce
            },
            success: function (res) {
                if (res.success) {
                    const progress = res.data.progress;

                    // Skip if no change
                    if (JSON.stringify(progress) === JSON.stringify(softaculous_pro_last_progress_cache)) {
                        return softaculous_pro_onboarding_progress();
                    }

                    softaculous_pro_last_progress_cache = progress;

                    // Update UI
                    if (progress.data && progress.data.success) {
                        softaculous_pro_create_html(
                            progress.text,
                            progress.percent,
                            true,
                            progress.data.logs || null
                        );
                    }

                    // Stop if complete
                    if (progress.percent == 100) {
                        softaculous_pro_stop_progress = true;
                        const modal = jQuery('.progress-bar');
                        modal.find('.progress-text').text(softwp_onboarding_lang.congratulations + '🎊');
                        modal.find('.skeleton-loader').hide();
                        modal.find('.softaculous-pro-done').show();
                        return;
                    }
                }

                // Keep polling until stopped
                if (!softaculous_pro_stop_progress) {
                    softaculous_pro_onboarding_progress();
                }
            },
            error: function (xhr, status, error) {
                console.log("Failed to fetch progress:", error);
                softaculous_pro_stop_progress_calls();
            }
        });
    }, delay);
}

// Show Number of images
function softaculous_pro_update_image_count(){
	var image_count = softaculous_ai_config.selected_images.length
	
	jQuery('.softaculous-pro-selected-image-count').html(image_count);
}