(function(){
	window.addEventListener('cookieadmin_before_consent_display', function(){
		if(cookieadmin_pro_handle_gpc()){
			cookieadmin_policy.hide_banner = true;
		}
	});
	
	// Handle click on blocked content to accept and show
	document.addEventListener('click', function(e){
		var blockedElement = e.target.closest('.cookieadmin-content-blocker');
		if(blockedElement){			
			const category = blockedElement.getAttribute('data-cookieadmin-category');
			var preference = {};

			if(cookieadmin_is_obj(cookieadmin_is_consent) && cookieadmin_is_consent.action){
				preference = cookieadmin_is_consent.action;
			}

			if(category){

				if(preference.reject && ((preference.reject === true) || (preference.reject === 'true'))){
					preference = {};
				}

				preference[category] = 'true';
				if(preference.functional && preference.marketing && preference.analytics){
					preference = {accept : 'true'};
				}
				var gpc_message = cookieadmin_pro_vars.gpc_alert_load_content ? cookieadmin_pro_vars.gpc_alert_load_content : '';
				if(cookieadmin_pro_set_consent(preference, 365, gpc_message)){
					cookieadmin_pro_enable_blocked_content(preference);
				}
			}
		}
	});
})()

function cookieadmin_pro_set_consent(prefrenc, days, gpc_message = '') {
	
	// Handling GPC override, preventing GPC signal
	if(cookieadmin_pro_vars && cookieadmin_pro_vars.respect_gpc){
		if(!cookieadmin_pro_handle_set_gpc(gpc_message)){
			return false;
		}
	}

	const data = 'action=cookieadmin_pro_ajax_handler&cookieadmin_act=save_consent' +
		'&cookieadmin_preference=' + encodeURIComponent(JSON.stringify(Object.keys(prefrenc)));


	let payload = data;
	if (cookieadmin_is_obj(cookieadmin_is_consent) && !!cookieadmin_is_consent.consent) {
		payload += '&cookieadmin_consent_id=' + cookieadmin_is_consent.consent;
	}

	// Make async request — don't block or wait
	const xhttp = new XMLHttpRequest();

	xhttp.open('POST', cookieadmin_pro_vars.ajax_url, true); // true = async

	xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

	xhttp.onload = function () {
		if(this.status === 200){
			try {
				const parsed = JSON.parse(this.responseText);
				if (parsed.success && parsed.data && parsed.data.response) {
					cookieadmin_save_consent_cookie(prefrenc, days, parsed.data.response);
					
					// Showing override option if we have GPC enabled.
					if(cookieadmin_pro_vars && cookieadmin_pro_vars.respect_gpc){
						var overrideEle = document.getElementById('cookieadmin_gpc_override');
						
						if(overrideEle){
							overrideEle.style.display = 'block';
						}
					}

					// Enable blocked content after consent
					cookieadmin_pro_enable_blocked_content(prefrenc);
				}
			} catch (e) {
				console.error("Invalid JSON response:", e);
			}
		}
	};

	xhttp.onerror = function () {
		console.error("AJAX request failed");
	};

	xhttp.send(payload);

	// Immediately return
	return true;
}

function cookieadmin_pro_enable_blocked_content(prefrenc){
	const blockedElements = document.querySelectorAll('.cookieadmin-content-blocker');
	
	blockedElements.forEach(function(element){
		const category = element.getAttribute('data-cookieadmin-category');
		
		if(!category){
			return;
		}

		// Check if user has consented to this category
		const hasCategoryConsent = prefrenc.hasOwnProperty(category) && (prefrenc[category] === true || prefrenc[category] === 'true');
		
		// Check if user accepted all (accept: true means all categories)
		const hasAcceptAll = prefrenc.hasOwnProperty('accept') && (prefrenc.accept === true || prefrenc.accept === 'true');
		
		if(hasCategoryConsent || hasAcceptAll){
			cookieadmin_pro_replace_blocked_element(element);
		}
	});
}

function cookieadmin_pro_replace_blocked_element(element){
	const placeholder = element;
	

	const iframe = placeholder.getElementsByTagName('iframe')[0];
	if(iframe && iframe.dataset.cookieadminSrc){
		iframe.src = iframe.dataset.cookieadminSrc;
		iframe.removeAttribute('data-cookieadmin-src');
	}

	placeholder.parentNode.replaceChild(iframe, placeholder);
}

// Decided if GPC is enabled and if user has enabled the override when saving preference
function cookieadmin_pro_handle_set_gpc(message){
	var consentData = cookieadmin_is_cookie("cookieadmin_consent");
	if (consentData && consentData.respect_gpc) {
		var override = document.getElementById('cookieadmin-override_gpc');
		
		if(!override || !override.checked){
			alert(message ? message : (cookieadmin_pro_vars.gpc_alert ? cookieadmin_pro_vars.gpc_alert : 'Please accept override GPC before saving preference.'));
			return false;
		}
	}

	return true;
}

// GPC (Global Privacy Control) Detection and Handling
function cookieadmin_pro_check_gpc(){
	// Check if respect_gpc setting is enabled
	if(!cookieadmin_pro_vars || !cookieadmin_pro_vars.respect_gpc){
		return false;
	}

	// Check navigator.globalPrivacyControl property (browser-level GPC signal)
	if(typeof navigator !== 'undefined' && typeof navigator.globalPrivacyControl !== 'undefined'){
		if(navigator.globalPrivacyControl === true || navigator.globalPrivacyControl === 'true'){
			return true;
		}
	}

	// Check if GPC was detected server-side (Sec-GPC header)
	if(cookieadmin_pro_vars && cookieadmin_pro_vars.gpc_enabled){
		return true;
	}

	return false;
}

function cookieadmin_pro_handle_gpc(){
	// Check if GPC is enabled and should be respected
	if(!cookieadmin_pro_check_gpc()){
		return false;
	}
	
	// Handling visibility of Override option in Cookie Customizer modal
	var overRideEle = document.getElementById('cookieadmin_gpc_override');
	var consentData = cookieadmin_is_cookie("cookieadmin_consent");

	if(overRideEle){
		overRideEle.style.display = (consentData && consentData.respect_gpc === true) ? '' : 'none';
	}

	// Check if consent was already saved
	if(consentData){
		return false;
	}

	// Apply auto-reject consent for GPC users
	// Only allow necessary/functional cookies, reject analytics and marketing
	const gpc_preferences = {
		'functional': true,
		'respect_gpc': true,
	};

	// Set consent for 365 days
	cookieadmin_pro_set_consent(gpc_preferences, 365);

	// Show GPC honored message if banner element exists
	cookieadmin_pro_show_gpc_message();

	return true;
}

function cookieadmin_pro_show_gpc_message() {
	
	var toast = document.getElementById('cookieadmin-gpc-toast');
    
	if(!toast){
		return;
	}
	
	closeBtn = toast.querySelector('button');
	
	if(!closeBtn){
		return;
	}
	
	// Add hover effects for the close button
	closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
	closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');

	// Add click event to remove the toast with a fade-out animation
	closeBtn.addEventListener('click', () => {
		toast.style.opacity = '0';
		toast.style.transform = 'translateY(20px)';
		toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s';
		toast.style.visibility = 'hidden';
		setTimeout(() => {
			if (document.body.contains(toast)) {
				document.body.removeChild(toast);
			}
		}, 300); // Wait for transition to finish
	});

	// Trigger the fade-in animation
	requestAnimationFrame(() => {
		toast.style.visibility = 'visible';
		toast.style.opacity = '1';
		toast.style.transform = 'translateY(0)';
		toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease, visibility 0s';
	});

	// Auto-hide after 10 seconds
	setTimeout(() => {
		if(document.body.contains(toast)){
			toast.style.opacity = '0';
			toast.style.transform = 'translateY(20px)';
			toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s';
			toast.style.visibility = 'hidden';
			setTimeout(() => {
				if (document.body.contains(toast)) {
					document.body.removeChild(toast);
				}
			}, 300);
		}
	}, 10000);
}

