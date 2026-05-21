<?php

namespace CookieAdmin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

$content = array(
	'cookieadmin_layout' => array(
		'box' => 
			'<div class="cookieadmin_law_container">
				<div class="cookieadmin_consent_inside">
					<p id="cookieadmin_notice_title"></p>
					<div class="cookieadmin_notice_con">
						<p id="cookieadmin_notice"></p>
					</div>
					<div class="cookieadmin_consent_btns">
						<button type="button" class="cookieadmin_btn cookieadmin_customize_btn" id="cookieadmin_customize_button">Customize</button>
						<button type="button" class="cookieadmin_btn cookieadmin_reject_btn" id="cookieadmin_reject_button">Reject All</button>
						<button type="button" class="cookieadmin_btn cookieadmin_accept_btn" id="cookieadmin_accept_button">Accept All</button>
					</div>
					[[banner_policy_links]]
					[[powered_by_html]]
				</div>
			</div>',
		
		'footer' => 
			'<div class="cookieadmin_law_container">
				<div class="cookieadmin_consent_inside">
					<p id="cookieadmin_notice_title"></p>
					<div class="cookieadmin_notice_group">
						<div class="cookieadmin_notice_con">
							<p id="cookieadmin_notice"></p>
						</div>
						<div class="cookieadmin_consent_btns">
							<button type="button" class="cookieadmin_btn cookieadmin_customize_btn" id="cookieadmin_customize_button">Customize</button>
							<button type="button" class="cookieadmin_btn cookieadmin_reject_btn" id="cookieadmin_reject_button">Reject All</button>
							<button type="button" class="cookieadmin_btn cookieadmin_accept_btn" id="cookieadmin_accept_button">Accept All</button>
						</div>
					</div>
					[[banner_policy_links]]
					[[powered_by_html]]
				</div>
			</div>'
			
	),
	'cookieadmin_modal' => array(
		'center' => 
			'<div class="cookieadmin_cookie_modal">
				<div class="cookieadmin_mod_head">
					<span class="cookieadmin_preference_title" id="cookieadmin_preference_title"></span>
					<button type="button" class="cookieadmin_close_pref">&#10006;</button>
				</div>
				<div class="cookieadmin_details_wrapper">
					<div class="cookieadmin_preference_details" role="dialog" aria-modal="true" aria-label="[[cookie_preferences]]">
						<div id="cookieadmin_preference" class="cookieadmin_preference"></div>
					</div>
					<div id="cookieadmin_wrapper">
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-necessary-heading">
							<div class="cookieadmin_header"> 
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-necessary-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-necessary-heading" for="cookieadmin-necessary">[[necessary_cookies]]</label> 
								</span>
								<label class="cookieadmin_remark cookieadmin_act">[[remark_standard]]</label>								
							</div>
							<div class="cookieadmin_desc"> [[necessary_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-necessary">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-functional-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-functional-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-functional-heading" for="cookieadmin-functional">[[functional_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-functional-heading">
									<input type="checkbox" id="cookieadmin-functional">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[functional_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-functional">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-analytics-heading">
							<div class="cookieadmin_header">
								<span> 
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-analytics-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-analytics-heading" for="cookieadmin-analytics">[[analytical_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-analytics-heading">
									<input type="checkbox" id="cookieadmin-analytics"> 
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[analytical_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-analytics">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-marketing-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-marketing-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-marketing-heading" for="cookieadmin-marketing">[[advertisement_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-marketing-heading">
									<input type="checkbox" id="cookieadmin-marketing">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[advertisement_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-marketing">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-unclassified-heading" hidden>
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="un_c-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-unclassified-heading" for="cookieadmin-unclassified">[[unclassified_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
							</div>
							<div class="cookieadmin_desc"> [[unclassified_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list un_c">[[none]]</div>
						</div>
					</div>
				</div>
				<div class="cookieadmin_modal_footer">
					[[override_gpc]]
					<button type="button" class="cookieadmin_btn cookieadmin_reject_btn" id="cookieadmin_reject_modal_button">Reject All</button>
					<button type="button" class="cookieadmin_btn cookieadmin_save_btn" id="cookieadmin_prf_modal_button">Save My Preferences</button>
					<button type="button" id="cookieadmin_accept_modal_button" class="cookieadmin_btn cookieadmin_accept_btn">Accept All</button>
					<div class="cookieadmin_modal_footer_links">
					[[modal_policy_links]]
					[[powered_by_html]]
					</div>
				</div>
			</div>',
		
		'side' => '
			<div class="cookieadmin_cookie_modal">
				<div class="cookieadmin_mod_head">
					<span class="cookieadmin_preference_title" id="cookieadmin_preference_title"></span> 
					<button type="button" class="cookieadmin_close_pref">&#10006;</button> 
				</div>
				<div class="cookieadmin_details_wrapper">
					<div class="cookieadmin_preference_details" role="dialog" aria-modal="true" aria-label="[[cookie_preferences]]">
						<div id="cookieadmin_preference" class="cookieadmin_preference"> </div>
					</div>
					<div id="cookieadmin_wrapper">
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-necessary-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-necessary-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-necessary-heading" for="cookieadmin-necessary">[[necessary_cookies]]</label>
								</span>
								<label class="cookieadmin_remark cookieadmin_act">[[remark_standard]]</label>
							</div>
							<div class="cookieadmin_desc"> [[necessary_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-necessary">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-functional-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-functional-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-functional-heading" for="cookieadmin-functional">[[functional_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-functional-heading">
									<input type="checkbox" id="cookieadmin-functional">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[functional_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-functional">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-analytics-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-analytics-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-analytics-heading" for="cookieadmin-analytics">[[analytical_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-analytics-heading">
									<input type="checkbox" id="cookieadmin-analytics">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[analytical_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-analytics">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-marketing-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-marketing-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-marketing-heading" for="cookieadmin-marketing">[[advertisement_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-marketing-heading">
									<input type="checkbox" id="cookieadmin-marketing">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[advertisement_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-marketing">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-unclassified-heading" hidden>
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="un_c-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-unclassified-heading" for="cookieadmin-unclassified">[[unclassified_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
							</div>
							<div class="cookieadmin_desc"> [[unclassified_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list un_c">[[none]]</div>
						</div>
					</div>
				</div>
				<div class="cookieadmin_modal_footer">
					[[override_gpc]]
					<button type="button" class="cookieadmin_btn cookieadmin_reject_btn" id="cookieadmin_reject_modal_button">Reject All</button>
					<button type="button" class="cookieadmin_btn cookieadmin_save_btn" id="cookieadmin_prf_modal_button">Save My Preferences</button>
					<button type="button" class="cookieadmin_btn cookieadmin_accept_btn" id="cookieadmin_accept_modal_button">Accept All</button>
					<div class="cookieadmin_modal_footer_links">
					[[modal_policy_links]]
					[[powered_by_html]]
					</div>
				</div>
			</div>',
		
		'down' => 
			'<div class="cookieadmin_cookie_modal">
				<div class="cookieadmin_mod_head">
					<span class="cookieadmin_preference_title" id="cookieadmin_preference_title"></span>
					<button type="button" class="cookieadmin_close_pref">&#10006;</button>
				</div>
				<div class="cookieadmin_details_wrapper">
					<div class="cookieadmin_preference_details" role="dialog" aria-modal="true" aria-label="[[cookie_preferences]]">
						<div id="cookieadmin_preference" class="cookieadmin_preference"></div>
					</div>
					<div id="cookieadmin_wrapper">
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-necessary-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-necessary-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-necessary-heading" for="cookieadmin-necessary">[[necessary_cookies]]</label>
								</span>
								<label class="cookieadmin_remark cookieadmin_act">[[remark_standard]]</label>
							</div>
							<div class="cookieadmin_desc"> [[necessary_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-necessary">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-functional-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-functional-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-functional-heading" for="cookieadmin-functional">[[functional_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-functional-heading">
									<input type="checkbox" id="cookieadmin-functional">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[functional_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-functional">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-analytics-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-analytics-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-analytics-heading" for="cookieadmin-analytics">[[analytical_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-analytics-heading">
									<input type="checkbox" id="cookieadmin-analytics">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[analytical_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-analytics">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-marketing-heading">
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="cookieadmin-marketing-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-marketing-heading" for="cookieadmin-marketing">[[advertisement_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
								<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-marketing-heading">
									<input type="checkbox" id="cookieadmin-marketing">
									<span class="cookieadmin_slider"></span>
								</label>
							</div>
							<div class="cookieadmin_desc"> [[advertisement_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list cookieadmin-marketing">[[none]]</div>
						</div>
						<div class="cookieadmin_types" role="region" aria-labelledby="cookieadmin-unclassified-heading" hidden>
							<div class="cookieadmin_header">
								<span>
									<span class="cookieadmin_show_pref_cookies" id="un_c-container">&#9658;</span>
									<label class="stitle" id="cookieadmin-unclassified-heading" for="cookieadmin-unclassified">[[unclassified_cookies]]</label>
									<label class="cookieadmin_remark">[[remark]]</label>
								</span>
							</div>
							<div class="cookieadmin_desc"> [[unclassified_cookies_desc]] </div>
							<div class="cookieadmin-modal-cookies-list un_c">[[none]]</div>
						</div>
					</div>
				</div>
				<div class="cookieadmin_modal_footer">
					[[override_gpc]]
					<button type="button" class="cookieadmin_btn cookieadmin_reject_btn" id="cookieadmin_reject_modal_button">Reject All</button>
					<button type="button" class="cookieadmin_btn cookieadmin_save_btn" id="cookieadmin_prf_modal_button">Save My Preferences</button>
					<button type="button" class="cookieadmin_btn cookieadmin_accept_btn" id="cookieadmin_accept_modal_button">Accept All</button>
					<div class="cookieadmin_modal_footer_links">
					[[modal_policy_links]]
					[[powered_by_html]]
					</div>
				</div>
			</div>'
	),
	'cookieadmin_reconsent' => 
		'<div>
			<button type="button" class="cookieadmin_re_consent">
				<img class="cookieadmin_reconsent_img" height="40" width="40" src="[[reconsent_icon_url]]" alt="[[reconsent]]">
			</button>
		</div>'
);
