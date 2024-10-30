<?php 
if(!defined('ABSPATH')) {
	exit;
}

function bpda_bp_devolved_authority_admin() {
	
	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('bpda_bp_devolved_authority_admin') ) {
	
		$new = array();
		$sites_roles = get_editable_roles();
		$sites_roles['not-set'] = 'Not Set';
		$bp_mod_controls = array();
		$bp_xprofile_individual = '';
		if ( bp_is_active( 'groups' ) )
			$bp_mod_controls['groups']	= 'Groups';
		if ( bp_is_active( 'members' ) )
			$bp_mod_controls['members']	= 'Members';

		if ( bp_is_active( 'activity' ) )
			$bp_mod_controls['activity'] = 'Activity';
		$bp_mod_controls['emails'] = 'Emails';
		if( isset( $_POST['bpda-xprofile-individual'] )) 
			$bp_xprofile_individual = esc_attr( $_POST['bpda-xprofile-individual'] );
		
		foreach ( $bp_mod_controls as $control => $control_name ) {
			
			$new[$control]['role'] = sanitize_text_field(esc_attr( $_POST[ 'bpda-role-option-' . $control ] ));
			$new[$control]['role_select'] = sanitize_text_field(esc_attr( $_POST[ 'bpda-role-select-' . $control ] ));
			
		}
				
		update_option( 'bpda_bp_devolved_authority', $new );
		update_option( 'bpda_xprofile_individual', $bp_xprofile_individual );
		
		$updated = true;
	}
	
	$data = maybe_unserialize( get_option( 'bpda_bp_devolved_authority') );
	$xprofile_indiv_data = maybe_unserialize( get_option( 'bpda_xprofile_individual') );
	

	// Get the proper URL for submitting the settings form. (Settings API workaround) - boone
	$url_base = function_exists( 'is_network_admin' ) && is_network_admin() ? network_admin_url( 'admin.php?page=bp-devolved-authority-settings' ) : admin_url( 'admin.php?page=bp-devolved-authority-settings' );
	
	$not_set = sanitize_text_field(esc_attr__( 'Not set', 'bp-devolved-authority' ));
	
	$bp_group_options = array(
		'not-set'		=> $not_set,
		'groups'		=> sanitize_text_field(esc_attr__( 'Manage Groups', 'bp-devolved-authority' ))
	);
	$bp_members_options = array(
		'not-set'		=> $not_set,
		'members'		=> sanitize_text_field(esc_attr__( 'Manage Members', 'bp-devolved-authority' ))
	);
	$bp_activity_options = array(
		'not-set'		=> $not_set,
		'activity'		=> sanitize_text_field(esc_attr__( 'Manage Activity', 'bp-devolved-authority' ))
	);
	$bp_emails_options = array(
		'not-set'		=> $not_set,
		'emails'		=> sanitize_text_field(esc_attr__( 'Manage Emails', 'bp-devolved-authority' ))
	);
	$bp_moderate_controls = array();
	if ( bp_is_active( 'groups' ) )
		$bp_moderate_controls['groups']	= 'Groups';
	if ( bp_is_active( 'members' ) )
		$bp_moderate_controls['members'] = 'Members';
	if ( bp_is_active( 'xprofile' ) ) {
		$bp_xprofile_is_live = 'yes';
	}
	if ( bp_is_active( 'activity' ) )
		$bp_moderate_controls['activity'] = 'Activity';
	$bp_moderate_controls['emails'] = 'Emails';
	$site_roles = get_editable_roles();
	$site_roles['not-set'] = 'Not set';
	?>	
	<div class="wrap">
		<h2><?php sanitize_text_field(esc_attr_e( 'BP Devolved Authority Settings', 'bp-devolved-authority' )); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . esc_attr__( 'Settings Updated.', 'bp-devolved-authority' ) . "</p></div>"; endif; ?>

		<form action="<?php echo esc_attr($url_base) ?>" name="bp-devolved-authority-settings-form" id="bp-devolved-authority-settings-form" method="post">			

			<h2><?php sanitize_text_field(esc_attr_e( 'Role based Devolved Authority', 'bp-devolved-authority' )); ?></h2>

			<div>
				<?php foreach ( $bp_moderate_controls as $control_key => $control_description ) : ?>
				
						<h4><?php echo esc_attr($control_description) . ' ' .sanitize_text_field(esc_attr__( 'devolved authority', 'bp-devolved-authority' )); ?></h4>
						
						<select name="bpda-role-option-<?php echo esc_attr($control_key); ?>" id="">
							
							<?php foreach ( $site_roles as $role => $role_capabilities ) : ?>
								
								<?php $setting = isset( $data[$control_key]['role'] ) ? $data[$control_key]['role'] : 'not-set'; ?>  

								<?php if ( $role == 'administrator' ) continue; ?>
						
								<option name="" value="<?php echo esc_attr($role); ?>" <?php if ( $role == $setting ) echo 'selected'; ?>><?php echo esc_attr($role); ?></option>
							
							<?php endforeach; ?>
						
						</select>
						
						<label><?php sanitize_text_field(esc_attr_e( ' can ', 'bp-devolved-authority' )); ?></label>

						<select name="bpda-role-select-<?php echo esc_attr($control_key); ?>" id="">
							
						
							<?php if ( $control_key == 'groups' ) : ?>
							
								<?php foreach ( $bp_group_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo esc_attr($option); ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo esc_attr($option_description); ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'members' ) : ?>
							
								<?php foreach ( $bp_members_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo esc_attr($option); ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo esc_attr($option_description); ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'activity' ) : ?>
							
								<?php foreach ( $bp_activity_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo esc_attr($option); ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo esc_attr($option_description); ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'emails' ) : ?>
							
								<?php foreach ( $bp_emails_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo esc_attr($option); ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo esc_attr($option_description); ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
						</select>

				<?php endforeach; ?>
				
			</div>
			
			<?php if ( $bp_xprofile_is_live == 'yes' ) : ?>
			<div>
				</br><label for="bpda-xprofile-individual"><?php sanitize_text_field(esc_attr_e( 'Enable individual setting of xprofile management', 'dp-devolved-authority')) ?></label></br>
				<input type="checkbox" id="bpda-xprofile-individual" name="bpda-xprofile-individual" <?php checked( 'on', $xprofile_indiv_data ) ?>><?php sanitize_text_field(esc_attr_e( 'Enable Xprofile DA individually', 'bp-devolved-authority')) ?></input></br>
			</div>
			<?php endif; ?>
			<?php wp_nonce_field( 'bpda_bp_devolved_authority_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
		
	</div>
<?php
}

?>
