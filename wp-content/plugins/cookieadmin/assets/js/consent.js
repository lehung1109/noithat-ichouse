// This file handle the cookie banner and stuff related to that.

var days = cookieadmin_policy.cookieadmin_days;

if(typeof cookieadmin_is_consent === 'undefined'){
	window.cookieadmin_is_consent = {};
}
var cookieadmin_allcookies = cookieadmin_policy.categorized_cookies;
//setInterval(cookieadmin_categorize_cookies, 5000);

function cookieadmin_is_obj(consentObj){
	try{
		return (
			consentObj !== null &&
			typeof consentObj === 'object' &&
			!Array.isArray(consentObj) &&
			Object.keys(consentObj).length > 0
		);
	}catch(e){
		return false;
	}
}


// function cookieadmin_cookie_interceptor(){

	var originalCookieDescriptor =
    Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
    Object.getOwnPropertyDescriptor(document, 'cookie');
	var allowed_cookies = '';

	// Override document.cookie to intercept cookie setting.
	Object.defineProperty(document, 'cookie', {
		configurable: true,
		enumerable: true,
		get: function(){
			return originalCookieDescriptor.get.call(document);
		},
		set: function(val){

			if (!val) return;

			var separatorIndex = val.indexOf('=');
			if(separatorIndex === -1) {
				return;
			}
			
			var cookieName = val.substring(0, separatorIndex).trim();
			var cookieValue = val.substring(separatorIndex + 1).trim();

			if(cookieName === "cookieadmin_consent"){
				originalCookieDescriptor.set.call(document, val);
				return;
			}
			
			// Set cookies which are meant to be deleted
			if(val.includes('expires=Thu, 01 Jan 1970') || cookieValue.startsWith("deleted;")){
				originalCookieDescriptor.set.call(document, val);
				return;
			}

			var cookieInfo = cookieadmin_allcookies[cookieName] || {};
			var category = (cookieInfo.category || 'uncategorized').toLowerCase();
			
			// Set necessary cookies
			if(category == "necessary"){
				originalCookieDescriptor.set.call(document, val);
				return;
			}

			if(cookieadmin_is_obj(cookieadmin_is_consent) || (Array.isArray(cookieadmin_policy['preload']) && cookieadmin_policy['preload'].length > 0)){

				var consentAction = cookieadmin_is_consent.action;
				
				if(!consentAction){
					consentAction = cookieadmin_policy['preload'].reduce((a, val) => {a[val] = ''; return a;}, {});
				}

				if(consentAction.accept || consentAction[category]){
					originalCookieDescriptor.set.call(document, val);
				}else{
					
					var pathMatch = val.match(/path=([^;]+)/i);
					var domainMatch = val.match(/domain=([^;]+)/i);
					var path = pathMatch ? pathMatch[1].trim() : '/';
					var domain = domainMatch ? `domain=${domainMatch[1].trim()};` : '';
					
					var deleteString = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path}; ${domain}`;
					originalCookieDescriptor.set.call(document, deleteString.trim());
				}
				
			}else{
				(cookieadmin_allcookies[cookieName] = cookieadmin_allcookies[cookieName] || {}).string = val.trim();
				return false;
			}
			
		}
	});
// }
// cookieadmin_cookie_interceptor();


function cookieadmin_is_cookie(name){
	
	if(!document.cookie) return false;
	
	var coki = document.cookie.split(";") ;
	
	if(name == "all"){
		return coki ? coki : [];
	}
	var nam = name + "=";
	
	for(var i=0; i < coki.length; i++){
		if(coki[i].trim().indexOf(nam) == 0){
			try {
				var cookie_value = coki[i].trim().split("=");
				if(!cookie_value[1]){
					return false;
				}

				var decoded = decodeURIComponent(cookie_value[1]);
				return JSON.parse(decoded);
			} catch {
				return false;
			}
		}
	}
	
	return false;
}

function cookieadmin_check_consent(){
	var cookieadmin_cookie = cookieadmin_is_cookie("cookieadmin_consent");
	if(!!cookieadmin_cookie){
		if(!!cookieadmin_cookie.consent){
			cookieadmin_is_consent.consent = cookieadmin_cookie.consent;
			delete cookieadmin_cookie.consent;
		}
		
		cookieadmin_is_consent.action = cookieadmin_cookie;
	}
}
cookieadmin_check_consent();

function cookieadmin_restore_cookies(update) {
    
	var cookieadmin_accepted_categories = [];
	
	if(update.accept && update.accept == "true"){
		
		document.querySelectorAll(".cookieadmin_toggle").forEach(function(e){
			key = e.children[0].id;
			if (key.includes("cookieadmin-")) {
				key = key.replace("cookieadmin-", "");
				cookieadmin_accepted_categories.push(key);
			}
		});
		
	}else if(update.reject && update.reject == "true"){
		return true;
	}else{
		for (var [key, value] of Object.entries(update)) {
			if(key != "consent"){
				cookieadmin_accepted_categories.push(key);
			}
		}
	}
	
	
  	for(cookie in cookieadmin_allcookies){
  		document.cookie = cookieadmin_allcookies[cookie].string;
  	};
	
    cookieadmin_accepted_categories.forEach(function(category) {
		
        document.querySelectorAll(
            'script[type="text/plain"][data-cookieadmin-category="' + category + '"]'
        ).forEach(function(el) {
            var newScript = document.createElement('script');

            // Copy attributes
            if (el.src) {
                newScript.src = el.src;
            } else {
                newScript.text = el.textContent;
            }

            if (el.defer) newScript.defer = true;
            if (el.async) newScript.async = true;

            // Copy other attributes if needed
            ['id', 'class', 'data-name'].forEach(attr => {
                if (el.hasAttribute(attr)) {
                    newScript.setAttribute(attr, el.getAttribute(attr));
                }
            });

            el.parentNode.replaceChild(newScript, el);
        });
    });
}

function cookieadmin_set_cookie(name, value, days = 365, domain = "") {
  if (!name || !value) return false;

	if((cookieadmin_policy.is_pro != 0) && (cookieadmin_pro_vars !== 'undefined')){
		if(cookieadmin_pro_vars.shared_subdomain_consent && cookieadmin_pro_vars.base_domain){
			domain = cookieadmin_pro_vars.base_domain;
		}
	}
  
  var date = new Date();
  date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000); // default 1 year

  var cookieString = `${encodeURIComponent(name)}=${JSON.stringify(value)};`;
  cookieString += ` expires=${date.toUTCString()};`;
	cookieString += ` path=${!domain ? cookieadmin_policy.base_path : '/'};`;
  cookieString += ` SameSite=Lax;`;
  if(cookieadmin_policy.is_ssl || window.location.protocol === 'https:'){
	  cookieString += ` Secure;`;
  }

  // Add domain if explicitly passed
  if (domain) {
    cookieString += ` domain=${domain};`;
  }

  document.cookie = cookieString;
  return true;
}

//Populates modal with consent if selected & adds found cookies
function cookieadmin_populate_preference(){
	
	consent = cookieadmin_is_consent.action;
	
	if(!!consent){
		
		if(consent.accept){
			document.querySelectorAll(".cookieadmin_toggle").forEach(function(e){
				e.children[0].checked = true;
			});
		}
		else if(consent.reject){
			document.querySelectorAll(".cookieadmin_toggle").forEach(function(e){
				e.children[0].checked = false;
			});
		}
		else{
			for(btn in consent){
				if(btn_ele = document.querySelector("#cookieadmin-" + btn)){
					btn_ele.checked = true;
				}
			}
		}
	//Load as per preloaded categories selected by admin	
	}else if(cookieadmin_policy['preload'] && cookieadmin_policy['preload'].length > 0){
		
		cookieadmin_policy['preload'].forEach(function(val){
			if(btn_ele = document.querySelector("#cookieadmin-" + val)){
				btn_ele.checked = true;
			}
		});
	}
	
	var cookieadmin_shown = (typeof cookieadmin_shown !== "undefined") ? cookieadmin_shown : [];
	
	if(cookieadmin_allcookies){
		
		var cookieadmin_filtrd = Object.keys(cookieadmin_allcookies).filter(e => !cookieadmin_shown.includes(e));
		
		for(c_info of cookieadmin_filtrd){
			
			var category_var = cookieadmin_allcookies[c_info].category;
			
			if(!category_var){
				continue;
			}
			var card_container = document.querySelector(".cookieadmin-" + category_var.toLowerCase());
			
			if(!card_container){
				continue;
			}
			card_container.innerHTML = (card_container.innerHTML == 'None') ?  '' : card_container.innerHTML;
			
			var cookieadmin_exp = cookieadmin_policy.lang.session;
				
			if(
				!!cookieadmin_allcookies[c_info].expires 
				&& cookieadmin_allcookies[c_info].expires !== "0000-00-00 00:00:00"
			){
				
				var expDate = new Date(cookieadmin_allcookies[c_info].expires.replace(' ', 'T'));
				
				if (!isNaN(expDate.getTime())) {
					var daysLeft = Math.ceil((expDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24));

					if (daysLeft > 0) {
						cookieadmin_exp = daysLeft +' '+ cookieadmin_policy.lang.days;
					}
				}
			}
			
			card_container.innerHTML += '<div class="cookieadmin-cookie-card"> <div class="cookieadmin-cookie-header"> <strong class="cookieadmin-cookie-name">'+ c_info.replace(/_+$/, "") +'</strong> <span class="cookieadmin-cookie-duration"><b>'+ cookieadmin_policy.lang.duration +':</b> '+ cookieadmin_exp +'</span> </div> <p class="cookieadmin-cookie-description">'+ cookieadmin_allcookies[c_info].description +'</p> <div class="cookieadmin-cookie-tags"> ' + (cookieadmin_allcookies[c_info].platform ? '<span class="cookieadmin-tag">' + cookieadmin_allcookies[c_info].platform + '</span>' : "") + ' </div> </div>';
			cookieadmin_shown.push(c_info);
		}
	}
		
}

function cookieadmin_toggle_overlay(){
	
	if(window.getComputedStyle(document.getElementsByClassName("cookieadmin_modal_overlay")[0]).display == "none"){
		document.getElementsByClassName("cookieadmin_modal_overlay")[0].style.display = "block";
	}else{
		document.getElementsByClassName("cookieadmin_modal_overlay")[0].style.display = "none";
	}
	
}

function cookieadmin_categorize_cookies(){
	
	if(!cookieadmin_allcookies){
		return;
	}
	
	var cookieadmin_chk_cookies = {};
	var cookieadmin_consent_chng = [];
	
	for(a_cookie in cookieadmin_allcookies){
		if(!cookieadmin_allcookies[a_cookie].category){
			cookieadmin_chk_cookies[a_cookie] = cookieadmin_allcookies[a_cookie];
		}else if(cookieadmin_is_consent.old_action !== cookieadmin_is_consent.action && a_cookie !== "cookieadmin_consent"){
			document.cookie = cookieadmin_allcookies[a_cookie].string;
		}
	}
	
	if(!cookieadmin_is_obj(cookieadmin_chk_cookies)){
		return;
	}
	
	/* var xhttp2 = new XMLHttpRequest();
	
	var data = 'action=cookieadmin_ajax_handler&cookieadmin_act=categorize_cookies&cookieadmin_security=' + cookieadmin_policy.nonce + "&cookieadmin_cookies=" + JSON.stringify(cookieadmin_chk_cookies);
	
	xhttp2.onload = function() {
		parsd = JSON.parse(this.responseText);
		
		if(parsd.success){
			cookies = parsd.data;
			for(coki in cookies){
				cookieadmin_chk_cookies[coki].name = coki;
				if(cookies[coki].category === "un_c"){
					cookieadmin_chk_cookies[coki].source = "unknown";
					cookieadmin_chk_cookies[coki].description = "unknown";
				}
				cookieadmin_allcookies[coki] = cookieadmin_chk_cookies[coki];
				document.cookie = cookieadmin_chk_cookies[coki].string;
			}
		}
	}
	
	xhttp2.open("POST", cookieadmin_policy.ajax_url, true);
	xhttp2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	xhttp2.send(data); */
}


document.addEventListener("DOMContentLoaded", function() {
	
	var cookieadmin_show_reconsent = 0;
	if(cookieadmin_policy.is_pro != 0 && cookieadmin_pro_vars !== 'undefined' && cookieadmin_pro_vars.reconsent != 0){
		var cookieadmin_show_reconsent = 1;
	}

	//Create overlay
	var cookieadmin_ovrlay =  document.createElement("div");
	cookieadmin_ovrlay.className = "cookieadmin_modal_overlay";
	document.body.appendChild(cookieadmin_ovrlay);
	
	var before_consent_dispaly = new CustomEvent('cookieadmin_before_consent_display');
	
	// For anything that needs to be done before disaplying consent.
	cookieadmin_policy.hide_banner = false; // initializing
	window.dispatchEvent(before_consent_dispaly);

	//Show notice or re-consent icon as needed
	if(!cookieadmin_is_obj(cookieadmin_is_consent) && !cookieadmin_policy.hide_banner){
		
		if(cookieadmin_policy.cookieadmin_layout !== "popup"){
				document.getElementsByClassName("cookieadmin_law_container")[0].style.display = "block";
		}else{
			cookieadmin_toggle_overlay();
			document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "flex";
		}
		
		/* //block cookie scripts
		var cookieadmin_blockedScripts = [
			'https://www.google-analytics.com/analytics.js',
			'https://connect.facebook.net/en_US/fbevents.js',
			'https://www.youtube.com/iframe_api'
		];
		
		cookieadmin_blockedScripts.forEach(function(scriptUrl) {
			var scriptTag = document.querySelector(`script[src='${scriptUrl}']`);
			if (scriptTag) {
				scriptTag.remove();  // Remove script if already loaded
			}
		}); */
		
	}else if(cookieadmin_show_reconsent){
		document.getElementsByClassName("cookieadmin_re_consent")[0].style.display = "block";	
		
	}
	
	//Edit Notice and Modal contents

	cookieadmin_populate_preference();

	for(data in cookieadmin_policy){

		typ = 0;
		if(data.includes("_bg_color")){
			d_ele = data.replace("_bg_color", "");
			typ = 1;
		}else if(data.includes("_border_color")){
			d_ele = data.replace("_border_color", "");
			typ = 2;
		}else if(data.includes("_color")){
			d_ele = data.replace("_color", "");
			typ = 3;
		}else{
			d_ele = data;
		}
		
		d_eles = [];
		if(document.getElementById(d_ele)){
			d_eles = [document.getElementById(d_ele)];
		}
		if(document.getElementsByClassName(d_ele).length){
			d_eles = (document.getElementsByClassName(d_ele).length > 1) ? document.getElementsByClassName(d_ele) : [document.getElementsByClassName(d_ele)[0]];
		}
		
		if(!!d_eles){
			i = 0;
			while(i < d_eles.length){
				d_ele = d_eles[i];
				if(typ == 3){
					d_ele.style.color = cookieadmin_policy[data];
				}else if(typ == 2){
					d_ele.style.borderColor = cookieadmin_policy[data];
				}else if(typ == 1){
					d_ele.style.backgroundColor = cookieadmin_policy[data];
				}else{
					d_ele.innerHTML = cookieadmin_policy[data];
				}
				i++;
			}
		}		
	}

	//Add layout as class
	if(!!cookieadmin_policy.cookieadmin_position && cookieadmin_policy.cookieadmin_layout !== "popup"){
		cookieadmin_policy.cookieadmin_position.split("_").forEach(function(clas){
			clas = "cookieadmin_" + clas;
			document.getElementsByClassName("cookieadmin_law_container")[0].classList.add(clas);
		});
	}

	// Change consent layout dynamically
  // TODO: There is a operator called optional chaining operator ?. something like this users?.[0]?.name; wont use it now just for reference
	var  cookieadmin_law_container_var = document.getElementsByClassName("cookieadmin_law_container")[0];
	if (!!cookieadmin_law_container_var){
		cookieadmin_law_container_var.classList.add("cookieadmin_" + cookieadmin_policy.cookieadmin_layout);
	}

	// Change Modal layout dynamically
	document.getElementsByClassName("cookieadmin_cookie_modal")[0].classList.add("cookieadmin_" + cookieadmin_policy.cookieadmin_modal);

	/*if(cookieadmin_policy.layout == "footer"){
		
	}*/

	if(cookieadmin_policy.cookieadmin_modal == "side"){
		document.getElementsByClassName("cookieadmin_modal_footer")[0].style.flexDirection = "column";
	}
		
	// Remove modal close Button
	if(cookieadmin_policy.cookieadmin_layout == "popup"){
		document.getElementsByClassName("cookieadmin_close_pref")[0].style.display = "none";
	}

	//show preference modal
	cookieadmin_show_modal_elemnts = document.querySelectorAll(".cookieadmin_re_consent, .cookieadmin_customize_btn");
	cookieadmin_show_modal_elemnts.forEach(function(e){
		
		e.addEventListener("click", function(e){
			
			/*cookieadmin_is_cookie("all").forEach(function(e){
				c_name = e.split("=")[0].trim();
				if(!!cookieadmin_allcookies[c_name]){
					console.log(JSON.stringify(cookieadmin_allcookies[c_name]));
				}
			});*/
			
			cookieadmin_toggle_overlay();
			document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "flex";
			var cookieadmin_re_consent = document.getElementsByClassName("cookieadmin_re_consent")[0];
			if(cookieadmin_re_consent){
				cookieadmin_re_consent.style.display = "none";
			}

			var cookieadmin_law_container = document.getElementsByClassName("cookieadmin_law_container")[0];
			if(cookieadmin_law_container){
				cookieadmin_law_container.style.display = "none";
			}
			
			if(cookieadmin_policy["cookieadmin_modal"] == "side"){
				document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "grid";
			}
			
			if(e.target.className == "cookieadmin_re_consent"){
				document.getElementsByClassName("cookieadmin_close_pref")[0].id = "cookieadmin_re_consent";
			}else{
				document.getElementsByClassName("cookieadmin_close_pref")[0].id = "cookieadmin_law_container";
			}
		});
	});
	
	//Save preference
	document.querySelector(".cookieadmin_save_btn").addEventListener("click", function(){
		
		document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "none";
		if(cookieadmin_show_reconsent){
			document.getElementsByClassName("cookieadmin_re_consent")[0].style.display = "block";
		}
		
		var prefer = {};

		document.querySelectorAll(".cookieadmin_toggle").forEach(function(e){
			if(!!e.children[0].checked){
				prefer[e.children[0].id.replace("cookieadmin-","")] = 'true';
			}
		});
		
		if(Object.keys(prefer).length !== 0){
			var override_gpc = document.getElementById('cookieadmin-override_gpc');
			var is_override_gpc = false;
			if(override_gpc && override_gpc.checked){
				is_override_gpc = true;
			}

			if(Object.keys(prefer).length === 3 && !is_override_gpc){
				let accept_btn = document.querySelectorAll(".cookieadmin_accept_btn");
			
				if(accept_btn.length > 0){
					accept_btn[accept_btn.length-1].click();
				}

				return;
			}
		}else{
			let reject_btn = document.querySelectorAll(".cookieadmin_reject_btn");
			
			if(reject_btn.length > 0){
				reject_btn[reject_btn.length-1].click();
			}
			
			return;
		}
		
		cookieadmin_toggle_overlay();

		cookieadmin_set_consent(prefer, days);
	});


	//Accept or reject all cookies
	cookieadmin_save_all_cookie_elemnts = document.querySelectorAll(".cookieadmin_accept_btn, .cookieadmin_reject_btn");

	cookieadmin_save_all_cookie_elemnts.forEach(function(e){
		
		e.addEventListener("click", function(){
			// console.log(e);

			document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "none";
			var cookieadmin_law_container = document.getElementsByClassName("cookieadmin_law_container")[0];
			if(cookieadmin_law_container){
				cookieadmin_law_container.style.display = "none";
			}
			
			var cookieadmin_re_consent = document.getElementsByClassName("cookieadmin_re_consent")[0];
			if(cookieadmin_re_consent){
				cookieadmin_re_consent.style.display = "block";
			}
			
			if(e.id.includes("modal")){
				cookieadmin_toggle_overlay();
			}
			
			var prefer2 = e.classList.contains("cookieadmin_reject_btn") ? {reject: "true"} : {accept: "true"};

			cookieadmin_set_consent(prefer2, days);
		});
	});
	
	document.querySelectorAll(".cookieadmin_show_pref_cookies").forEach(function(e){
		e.addEventListener("click", function(el){
			
			var tgt = el.target.id;
			tgt = tgt.replace(/-container$/, "");
			
			if(el.target.classList.contains("dwn")){
				el.target.innerHTML = "&#9658;";
				el.target.classList.remove("dwn");
				document.querySelector("."+tgt).style.display = "none";
			}else{
				el.target.innerHTML = "&#9660;";
				el.target.classList.add("dwn");
				document.querySelector("."+tgt).style.display = "block";
			}
		});
	});

	document.getElementsByClassName("cookieadmin_close_pref")[0].addEventListener("click", function(e){
		document.getElementsByClassName("cookieadmin_cookie_modal")[0].style.display = "none";
		cookieadmin_toggle_overlay();
		if(!cookieadmin_is_obj(cookieadmin_is_consent)){
			var cookieadmin_law_container = document.getElementsByClassName("cookieadmin_law_container")[0];
			if(cookieadmin_law_container){
				cookieadmin_law_container.style.display = "block";
			}
		}else if(cookieadmin_show_reconsent){
			var cookieadmin_re_consent = document.getElementsByClassName("cookieadmin_re_consent")[0];
			if(cookieadmin_re_consent){
				cookieadmin_re_consent.style.display = "block";
			}
		}
	});
	
});

function cookieadmin_set_consent(prefrenc, days){
	
	if (typeof cookieadmin_pro_set_consent === "function") {
		return cookieadmin_pro_set_consent(prefrenc, days);
	}else{
		return cookieadmin_save_consent_cookie(prefrenc, days);
	}
}

function cookieadmin_save_consent_cookie(prefrenc, days, consent_id){
	
	var cookieadmin_consent = prefrenc;
		
	if(consent_id){
		cookieadmin_is_consent.consent = consent_id;
		cookieadmin_consent['consent'] = consent_id;
	}
	
	if(!cookieadmin_is_consent.consent){
		cookieadmin_is_consent.consent = "";
	}
	
	cookieadmin_is_consent["old_action"] = cookieadmin_is_consent.action ? cookieadmin_is_consent.action : {};
	cookieadmin_is_consent.action = prefrenc;
	cookieadmin_populate_preference();
	cookieadmin_set_cookie('cookieadmin_consent', cookieadmin_consent, days);
	
	if (typeof cookieadmin_update_gcm === "function") {
		cookieadmin_update_gcm(1);
	}

	if (typeof cookieadmin_pro_update_clarity_cookie === "function") {
		cookieadmin_pro_update_clarity_cookie();
	}
	
	if(!!cookieadmin_policy.reload_on_consent){
		location.reload();
	}else{
		cookieadmin_restore_cookies(prefrenc);
	}
}

