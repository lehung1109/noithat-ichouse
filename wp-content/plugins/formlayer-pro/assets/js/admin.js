jQuery(document).ready(function($){
	
	let currentSlug = '';

	const entryState = {
		page: 1,
		total_pages: parseInt($('.formlayer-pagination-controls').data('total-pages')) || 1
	};

	function show_toast(message, type = 'success') {
		let toast = $('<div>')
			.addClass('formlayer-toast')
			.addClass(type)
			.html(`<span class="dashicons dashicons-${type === 'success' ? 'yes' : 'warning'}"></span> ${message}`);

		$('body').append(toast);

		toast.fadeIn(300).delay(3000).fadeOut(300, function () {
			toast.remove();
		});
	}

	function update_unread_count(count) {
		count = parseInt(count);
		let $badge = $('.formlayer-unread-count');
		let $menu_badge = $('#toplevel_page_formlayer .plugin-count');

		if (count > 0) {
			if ($badge.length) {
				$badge.text(count).show();
			} else {
				$('[data-tab="entries"] span').after(` <span class="formlayer-unread-count formlayer-unread-badge">${count}</span>`);
			}

			if ($menu_badge.length) {
				$menu_badge.text(count).closest('.update-plugins').show();
			}
		} else {
			$badge.hide();
			$menu_badge.closest('.update-plugins').hide();
		}
	}

	function update_entry_row_ui(entry_id, status) {
		let $row = $(`tr[data-entry-id="${entry_id}"]`);
		if (!$row.length) return;

		$row.removeClass('entry-row-unread entry-row-read').addClass(`entry-row-${status}`);
		
		let $badge = $row.find('.formlayer-badge');
		$badge.removeClass('status-unread status-read').addClass(`status-${status}`).text(status.charAt(0).toUpperCase() + status.slice(1));
		
		let $toggle = $row.find('.formlayer-toggle-status');
		let new_target_status = (status === 'read' ? 'unread' : 'read');
		$toggle.data('status', new_target_status);
		$toggle.attr('title', `Mark as ${new_target_status.charAt(0).toUpperCase() + new_target_status.slice(1)}`);
		
		let $icon = $toggle.find('.dashicons');
		$icon.removeClass('dashicons-marker dashicons-email-alt').addClass(`dashicons-${status === 'read' ? 'marker' : 'email-alt'}`);
		$toggle.css('color', status === 'read' ? '#94a3b8' : 'var(--fl-primary)');
	}

	function load_entries(page = 1) {
		const $container = $('#formlayer-entries-tbody');
		if(!$container.length) return;
		
		$container.html('<tr><td colspan="6" style="text-align:center; padding:40px;">Loading entries...</td></tr>');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_filter_entries',
			nonce: formlayer_admin.nonce,
			search: $('#formlayer-entries-search').val(),
			form_id: $('#formlayer-entries-filter-form').val(),
			status: $('#formlayer-entries-filter-status').val(),
			page: page
		}, function(response){
			if(response.success){
				entryState.page = page;
				entryState.total_pages = response.data.total_pages || 1;
				$container.html(response.data.html);
				
				$('#formlayer-entries-count-current').text($container.find('tr').not(':has(td[colspan])').length);
				$('#formlayer-entries-total').text(response.data.total_entries);
				
				$('#formlayer-pagination-prev').prop('disabled', entryState.page <= 1);
				$('#formlayer-pagination-next').prop('disabled', entryState.page >= entryState.total_pages);

				if (response.data.unread_count !== undefined) {
					update_unread_count(response.data.unread_count);
				}
			}else{
				$container.html('<tr><td colspan="6" style="text-align:center; padding:40px; color:#ef4444;">Error loading entries.</td></tr>');
			}
		});
	}

	window.formlayerAdminPro = window.formlayerAdminPro || {};
	window.formlayerAdminPro.load_entries = load_entries;
	window.formlayerAdminPro.entryState = entryState;

	function openModal(name, slug){
		$('#int-modal-title').text('Configure ' + name);
		$('#int-modal-desc').text('Enter your ' + name + ' credentials below.');
		
		let fieldsHtml = getFieldsHtml(slug);
		$('#int-settings-fields').html(fieldsHtml);
		
		$('#formlayer-integration-modal').show(0, function(){
			$(this).addClass('active');
		});
	}

	function getFieldsHtml(slug){
		let html = '';
		let settings = formlayer_pro.settings[slug] || {};

		switch(slug){
			case 'slack':
				html += renderField('webhook', 'Webhook URL', 'https://hooks.slack.com/services/...', settings.webhook);
				break;
			case 'mailchimp':
				html += renderField('api_key', 'API Key', 'your-api-key', settings.api_key);
				html += renderField('list_id', 'Audience ID', 'List ID', settings.list_id);
				break;
			case 'sheets':
				html += renderTextarea('service_account_json', 'Service Account JSON', 'Paste your service account JSON here', settings.service_account_json);
				html += renderField('spreadsheet_id', 'Spreadsheet ID', 'Enter your Spreadsheet ID', settings.spreadsheet_id);
				html += renderField('sheet_name', 'Sheet Name', 'Sheet1', settings.sheet_name);
				break;
			case 'notion':
				html += renderField('api_key', 'Internal Integration Token', '', settings.api_key);
				html += renderField('database_id', 'Database ID', '', settings.database_id);
				break;
			case 'trello':
				html += renderField('api_key', 'API Key', '', settings.api_key);
				html += renderField('token', 'Token', '', settings.token);
				break;
			case 'discord':
				html += renderField('webhook', 'Webhook URL', 'https://discord.com/api/webhooks/...', settings.webhook);
				break;
		}

		return html;
	}

	// Template engine helper
	const utils = {
		tmpl: function(key, data = {}) {
			let html = formlayer_pro.html_templates[key] || '';
			Object.keys(data).forEach(k => {
				const val = data[k] === undefined || data[k] === null ? '' : data[k];
				html = html.replace(new RegExp(`{{${k}}}`, 'g'), val);
			});
			return html;
		}
	};

	function renderField(name, label, placeholder, value){
		return utils.tmpl('integration_field', {
			name: name,
			label: label,
			placeholder: placeholder,
			value: value || ''
		});
	}

	function renderTextarea(name, label, placeholder, value){
		return utils.tmpl('integration_textarea', {
			name: name,
			label: label,
			placeholder: placeholder,
			value: value || ''
		});
	}

	function saveSettings(){
		let $btn = $('#save-int-settings');
		let originalText = $btn.text();
		
		let settings = {};
		$('.int-setting-input').each(function(){
			settings[$(this).attr('name')] = $(this).val();
		});

		$btn.prop('disabled', true).text('Saving...');

		$.post(formlayer_pro.ajax_url, {
			action: 'formlayer_pro_save_integration',
			integration: currentSlug,
			settings: settings,
			nonce: formlayer_pro.nonce
		}, function(response){
			if(response.success){
				alert(response.data.message);
				// Update local storage of settings
				formlayer_pro.settings[currentSlug] = settings;
				
				$('#formlayer-integration-modal').removeClass('active');
				$btn.prop('disabled', false).text(originalText);
				
				// Update button UI
				$(`.formlayer-configure-int[data-slug="${currentSlug}"]`)
					.removeClass('formlayer-btn-action')
					.addClass('formlayer-btn-outline')
					.text('Connected');
			}else{
				alert(response.data.message || 'Error saving settings');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	}
	// Entries Panel
	$('#formlayer-entries-search, #formlayer-entries-filter-form, #formlayer-entries-filter-status').on('input change', function(){
		load_entries(1);
	});

	// Entry Pagination
	$('#formlayer-pagination-prev').on('click', function(){
		if(!$(this).prop('disabled') && entryState.page > 1) load_entries(entryState.page - 1);
	});

	$('#formlayer-pagination-next').on('click', function(){
		if(!$(this).prop('disabled') && entryState.page < entryState.total_pages) load_entries(entryState.page + 1);
	});

	// Entries Bulk Action
	$('#formlayer-entries-apply-bulk').on('click', function(){
		let action = $('#formlayer-entries-bulk-action').val(),
			ids = $('.entry-cb:checked').map(function(){ return $(this).val(); }).get();

		if(!action || ids.length === 0) return;

		if(!confirm('Are you sure you want to apply this action to selected entries?')) return;

		let $btn = $(this);
		$btn.prop('disabled', true);

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_entries_bulk_action',
			bulk_action: action,
			ids: ids,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				load_entries(entryState.page);
			}else{
				show_toast(response.data.message || 'Error applying bulk action', 'error');
			}
			$btn.prop('disabled', false);
		});
	});

	$('#formlayer-entries-select-all').on('change', function(){
		$('.entry-cb').prop('checked', $(this).prop('checked'));
	});

	// Export Entries Handler
	$('#formlayer-btn-export-entries').on('click', function(e){
		e.preventDefault();
		
		let form_id = $('#formlayer-entries-filter-form').val();
		let status = $('#formlayer-entries-filter-status').val();
		
		let $form = $('<form>', {
			action: formlayer_admin.ajax_url,
			method: 'POST'
		});
		
		$form.append($('<input>', { type: 'hidden', name: 'action', value: 'formlayer_export_csv' }));
		$form.append($('<input>', { type: 'hidden', name: 'nonce', value: formlayer_admin.nonce }));
		$form.append($('<input>', { type: 'hidden', name: 'form_id', value: form_id }));
		$form.append($('<input>', { type: 'hidden', name: 'status', value: status }));
		
		$('body').append($form);
		$form.submit();
		$form.remove();
	});

	// View Entry Handler
	$('#formlayer-entries-tbody').on('click', '.formlayer-view-entry', function(e){
		e.preventDefault();
		let entry_id = $(this).data('id') || $(this).data('entry-id');
		if(!entry_id) return;

		$('#formlayer-entry-detail-body').html('<div style="padding:40px; text-align:center;">Loading entry details...</div>');
		$('#formlayer-entry-modal').addClass('active');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_get_entry_details',
			entry_id: entry_id,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				$('#formlayer-entry-detail-body').html(response.data.html);
				update_entry_row_ui(entry_id, 'read');
				if (response.data.unread_count !== undefined) {
					update_unread_count(response.data.unread_count);
				}
			}else{
				$('#formlayer-entry-detail-body').html('<div style="padding:40px; color:#ef4444;">' + (response.data.message || 'Error loading details') + '</div>');
			}
		});
	});

	// Toggle Entry Status Handler
	$('#formlayer-entries-tbody').on('click', '.formlayer-toggle-status', function(e){
		e.preventDefault();
		let $btn = $(this);
		let entry_id = $btn.data('entry-id');
		let status = $btn.data('status');
		if(!entry_id) return;

		$btn.css('opacity', '0.5');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_toggle_entry_status',
			entry_id: entry_id,
			status: status,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				update_entry_row_ui(entry_id, status);
				if (response.data.unread_count !== undefined) {
					update_unread_count(response.data.unread_count);
				}
			}else{
				show_toast(response.data.message || 'Error updating status', 'error');
			}
			$btn.css('opacity', '1');
		});
	});

	// Delete Entry Handler
	$('#formlayer-entries-tbody').on('click', '.formlayer-delete-entry', function(e){
		e.preventDefault();
		const entry_id = $(this).data('id') || $(this).data('entry-id');
		if(!entry_id) return;

		if(!confirm('Are you sure you want to delete this entry?')){
			return;
		}

		const $row = $(this).closest('tr');
		$row.css('opacity', '0.5');

		$.post(formlayer_admin.ajax_url, {
			action: 'formlayer_delete_entry',
			entry_id: entry_id,
			nonce: formlayer_admin.nonce
		}, function(response){
			if(response.success){
				$row.fadeOut(300, function(){ $(this).remove(); });
				if (response.data.unread_count !== undefined) {
					update_unread_count(response.data.unread_count);
				}
			}else{
				show_toast(response.data.message || 'Error deleting entry', 'error');
				$row.css('opacity', '1');
			}
		});
	});
	// Event Listeners
	$(document).on('click', '.formlayer-configure-int', function(e){
		e.preventDefault();
		let $btn = $(this);
		currentSlug = $btn.data('slug');
		let name = $btn.data('name');

		openModal(name, currentSlug);
	});

	$(document).on('click', '#close-int-modal, .formlayer-modal-overlay', function(e){
		if(e.target === this || $(this).attr('id') === 'close-int-modal'){
			$('#formlayer-integration-modal').removeClass('active');
			setTimeout(function(){
				$('#formlayer-integration-modal').hide();
			}, 300);
		}
	});

	$(document).on('click', '#save-int-settings', function(){
		saveSettings();
	});

});
