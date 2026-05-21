jQuery(document).ready(function($){
	
	$('#cookieadmin-pro-create-consent-table').on('click', function(){
		var btn = $(this);
		btn.prop('disabled', true).text('Creating...');

		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: 'POST',
			data : {
				action:'cookieadmin_pro_ajax_handler',
				cookieadmin_act:'create_consent_table',
				cookieadmin_pro_security : cookieadmin_pro_policy.cookieadmin_nonce,
			}, success: function(res){
				if(res && res.success){
					$('#cookieadmin-pro-table-missing-notice').slideUp();
					location.reload();
				} else {
					alert(res && res.data ? res.data : 'Failed to create table');
					btn.prop('disabled', false).text('Create Consent Table');
				}
			}
		})
	});
		
	function cookieadminConsentLogsExport(){
		
		$(this).prop("disabled", true);
		
		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: "POST",
			data : {
				action : 'cookieadmin_pro_ajax_handler',
				cookieadmin_act : 'export_logs',
				cookieadmin_export_type : 'consent_logs',
				cookieadmin_pro_security : cookieadmin_pro_policy.cookieadmin_nonce,
			},
			success: function(response){				
				
				// Was the ajax call successful ?
				if(response.substring(0,2) == "-1"){
					
					var err_message = response.substring(2);
					
					if(err_message){
						alert(err_message);
					}else{
						alert("Failed to export data");
					}
					
					return false;
				}
				
				/*
				* Make CSV downloadable
				*/
				var downloadLink = document.createElement("a");
				var fileData = ['\ufeff'+response];

				var blobObject = new Blob(fileData,{
				 type: "text/csv;charset=utf-8;"
				});

				var url = URL.createObjectURL(blobObject);
				downloadLink.href = url;
				downloadLink.download = "cookieadmin-consent-logs.csv";

				/*
				* Actually download CSV
				*/
				document.body.appendChild(downloadLink);
				downloadLink.click();
				document.body.removeChild(downloadLink);
			},	
			error: function(xhr, status, error) {
				console.log('CookieAdmin AJAX Error:', status, error);
				console.log('CookieAdmin response: ' + xhr.responseText); // Check the error message
				alert("Request failed: " + error);
			}
		});
		
		$(this).prop("disabled", false);
	}
	$(".cookieadmin-consent-logs-export").on("click", cookieadminConsentLogsExport);
	
	// For .cookieadmin-logs-paginate click
	$(".cookieadmin-logs-paginate").on("click", function () {
		cookieadmin_pro_paginate(this);
	});

	// For input change
	$("#current-page-selector").on("change", function () {
		cookieadmin_pro_paginate(this);
	});
	
	function cookieadmin_pro_paginate(el){
		var $this = $(el);
		
		let pageType = $this.attr("id");
		let currentPageInput = $("#current-page-selector");
		let currentPage = parseInt(currentPageInput.val());
		let totalPages = parseInt($(".total-pages").text());

		// Determine action based on ID
		if (pageType === "cookieadmin-first-consent-logs") {
			currentPage = 1;
		} else if (pageType === "cookieadmin-previous-consent-logs") {
			if (currentPage > 1) currentPage--;
		} else if (pageType === "cookieadmin-next-consent-logs") {
			if (currentPage < totalPages) currentPage++;
		} else if (pageType === "cookieadmin-last-consent-logs") {
			currentPage = totalPages;
		}

		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: "POST",
			data: {
				action: 'cookieadmin_pro_ajax_handler',
				cookieadmin_act: 'get_consent_logs',
				current_page: currentPage,
				cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce,
			},
			success: function(response) {
				if (response.success) {
					let data = response.data; // Get response data
					
					// Update the current page input field
					$("#current-page-selector").val(data.current_page);

					// Update the counts
					$(".displaying-num").text(data.min_items+" - "+data.max_items);
					$(".max-num").text(data.total_logs);
					$(".total-pages").text(data.total_pages);

					// Select the table body (excluding headers)
					let logsContainer = $(".cookieadmin-consent-logs-result tbody");

					// Clear all the rows in tbody
					logsContainer.find("tr").remove();
					
					// Append new rows with updated logs
					if (data.logs.length > 0) {  // Fix: Check `data.logs`, not `data`
						$.each(data.logs, function(index, log) {  // Fix: `data.logs`
							var status_badge = "warning";
							if(log.consent_status_raw.toLowerCase() == 'accept'){
								status_badge = "success";
							}else if(log.consent_status_raw.toLowerCase() == 'reject'){
								status_badge = "danger";
							}
							logsContainer.append(`
								<tr>
									<td>${log.consent_id}</td>
									<td><span class="cookieadmin-badge cookieadmin-${status_badge}">${log.consent_status}</td>
									<td>${log.country || '—'}</td>
									<td>${log.user_ip}</td>
									<td>${log.consent_time}</td>
								</tr>
							`);
						});
					} else {
						logsContainer.append(`<tr><td colspan="4">No consent logs recorded yet!</td></tr>`);
					}
					
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function(xhr, status, error) {
				console.log('CookieAdmin AJAX Error:', status, error);
				console.log('CookieAdmin response: ' + xhr.responseText); // Check the error message
				alert("Request failed: " + error);
			}
		});
	}

	$('.cookieadmin-purge-consent-btn').on('click', cookieadminProPurgeConsentNow);
	
});

function cookieadminProPurgeConsentNow(){

	var logs_expiry = jQuery('#cookieadmin_consent_logs_expiry_days').val();
	if(!jQuery.isNumeric(logs_expiry)){
		alert('Logs Expiry days cannot be empty and should contain numbers only.');
		return;
	}
	
	if(!confirm('Consent logs older than ' +logs_expiry+ ' days will be deleted. Are you sure you want to continue.?')){
		return;
	}
	
	jQuery.ajax({
		url: cookieadmin_pro_policy.admin_url,
		method: "POST",
		data : {
			action : 'cookieadmin_pro_ajax_handler',
			cookieadmin_act : 'purne_consents',
			consent_logs_expiry : logs_expiry,
			cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce,
		},
		success: function(result){
			if(!result.success && result.message){
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
	
function cookieadminProSaveCookie(cookie_info){

	jQuery.ajax({
		url: cookieadmin_pro_policy.admin_url,
		method: "POST",
		data : {
			action : 'cookieadmin_pro_ajax_handler',
			cookieadmin_act : 'add_cookie',
			cookie_info : cookie_info,
			cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce,
		},
		success: function(result){
			
			if(result.success){
				cookie_info.id = result.data;
				cookieadminAddCookieToTable(cookie_info);
				jQuery('#cookieadmin-message').removeClass().addClass('cookieadmin-success-message').text(result.message);
			}else{
				alert(result.message);
			}
		}
	});

	jQuery("#cookieadmin_dialog_save_btn").prop("disabled", false);
 }

jQuery(document).ready(function($){

	if(!$('#cookieadmin_sp_cards').length){
		return;
	}

	var sp = {
		preloaded: cookieadmin_pro_policy.sp_preloaded_pages || [],
		hasWC: cookieadmin_pro_policy.sp_has_woocommerce || false,
		homeUrl: cookieadmin_pro_policy.sp_home_url || '',
		lang: cookieadmin_pro_policy.lang || {},
		searchTimer: null,
		MAX: 4
	};

	function spGetUserCards(){
		return $('#cookieadmin_sp_cards .cookieadmin-sp-card:not(.cookieadmin-sp-card--home)');
	}

	function spGetCount(){
		return spGetUserCards().length;
	}

	function spUpdateCounter(){
		var count = spGetCount() + 1;
		$('.cookieadmin-sp-count').text(count + ' ' + 'page(s) selected');
		spCheckLimit();
	}

	function spCheckLimit(){
		var $search = $('#cookieadmin_sp_search');
		if(spGetCount() >= sp.MAX){
			$search.prop('disabled', true).attr('placeholder', sp.lang.sp_max_reached);
			spCloseDropdown();
		}else{
			$search.prop('disabled', false).attr('placeholder', sp.lang.sp_search_placeholder);
		}
	}

	function spHasVal(val){
		return $('#cookieadmin_sp_cards .cookieadmin-sp-card[data-val="'+val+'"]').length > 0;
	}

	function spGetSelectedIds(){
		var ids = [];
		spGetUserCards().each(function(){
			var v = $(this).data('val');
			if(typeof v === 'number' || (typeof v === 'string' && /^\d+$/.test(v))){
				ids.push(parseInt(v, 10));
			}
		});
		return ids;
	}

	function spBuildPath(url){
		if(!url) return '';
		var path = url.replace(sp.homeUrl, '');
		return path || '/';
	}

	function spGetIcon(val){
		if(val === 'latest_post') return 'dashicons-admin-post';
		if(val === 'latest_product') return 'dashicons-products';
		return 'dashicons-admin-page';
	}

	function spGetType(val){
		if(val === 'home') return 'home';
		if(val === 'latest_post') return 'latest_post';
		if(val === 'latest_product') return 'latest_product';
		return 'page';
	}

	function spAddCard(val, title, url){
		if(spGetCount() >= sp.MAX) return;
		if(spHasVal(val)) return;

		var type = spGetType(val);
		var path = spBuildPath(url);
		var $card = $('<div class="cookieadmin-sp-card cookieadmin-sp-card--'+type+'" data-val="'+spEscapeHtml(String(val))+'">' +
			'<div class="cookieadmin-sp-card-title">' +
			'<span class="dashicons '+spGetIcon(val)+'"></span>' +
			'<span class="cookieadmin-sp-card-name">'+spEscapeHtml(title)+'</span>' +
			'<span class="cookieadmin-sp-card-remove" title="Remove">&times;</span>' +
			'</div>' +
			(path && path !== '/' ? '<div class="cookieadmin-sp-card-url">'+spEscapeHtml(path)+'</div>' : '') +
			'</div>');

		$('#cookieadmin_sp_cards').append($card);
		spUpdateCounter();
		spAutoSave();
	}

	function spRemoveCard($card){
		$card.remove();
		spUpdateCounter();
		spAutoSave();
	}

	function spAutoSave(){
		var selections = [];
		spGetUserCards().each(function(){
			selections.push($(this).data('val'));
		});

		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: 'POST',
			data: {
				action: 'cookieadmin_pro_ajax_handler',
				cookieadmin_act: 'save_scan_pages',
				selections: selections,
				cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce
			},
			success: function(res){
				if(res && res.success){
					spShowSaved();
				}else{
					spShowError();
				}
			},
			error: function(){
				spShowError();
			}
		});
	}

	function spShowSaved(){
		var $el = $('#cookieadmin_sp_saved_text');
		$el.text(sp.lang.sp_saved).addClass('cookieadmin-sp-saved-visible');
		clearTimeout(sp._savedTimer);
		sp._savedTimer = setTimeout(function(){
			$el.removeClass('cookieadmin-sp-saved-visible');
		}, 2000);
	}

	function spShowError(){
		alert(sp.lang.sp_save_error);
		$('#cookieadmin_sp_cards .cookieadmin-sp-card:last').addClass('cookieadmin-sp-card--warning');
	}

	function spRenderDropdown(items){
		var $dd = $('#cookieadmin_sp_dropdown');
		$dd.empty();

		if(spGetCount() >= sp.MAX){
			$dd.hide();
			return;
		}

		var hasItems = false;

		if(!spHasVal('latest_post')){
			var $postOpt = $('<div class="cookieadmin-sp-dropdown-item cookieadmin-sp-dropdown-item--static" data-val="latest_post" data-title="Single Post">' +
				'<span class="cookieadmin-sp-type-badge cookieadmin-sp-type-badge--post">'+sp.lang.sp_post_badge+'</span>' +
				'<span class="cookieadmin-sp-dropdown-item-title">'+sp.lang.sp_single_post+'</span>' +
				'</div>');
			$dd.append($postOpt);
			hasItems = true;
		}

		if(sp.hasWC && !spHasVal('latest_product')){
			var $prodOpt = $('<div class="cookieadmin-sp-dropdown-item cookieadmin-sp-dropdown-item--static" data-val="latest_product" data-title="Single Product">' +
				'<span class="cookieadmin-sp-type-badge cookieadmin-sp-type-badge--product">'+sp.lang.sp_product_badge+'</span>' +
				'<span class="cookieadmin-sp-dropdown-item-title">'+sp.lang.sp_single_product+'</span>' +
				'</div>');
			$dd.append($prodOpt);
			hasItems = true;
		}

		var selectedIds = spGetSelectedIds();

		for(var i = 0; i < items.length; i++){
			var item = items[i];
			if(selectedIds.indexOf(item.id) !== -1) continue;

			var path = spBuildPath(item.url);
			$dd.append(
				'<div class="cookieadmin-sp-dropdown-item" data-val="'+item.id+'" data-title="'+spEscapeHtml(item.title)+'" data-url="'+spEscapeHtml(item.url)+'">' +
				'<span class="cookieadmin-sp-type-badge cookieadmin-sp-type-badge--page">'+sp.lang.sp_page_badge+'</span>' +
				'<span class="cookieadmin-sp-dropdown-item-title">'+spEscapeHtml(item.title)+'</span>' +
				'<span class="cookieadmin-sp-dropdown-item-url">'+spEscapeHtml(path)+'</span>' +
				'</div>'
			);
			hasItems = true;
		}

		if(!hasItems){
			$dd.append('<div class="cookieadmin-sp-dropdown-empty">'+sp.lang.sp_no_results+'</div>');
		}

		$dd.show();
	}

	function spCloseDropdown(){
		$('#cookieadmin_sp_dropdown').hide().empty();
	}

	function spEscapeHtml(str){
		if(!str) return '';
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	function spLocalFilter(term){
		var results = [];
		var lower = term.toLowerCase();
		var selectedIds = spGetSelectedIds();
		for(var i = 0; i < sp.preloaded.length; i++){
			var p = sp.preloaded[i];
			if(selectedIds.indexOf(p.id) !== -1) continue;
			if(p.title.toLowerCase().indexOf(lower) !== -1){
				results.push(p);
			}
		}
		return results;
	}

	$('#cookieadmin_sp_search').on('focus', function(){
		if(spGetCount() >= sp.MAX) return;
		var val = $(this).val().trim();
		if(val.length === 0){
			spRenderDropdown(sp.preloaded);
		}
	});

	$('#cookieadmin_sp_search').on('input', function(){
		var term = $(this).val().trim();
		if(!term){
			spRenderDropdown(sp.preloaded);
			return;
		}

		var local = spLocalFilter(term);
		if(local.length > 0){
			spRenderDropdown(local);
		}else if(term.length >= 2){
			clearTimeout(sp.searchTimer);
			sp.searchTimer = setTimeout(function(){
				$.ajax({
					url: cookieadmin_pro_policy.admin_url,
					method: 'POST',
					data: {
						action: 'cookieadmin_pro_ajax_handler',
						cookieadmin_act: 'search_scan_content',
						s: term,
						selected_ids: spGetSelectedIds(),
						cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce
					},
					success: function(res){
						if(res && res.success){
							spRenderDropdown(res.data || []);
						}else{
							spRenderDropdown([]);
						}
					},
					error: function(){
						spRenderDropdown([]);
					}
				});
			}, 300);
		}else{
			spRenderDropdown([]);
		}
	});

	$(document).on('click', '#cookieadmin_sp_dropdown .cookieadmin-sp-dropdown-item:not(.cookieadmin-sp-dropdown-item--disabled)', function(){
		var val = $(this).data('val');
		var title = $(this).data('title') || '';
		var url = $(this).data('url') || '';

		spAddCard(val, title, url);
		$('#cookieadmin_sp_search').val('');
		spCloseDropdown();
	});

	$(document).on('click', function(e){
		if(!$(e.target).closest('.cookieadmin-sp-search-wrap').length){
			spCloseDropdown();
		}
	});

	$('#cookieadmin_sp_cards').on('click', '.cookieadmin-sp-card-remove', function(e){
		e.stopPropagation();
		var $card = $(this).closest('.cookieadmin-sp-card');
		spRemoveCard($card);
	});

	$('#cookieadmin_sp_reset').on('click', function(){
		if(!confirm(sp.lang.sp_reset_confirm)) return;
		spGetUserCards().remove();
		spUpdateCounter();
		spAutoSave();
	});

	spCheckLimit();
});