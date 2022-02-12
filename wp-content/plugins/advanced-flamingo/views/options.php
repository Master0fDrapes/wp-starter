<?php
/*
*
* View Name: Advanced Flamingo options view
*
*/

if (!defined('ABSPATH')) exit;

global $ufa_options; 
extract($ufa_options); ?>

	<div class="wrap ufa-wrap">
	<?php do_action('ufa_option_screen_header'); ?>
	
		<div class="ufa-container">
			<div class="col-md-12">
				<img class="ufa-logo" src="<?php echo plugins_url($this->domain.'/assets/images/logo.png'); ?>" width="200" alt="Advanced Flamingo" />
			</div>
			<div class="col-md-12 ufa-nav">
				<ul class="nav">
					<li class="nav-item active">
						<a href="javascript:;"><?php _e('General Settings', 'advanced-flamingo'); ?></a>
					</li>
					<li class="nav-item nav-item-support">
						<a href="mailto:support@bluecoral.vn"><i class="dashicons dashicons-sos"></i> <span class="label"><?php _e('Need Support ?', 'advanced-flamingo'); ?></span></a>
					</li>
					<li class="nav-item nav-item-beer">
						<a href="https://go.bluecoral.vn/buymeabeer" target="_blank"><i class="dashicons dashicons-beer"></i> <span class="label"><?php _e('Buy me a beer!', 'advanced-flamingo'); ?></span></a>
					</li>
				</ul>
			</div>
			<div class="col-md-12">
				<form method="POST" autocompleted="off" class="ufa-form">
					<div class="form-header">
						<div class="row">
							<div class="col-md-12 col-border-bottom">
								<h2><?php _e('General Settings', 'advanced-flamingo'); ?></h2>
							</div>
						</div>
					</div>
					
				<?php do_action('ufa_option_form_header'); ?>
					
					<div class="form-content">
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php _e('Addresss Book', 'advanced-flamingo'); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e('Addresss Book', 'advanced-flamingo'); ?></span></legend>
										<label for="address_book">
											<input name="address_book" type="hidden" value="1" />
											<input id="address_book" name="address_book" type="checkbox" class="address_book" value="0" <?php echo ($address_book === 0) ? 'checked' : ''; ?> />
											<?php _e('Disable', 'advanced-flamingo'); ?>
										</label>
									</fieldset>
									
									<div class="address_book-position <?php echo ($address_book === 0) ? 'hidden' : ''; ?>">
										<p><?php _e('First position of submenu', 'advanced-flamingo'); ?></p>
										<select name="menu_pos" class="form-control">
											<option value="0" <?php echo ($menu_pos === 0) ? 'selected' : ''; ?>><?php _e('Address Book', 'flamingo'); ?></option>
											<option value="1" <?php echo ($menu_pos === 1) ? 'selected' : ''; ?>><?php _e('Inbound Messages', 'flamingo'); ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php _e('Inbound Messages', 'advanced-flamingo'); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<label for="inbound_data">
										<input name="inbound_data" type="hidden" value="0" />
										<input id="inbound_data" name="inbound_data" type="checkbox" class="inbound_data" value="1" <?php echo ($inbound_data === 1) ? 'checked' : ''; ?> />
										<?php _e('Show the field form in', 'advanced-flamingo'); ?> <a href="<?php echo admin_url('admin.php?page=flamingo_inbound'); ?>"><?php _e('Inbound Messages', 'advanced-flamingo'); ?></a>.
									</label>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php _e('Flamingo Menu', 'advanced-flamingo'); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<input name="menu_label" type="text" id="menu_label" aria-describedby="menu_label-description" placeholder="Ex: Contact Leads" class="regular-text code form-control" style="max-width: 25rem;" value="<?php echo esc_attr($menu_label); ?>" />
									<p id="menu_label-description" class="description"><?php _e('Enter the text here if you want to change menu label of Flamingo plugin.', 'advanced-flamingo'); ?></p>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php _e('Additional Settings', 'advanced-flamingo'); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<ul style="list-style: disc;">
										<li><?php _e('Use <strong>flamingo_fields_enabled</strong> to show only specified fields in <u>Inbound Messages</u>.', 'advanced-flamingo'); ?>
											<p><?php _e('Example', 'advanced-flamingo'); ?>:
											<code>flamingo_fields_enabled: your-name, your-email, your-message</code>
											<code>flamingo_fields_enabled: [your-name], [your-email], [your-message]</code>
											</p>
										</li>
										<li><?php _e('Use <strong>flamingo_fields_disabled</strong> to hide specified fields in <u>Inbound Messages</u>.', 'advanced-flamingo'); ?>
											<p><?php _e('Example', 'advanced-flamingo'); ?>:
											<code>flamingo_fields_disabled: your-subject, your-message</code>
											<code>flamingo_fields_disabled: [your-subject], [your-message]</code>
											</p>
										</li>
									</ul>
									<p class="description"><?php _e('How to use Additional Settings', 'advanced-flamingo'); ?>: <a href="https://go.bluecoral.vn/additional-settings" target="_blank">https://go.bluecoral.vn/additional-settings</a></p>
								</div>
							</div>
						</div>
						
					<?php do_action('ufa_option_form_items'); ?>	
					</div>
					
				<?php do_action('ufa_option_form_end'); ?>
					
					<div class="form-footer">
						<button type="submit" name="submit_ufa" id="submit" class="btn btn-primary" value="Save Changes"><?php _e('Save Changes'); ?></button>
					</div>
					
				<?php do_action('ufa_option_form_footer'); ?>
				</form>
			</div>
		</div>
	
	<?php do_action('ufa_option_screen_footer'); ?>
	</div>