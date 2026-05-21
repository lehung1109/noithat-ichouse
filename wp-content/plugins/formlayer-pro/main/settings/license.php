<?php
/*
* FormLayer Pro
* https://formlayer.net
* (c) FormLayer Team
*/

namespace FormlayerPro\Settings;

// Are we being accessed directly ?
if(!defined('ABSPATH')){
	exit;
}

class License{

	static function template(){
		global $formlayer;
		
		// Add header
		if(isset($_REQUEST['save_formlayer_pro_license'])){
			self::save();
		}
		
		// Handle delete license
		if(isset($_REQUEST['delete_formlayer_pro_license'])){
			self::delete();
		}

		echo '<div class="formlayer-license-tab">
				<div class="formlayer-license-card">
					
					<!-- Version Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('FormLayer Version', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value">' . (defined('FORMLAYER_PRO_VERSION') ? esc_html(FORMLAYER_PRO_VERSION) . ' (Pro Version)' : 'N/A') . '</div>
					</div>

					<!-- License Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('FormLayer License', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value">
							<form method="post" action="" class="formlayer-license-input-wrapper">
							    <input type="hidden" name="formlayer_pro_license_nonce" value="' . esc_attr( wp_create_nonce( 'formlayer_pro_license' ) ) . '" />

								<div class="formlayer-license-input-row">
									' . (defined('FORMLAYER_PRO_VERSION') && empty($formlayer->license['active']) ? '<span class="formlayer-license-badge">Unlicensed</span>' : '') . '
									<input type="text" name="formlayer_pro_license" class="formlayer-license-input" value="' . (empty($formlayer->license['license']) ? '' : esc_html($formlayer->license['license'])) . '" placeholder="FORML-11111-22222-33333-44444">
								</div>

								<div class="formlayer-license-buttons">
									<button name="save_formlayer_pro_license" class="formlayer-license-btn formlayer-license-btn-update" type="submit">' . esc_html__('Update License', 'formlayer-pro') . '</button>';
									
									// Show delete button only if license exists
									if(!empty($formlayer->license['license'])) {
										echo '<button name="delete_formlayer_pro_license" class="formlayer-license-btn formlayer-license-btn-delete" type="submit" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete the license? This will deactivate your license on this site.', 'formlayer-pro')) . '\')">' . esc_html__('Delete License', 'formlayer-pro') . '</button>';
									}
									
									echo '</div>';

								if(!empty($formlayer->license)){
									$expires = $formlayer->license['expires'];
									$expires = substr($expires, 0, 4) . '/' . substr($expires, 4, 2) . '/' . substr($expires, 6);
									echo '<div class="formlayer-license-info">
										<span>License Status: <b>' . (empty($formlayer->license['status_txt']) ? 'N.A.' : wp_kses_post($formlayer->license['status_txt'])) . '</b></span>
										' . ($formlayer->license['expires'] <= gmdate('Ymd') ? '<span>License Expires: <b class="formlayer-text-danger">' . esc_attr($expires) . '</b></span>' : (empty($formlayer->license['has_plid']) ? '<span>License Expires: <b>' . esc_html($expires) . '</b></span>' : '')) . '
									</div>';
								}
						echo '	</form>
						</div>
					</div>

					<!-- URL Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('URL', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value mono">' . esc_url(get_site_url()) . '</div>
					</div>

					<!-- Path Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('Path', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value mono">' . esc_html(ABSPATH) . '</div>
					</div>

					<!-- IP Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('Server\'s IP Address', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value mono">' . esc_html($_SERVER['SERVER_ADDR']) . '</div>
					</div>

					<!-- Writable Row -->
					<div class="formlayer-license-row">
						<div class="formlayer-license-label">' . esc_html__('.htaccess is writable', 'formlayer-pro') . '</div>
						<div class="formlayer-license-value">
							' . (is_writable(ABSPATH . '.htaccess') ? '<span class="formlayer-text-success">Yes</span>' : '<span class="formlayer-text-danger">No</span>') . '
						</div>
					</div>
				</div>
		</div>';
	}

	static function save(){
		global $formlayer, $lic_resp;

		// Verify nonce
		if(!isset($_POST['formlayer_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['formlayer_pro_license_nonce'])), 'formlayer_pro_license')){
			echo '<div class="formlayer-license-notice formlayer-license-notice-error">'.esc_html__('Nonce verification failed', 'formlayer-pro').'</div>';
			return;
		}

		$license = sanitize_text_field(wp_unslash($_POST['formlayer_pro_license']));

		if(empty($license)){
			echo '<div class="formlayer-license-notice formlayer-license-notice-error">'.esc_html__('Please enter a license key.', 'formlayer-pro').'</div>';
			return;
		}
		
		formlayer_pro_load_license($license);
		
		if(is_wp_error($lic_resp) || 200 !== wp_remote_retrieve_response_code($lic_resp)){
			if(is_wp_error($lic_resp)){
				echo '<div class="formlayer-license-notice formlayer-license-notice-error">' . esc_html($lic_resp->get_error_message()) . '</div>';
				return;
			} else{
				echo '<div class="formlayer-license-notice formlayer-license-notice-error">'.
					esc_html__('An error occurred, please try again. Response code: ', 'formlayer-pro') . esc_attr(wp_remote_retrieve_response_code($lic_resp))
				.'</div>';
				return;
			}
		} else {
			$tmp = json_decode(wp_remote_retrieve_body($lic_resp), true);
			if(empty($tmp)){
				echo '<div class="formlayer-license-notice formlayer-license-notice-error">'.
				esc_html__('Invalid license key', 'formlayer-pro').'</div>';
				return;
			}
			
			echo '<div class="formlayer-license-notice formlayer-license-notice-success">'.
			esc_html__('License activated successfully', 'formlayer-pro').'</div>';
		}
	}

	static function delete(){
		global $formlayer;

		// Verify nonce
		if(!isset($_POST['formlayer_pro_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['formlayer_pro_license_nonce'])), 'formlayer_pro_license')){
			echo '<div class="formlayer-license-notice formlayer-license-notice-error">
			'.esc_html__('Nonce verification failed', 'formlayer-pro').'</div>';
			return;
		}

		if(isset($_POST['delete_formlayer_pro_license'])){
			// Delete the license option
			delete_option('formlayer_license');

			// Clear the global license data
			if(isset($formlayer->license)) {
				$formlayer->license = array();
			}
			
			echo '<div class="formlayer-license-notice formlayer-license-notice-success">'.
			 esc_html__('License deleted successfully', 'formlayer-pro').'</div>';
		}
	}
}