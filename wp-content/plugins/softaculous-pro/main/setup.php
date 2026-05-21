<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

$steps = array(
	'start' => __('Let\'s start', 'softaculous-pro'),
	'contact' => __('Contact','softaculous-pro'),
	'features' => __('Goals','softaculous-pro'),
	'images' => __('Images','softaculous-pro'),
	'import_theme' => __('Template','softaculous-pro'),
);

// Option table values 
$setup_info = softaculous_pro_get_option_setup_info();
// pagelayer_print($setup_info);
$business_email = get_option('pagelayer_cf_from_email', get_option('admin_email'));
$business_phone = get_option('pagelayer-phone', '');
$business_address = get_option('pagelayer-address', '');

// Social accounts
$social_facebook = get_option('pagelayer-facebook-url', '');
$social_twitter = get_option('pagelayer-twitter-url', '');
$social_instagram = get_option('pagelayer-instagram-url', '');
$social_linkedin = get_option('pagelayer-linkedin-url', '');
$social_youtube = get_option('pagelayer-youtube-url', '');

$setup_info = empty($setup_info) ? [] : $setup_info;
$business_type = !empty($setup_info['business_type']) ? $setup_info['business_type'] : '';
$business_descriptions = isset($setup_info['business_description']) && is_array($setup_info['business_description'])  ? $setup_info['business_description'] : [''];
$active_desc_index = isset($setup_info['active_desc']) ? $setup_info['active_desc'] : 0;
$total_descriptions = count($business_descriptions);
$selected_images = !empty($setup_info) && !empty($setup_info['selected_images']) ? $setup_info['selected_images'] : [];
$suggested_tags = !empty($setup_info) && !empty($setup_info['image_suggestions']) ? $setup_info['image_suggestions'] : (!empty($business_type) ? [$business_type] : '');
$template_mode = !empty($setup_info['mode']) ? $setup_info['mode']: 'manual';
$business_lang = !empty($setup_info) && !empty($setup_info['business_lang']) ? $setup_info['business_lang'] : 'en';

$active_step = isset($_GET['step']) && array_key_exists($_GET['step'], $steps) ? $_GET['step'] : 'start';

if (empty($business_descriptions)) {
	$active_step = 'start';
}

include_once(dirname(__FILE__).'/onboarding.php');

$softaculous_pro['templates'] = softaculous_pro_get_templates_list();
$spro_onboarding_done = get_option('softaculous_pro_onboarding_done');

update_option('softaculous_pro_onboarding_shown', time()); // Change this for AI

echo '
<script id="softaculous-ai-config">
var softaculous_ai_config = {};
softaculous_ai_config.descriptions = '.json_encode($business_descriptions).';
softaculous_ai_config.desc_active = "'.$active_desc_index.'";
softaculous_ai_config.selected_images = '.json_encode($selected_images).';
softaculous_ai_config.suggested_tags = '.json_encode($suggested_tags).';
softaculous_ai_config.template_mode = '.json_encode($template_mode).';
</script>
';

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$installed_plugins = get_plugins();

if(!empty($softaculous_pro['branding']['default_hf_bg'])){
	echo '
	<style>
	.softaculous-pro-wizard-sidebar {
		background-color:'.$softaculous_pro['branding']['default_hf_bg'].' !important;
	}
	</style>';
}

if(!empty($softaculous_pro['branding']['default_hf_text'])){
	echo '
	<style>
	.softaculous-pro-wizard-steps li, .softaculous-pro-wizard-steps li::before {
		color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
		border-color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
	}
	.softaculous_pro_return_btn span, .dashicons-exit::before {
		color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
	}
	</style>';
}

?>
<style>
*,
*::before,
*::after {
	box-sizing: content-box;
}
</style>

<div class="softaculous-pro-wizard softaculous-pro-ai-wizard">
	<div class="softaculous-pro-wizard-sidebar">
		<div class="softaculous-pro-setup-logo">
			<a href="<?php echo admin_url('admin.php?page=assistant&act=onboarding'); ?>">
				<img src="<?php echo esc_attr($softaculous_pro['branding']['logo_url']);?>" style="max-width:200px;" />
			</a>
		</div>
		<div class="softaculous-pro-steps-holder">
			<ol class="softaculous-pro-wizard-steps">
				<?php foreach ($steps as $key => $name) : ?>
				<a href="admin.php?page=assistant&act=onboarding&step=<?php echo $key; ?>">
					<li class="<?php echo ($key == $active_step ? 'active_step' : ''); ?>">
						<span data-step="<?php echo $key; ?>"><?php echo $name; ?></span>
					</li>
				</a>
				<?php endforeach; ?>
			</ol>
		</div>
		<a class="softaculous_pro_return_btn" style="cursor:pointer;" onclick="return softaculous_pro_onboarding_dismiss(event);">
		<span class="dashicons dashicons-exit"></span><span><?php _e('Exit', 'softaculous-pro'); ?></span></a>
	</div>
	
	<div class="softaculous-pro-wizard-content" data-active-panel="<?php echo $active_step; ?>">
		<!-- Step Start section -->
		<div class="softaculous-pro-wizard-inner" data-panel="start">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('Welcome to the Onboarding process !', 'softaculous-pro'); ?></h1>
				<p><?php _e('This process will help you choose a professional template for your website and install plugins that you might need to achieve your goal for creating this website', 'softaculous-pro'); ?></p>
				<?php if(defined('PAGELAYER_VERSION') && version_compare(PAGELAYER_VERSION, SOFTACULOUS_PRO_AI_PL_VER, '<')){ ?>
					<div class="softaculous-pro-wizard-buttons softaculous-pro-alert softaculous-pro-alert-warning">
						<label style="cursor:pointer;">
							<?php _e('AI builder requires Pagelayer version '.SOFTACULOUS_PRO_AI_PL_VER.' or higher to function without interruptions. Please update Pagelayer to the latest version for full compatibility', 'softaculous-pro'); ?>
						</label>
					</div>
				<?php 
				}
				
				if (!empty($spro_onboarding_done)) : ?>
					<div class="softaculous-pro-wizard-buttons softaculous-pro-alert softaculous-pro-alert-danger">
						<input type="checkbox" id="onboarding_done_confirm" name="onboarding_done_confirm" style="margin:0px;" />&nbsp;&nbsp;
						<label for="onboarding_done_confirm" style="cursor:pointer;">
							<?php _e('It looks like you have already completed the onboarding process. You might lose data if you run the onboarding process again. Select this checkbox to confirm that you agree.', 'softaculous-pro'); ?>
						</label>
					</div>
				<?php endif; ?>
			</div>

			<div class="softaculous-form-container">
				<div class='softaculous-container-full'>
					<div class='softaculous-content'>
						<label for="softaculous-site-name" style="cursor:pointer;"><?php _e('Name of the website:', 'softaculous-pro'); ?></label>
						<input type="text" name='site_name' id="softaculous-site-name" class="softaculous_pro_input" placeholder="<?php _e('Enter a name for your website', 'softaculous-pro'); ?>" value="<?php echo esc_attr(get_bloginfo('name')); ?>" autocomplete="off"/>
					</div>
				</div>

				<div class='softaculous-container-half'>
					<div class="softaculous-content">
						<label for="softaculous-business-type" style="cursor:pointer;"><?php _e('Choose a Category:', 'softaculous-pro'); ?></label>
						<div class="softaculous-dropdown">
							<div class="softaculous-dropdown-selected">
								<input type="text" class="softaculous_input" value="<?php echo esc_attr($business_type); ?>" name="business_type" id="softaculous-business-type" placeholder="<?php _e('Explore Options', 'softaculous-pro'); ?>" autocomplete="off"/>
								<span class="clear-btn" style="display: <?php echo !empty($business_type) ? 'block' : 'none'; ?>">&#10006;</span>
							</div>
							<div class="softaculous-category-holder">
								<div class="softaculous-pro-categories-dropdownlist"></div>
								<div class="softaculous-pro-categories-default-dropdownlist" style='display:none'>
									<?php foreach ($softaculous_pro['templates']['categories'] as $cslug => $cdata) : ?>
										<div class="softaculous-category_btn" data-target="<?php echo esc_attr($cslug); ?>">
											<?php echo esc_html($cdata['en']); ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<div class='softaculous-content'>
						<label for="locale" style="cursor:pointer;"><?php _e('Website Language:', 'softaculous-pro'); ?></label>
						<?php 
						wp_dropdown_languages(array(
							'name'     => 'language',
							'selected' => $business_lang,
						));
						?>
					</div>
				</div>
			</div>
			
			<div class="softaculous-ai-description softaculous-content">			
				<table>
					<tr>
						<td width="1px">
							<input type="checkbox" name="mode" id="softaculous-ai-mode" onclick="softaculous_pro_set_mode()" style="width: inherit !important;" <?php echo ($setup_info['mode'] == 'ai' ? 'checked' : '');?> />
						</td>
						<td><label for="softaculous-ai-mode" style="cursor:pointer;"><?php _e('Use AI Builder to generate content ?', 'softaculous-pro'); ?></label></td>
					</tr>
				</table>
				
				<div id="softaculous-ai-description-field" style="<?php echo $template_mode == 'manual' ? 'display:none' : 'display:block'?>">
				
				<label for="softaculous-business-desc" style="cursor:pointer;"><?php _e('What\'s your website about ?', 'softaculous-pro'); ?></label>
				<div class='softaculous-pro-active-desc' data-active="<?php echo esc_attr($active_desc_index); ?>">
					<p class='softaculous-pro-description-error' style='display:none'><?php _e('It seems that the description is insufficient. Please provide a brief overview of your site before proceeding further. You can write your own description, or let AI generate one for you.', 'softaculous-pro');?></p>
					<textarea rows="4" cols="50" id="softaculous-business-desc" placeholder="<?php _e('E.g. Quorvio is a next-gen tech startup based in Austin, Texas. The company is focused on building intuitive tools that simplify software deployment, automation, and infrastructure management. Founded by Morgan Hale, a product architect with 12+ years in the tech space, Quorvio brings together simplicity, power, and developer-first thinking. With a clear mission to streamline digital operations, the team is dedicated to shaping the future of web and app development.');?>"><?php echo esc_html( isset($business_descriptions[$active_desc_index]) ?  $business_descriptions[$active_desc_index] : ''); ?></textarea>
					<div class="softaculous-ai-description-creation">
						<div class="softaculous-ai-svg">
							<svg width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="m11 4-.5-1-.5 1-1 .125.834.708L9.5 6l1-.666 1 .666-.334-1.167.834-.708zm8.334 10.666L18.5 13l-.834 1.666-1.666.209 1.389 1.181L16.834 18l1.666-1.111L20.166 18l-.555-1.944L21 14.875zM6.667 6.333 6 5l-.667 1.333L4 6.5l1.111.944L4.667 9 6 8.111 7.333 9l-.444-1.556L8 6.5zM3.414 17c0 .534.208 1.036.586 1.414L5.586 20c.378.378.88.586 1.414.586s1.036-.208 1.414-.586L20 8.414c.378-.378.586-.88.586-1.414S20.378 5.964 20 5.586L18.414 4c-.756-.756-2.072-.756-2.828 0L4 15.586c-.378.378-.586.88-.586 1.414zM17 5.414 18.586 7 15 10.586 13.414 9 17 5.414z"/>
							</svg>
							<span class="softaculous-ai-loader"></span>
							<p><?php _e('Create description using AI', 'softaculous-pro');?></p>
						</div>
						<div class="softaculous-ai-pagination" data-current="<?php echo esc_attr($active_desc_index); ?>">
							<span class="active-prev-handler"> <i class="fas fa-angle-left"></i> </span>
							<span class="currently_active"> <?php echo esc_html($active_desc_index+1); ?> / <?php echo esc_html( max(1, $total_descriptions)); ?> </span>
							<span class="active-next-handler"> <i class="fas fa-angle-right"></i> </span>
						</div>
					</div>
				</div>
				
				</div>
			</div>
			<br />
			<br />
			<div class="softaculous-pro-wizard-buttons">
				<button class="step_btn step_next_btn" data-step="contact" onclick="softaculous_pro_next_handler(this)"><?php _e('Get Started', 'softaculous-pro'); ?><span class="dashicons dashicons-arrow-right-alt next-handler-icon"></span><span class="softaculous-next-loading" style="display:none"></span></button>
				<button class="step_btn step_next_btn step_dismiss_btn" data-step="type" onclick="softaculous_pro_onboarding_dismiss(event);">No, I don't want to try an easy setup process<span class="dashicons dashicons-no-alt"></span></button>
			</div>
		</div>
		
		<!-- Step Contact section -->
		<div class="softaculous-pro-wizard-inner" data-panel="contact">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('What are the preferred methods for communication?', 'softaculous-pro'); ?></h1>
				<p><?php _e('Please provide the necessary contact information to be displayed on the website', 'softaculous-pro'); ?>
				<br />
				<?php _e('Note: This information is for displaying on your website only and is not saved on external servers', 'softaculous-pro'); ?></p>
			</div>
			
			<div class="softaculous-ai-contact">	
				<div class='softaculous-container-half'>
					<div class='softaculous-content softaculous-contact-email'>
						<label for="softaculous-site-email"><?php _e('Email:', 'softaculous-pro'); ?></label>
						<input type="email" name='site_email' id="softaculous-site-email" class="softaculous_pro_input" placeholder="<?php _e('Your email', 'softaculous-pro'); ?>" value="<?php echo esc_attr($business_email); ?>" autocomplete="off"/>
					</div>
					<div class='softaculous-content softaculous-contact-number'>
						<label for="softaculous-site-phone"><?php _e('Phone number:', 'softaculous-pro'); ?></label>
						<input type="tel" name='site_phone' id="softaculous-site-phone" class="softaculous_pro_input" placeholder="<?php _e('Your phone number', 'softaculous-pro'); ?>" value="<?php echo esc_attr($business_phone ); ?>" autocomplete="off"/>
					</div>
				</div>
				<div class='softaculous-container-full'>
					<div class='softaculous-content softaculous-contact-address'>
						<label for="softaculous-site-address"><?php _e('Address', 'softaculous-pro') ;?></label>
						<textarea rows="4" cols="30" name='site_address' id="softaculous-site-address" placeholder="<?php _e('Your full address', 'softaculous-pro'); ?>"><?php echo esc_html($business_address); ?></textarea>
					</div>
				</div>
				<div class='softaculous-pro-social-profiles'>
					<h3 class='softaculous-pro-add-social'>
						<span class="dashicons dashicons-plus"></span>
						<?php _e('More social media profiles', 'softaculous-pro')?>
					</h3>
					<div class='softaculous-pro-social-links-profile'>
						<div class='softaculous-container-half'>
							<div class='softaculous-content softaculous-social-facebook'>
								<label for="softaculous-site-social-facebook"><?php _e('Facebook', 'softaculous-pro'); ?></label>
								<input type="text" name='social_facebook' id="softaculous-site-social-facebook" class="softaculous_pro_input" placeholder="https://facebook.com/yourhandle" value="<?php echo esc_attr($social_facebook); ?>" autocomplete="off"/>
							</div>
							<div class='softaculous-content softaculous-social-twitter'>
								<label for="softaculous-site-social-twitter"><?php _e('X', 'softaculous-pro'); ?></label>
								<input type="text" name='social_twitter' id="softaculous-site-social-twitter" class="softaculous_pro_input" placeholder="https://x.com/yourhandle" value="<?php echo esc_attr($social_twitter); ?>" autocomplete="off"/>
							</div>
						</div>

						<div class='softaculous-container-half'>
							<div class='softaculous-content softaculous-social-instagram'>
								<label for="softaculous-site-social-instagram"><?php _e('Instagram', 'softaculous-pro'); ?> :</label>
								<input type="text" name='social_instagram' id="softaculous-site-social-instagram" class="softaculous_pro_input" placeholder="https://instagram.com/yourhandle" value="<?php echo esc_attr($social_instagram); ?>" autocomplete="off"/>
							</div>
							<div class='softaculous-content softaculous-social-linkedin'>
								<label for="softaculous-site-social-twitter"><?php _e('LinkedIn', 'softaculous-pro'); ?></label>
								<input type="text" name='social_linkedin' id="softaculous-site-social-linkedin" class="softaculous_pro_input" placeholder="https://linkedin.com/yourhandle" value="<?php echo esc_attr($social_linkedin); ?>" autocomplete="off"/>
							</div>
						</div>

						<div class='softaculous-container-half'>
							<div class='softaculous-content softaculous-social-youtube'>
								<label for="softaculous-site-social-youtube"><?php _e('Youtube', 'softaculous-pro'); ?></label>
								<input type="text" name='social_youtube' id="softaculous-site-social-youtube" class="softaculous_pro_input" placeholder="https://youtube.com/yourchannel" value="<?php echo esc_attr($social_youtube); ?>" autocomplete="off"/>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="softaculous-pro-wizard-buttons">
				<button onclick="softaculous_pro_prev_handler(this)" data-step="start" class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?></button>
				<button class="step_btn step_next_btn" data-step="features" onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?><span class="dashicons dashicons-arrow-right-alt next-handler-icon"></span><span class="softaculous-next-loading" style="display:none"></span></button>
			</div>
		</div>
		<!-- Step Features -->
		<div class="softaculous-pro-wizard-inner" data-panel="features">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('What are you looking to achieve with your new website ?', 'softaculous-pro'); ?></h1>
				<p><?php _e('We will install the appropriate plugins that will add the required functionality to your website', 'softaculous-pro'); ?></p>
			</div>
			<div class="softaculous-pro-features-container">
			<?php foreach(spro_get_features_list() as $slug => $feature):?>
				<label for="<?php echo $slug;?>_input" style="cursor:pointer;">
					<div class="softaculous-pro-features" data-slug="<?php echo $slug; ?>">
					<div class="softaculous-pro-features-icon">
						<span class="<?php echo $feature['icon']; ?>"></span>
					</div>
					<div class="softaculous-pro-features-text">
						<h3><?php echo $feature['name']; ?></h3>
						<p><?php echo $feature['info']; ?></p>
					</div>
					<div class="softaculous-pro-features-input">
						<input type="checkbox" onclick="softaculous_pro_selected_features(this)" id="<?php echo $slug;?>_input" <?php echo (!empty($spro_setup_info) && !empty($spro_setup_info['features']) && in_array($slug, $spro_setup_info['features']) ? 'checked="checked"' : '') ;			
						foreach($feature['plugin'] as $info){
							if (!empty($info['requires_php']) && version_compare(PHP_VERSION, $info['requires_php'], '<')) {
								echo ' disabled';
								echo ' spro-erro="'.sprintf(__('Requires PHP version %1$s or higher', 'softaculous-pro'), $info['requires_php']).'"';
								break;
							}
							echo (!empty($installed_plugins[$info['plugin_init']]) ? ' checked="checked"' : '');
							echo (empty($spro_setup_info['features']) && !empty($info['selected']) ? ' checked="checked"' : '');
						} ?>/>
					</div>
					</div>
				</label>
			<?php endforeach; ?>
			</div>
			<div class="softaculous-pro-wizard-buttons">
				<button onclick="softaculous_pro_prev_handler(this)" data-step="contact" class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?> </button>
				<button class="step_btn step_next_btn" data-step="images" onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?> <span class="dashicons dashicons-arrow-right-alt  next-handler-icon"></span><span class="softaculous-next-loading" style="display:none"></span></button>
			</div>
		</div>
		<!-- Step Image section -->
		<div class="softaculous-pro-wizard-inner" data-panel="images">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('Select Your Website Images', 'softaculous-pro'); ?></h1>
				<p><?php _e('These images will be featured throughout your website', 'softaculous-pro'); ?></p>
			</div>
			<div class="softaculous-ai-warning" style="<?php echo empty($business_descriptions) ? 'display:block' : 'display:none'; ?>"><?php _e('It looks like the description is empty. Please ensure that a description is provided before proceeding further.', 'softaculous-pro')?></div>
			<div class='softaculous-ai-images'>
				<div class="softaculous-ai-image-search">
					<div class='softaculous-ai-image-search-input'>
						<input type="text" placeholder="Search image" class='softaculous-search-images'/>
					</div>
					<div class='softaculous-ai-image-suggestion-list' style="display:none">
						<div class='softaculous-ai-image-suggestion-header'>
							<hr>
							<h3><?php _e('Suggestions', 'softaculous-pro')?></h3>
						</div>
						<div class='softaculous-ai-image-suggestions'>
							<?php if(!empty($suggested_tags)): ?>
								<?php foreach($suggested_tags as $image_suggestion):?>
									<span class='softaculous_pro_suggestion'><?php echo esc_html($image_suggestion);?></span>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div >
				<div class="softaculous-ai-images-section">
					<div class="softaculous-ai-images-suggestions">
						<div class="softaculous-pro-image-buttons">
							<button class="softaculous-suggested-images active" data-slug="suggested_image"><?php _e('Search Results', 'softaculous-pro');?></button>
							<button class="softaculous-upload-image" data-slug="upload_image"><?php _e('Upload your Images', 'softaculous-pro');?></button>
							<button class="softaculous-selected-image" data-slug="selected_image">
								<?php _e('Selected Images', 'softaculous-pro');?>
								<span class="softaculous-pro-selected-image-count"><?php echo count($selected_images); ?></span> 	
							</button>
						</div>
						<br />
						<p style="display: inline-block;background-color: #fff3cd;color: #856404;padding: 8px 16px;border-radius: 4px;border: 1px solid #ffeeba;max-width: 100%;white-space: nowrap;">
						<?php _e('Select <b>10–15</b> images from the collection below, or upload your own.');?>
						</p>
					</div>
					<div class="softaculous-ai-images-all">
						<div class='softaculous-ai-images-rows softaculous-active-section'></div>
						<div class='softaculous-upload-placeholder'>
							<div class="softaculous-pro-image-success softaculous-pro-alert softaculous-pro-alert-success" style='text-align:left; display:none;	'>
								<p style='margin: 0px; font-size:14px;'><?php _e('Image(s) selected successfully', 'softaculous-pro'); ?></p>
								<span class="softaculous-alert-dismissable dashicons dashicons-no" style='cursor: pointer;'></span>
							</div>
							<div class="softaculous-upload-box">
								<h2><?php _e('Upload File', 'softaculous-pro') ;?></h2>
								<div class="softaculous-upload-area">
									<p id="upload_area"><?php _e('<strong>Click here to upload images</strong>', 'softaculous-pro') ;?></p>
									<input type="file" id="file_input">
								</div>
							</div>
						</div>
						<div class='softaculous-selected-images-rows'>
							<p class='softaculous-placeholder' style="<?php echo empty($selected_images) ? 'display:block' : 'display:none'; ?>"><?php _e('You have not selected any image.', 'softaculous-pro')?></p>
							<?php if(!empty($selected_images)):?>
								<?php foreach ($selected_images as $image): 
									if(!empty($image['image_id']))
										$softaculous_pro_wp_img = wp_get_attachment_image_url($image['image_id'], 'full');
								?>
								<div class="softaculous-selected-image">
									<?php if (!empty($softaculous_pro_wp_img)): ?>
										<img src="<?php echo esc_attr($softaculous_pro_wp_img); ?>" data-selected-id="<?php echo esc_attr($image['image_id']); ?>" alt="<?php esc_attr_e('image', 'softaculous-pro'); ?>">
									<?php else: ?>
										<img src="<?php echo esc_url($image['image_url']); ?>" data-selected-id="<?php echo esc_attr($image['image_id']); ?>"  alt="Selected Image" loading="lazy">
									<?php endif; ?>
										<div class="softaculous-remove-selected">
										<span>&#10006;</span>
										</div>
									</div>
								<?php endforeach;?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="softaculous-pro-wizard-buttons">
				<button onclick="softaculous_pro_prev_handler(this)" data-step="features" class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?> 
				</button>
				<button class="step_btn step_next_btn" data-step="import_theme" onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?>
				<span class="dashicons dashicons-arrow-right-alt next-handler-icon"></span><span class="softaculous-next-loading" style="display:none"></span>
				</button>	
			</div>
		</div>
		<!-- Step Import theme -->
		<div class="softaculous-pro-wizard-inner" data-panel="import_theme">
			<?php
				softaculous_pro_templates();
			?>
		</div>
	</div>
</div>
