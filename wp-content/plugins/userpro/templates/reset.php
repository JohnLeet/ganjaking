<div class="userpro userpro-<?php echo $i; ?> userpro-<?php echo $layout; ?>" <?php userpro_args_to_data( $args ); ?>>

	<!-- <a href="#" class="userpro-close-popup"><?php _e('Close','userpro'); ?></a> -->
	
	<div class="userpro-head">
		<div class="userpro-left"><?php echo $args["{$template}_heading"]; ?></div>
		<?php if ($args["{$template}_side"]) { ?>
		<div class="userpro-right"><a href="#" data-template="<?php echo $args["{$template}_side_action"]; ?>"><?php echo $args["{$template}_side"]; ?></a></div>
		<?php } ?>
		<div class="userpro-clear"></div>
	</div>
	
	<div class="userpro-body">
	
		<?php do_action('userpro_pre_form_message'); ?>

		<form action="" method="post" data-action="<?php echo $template; ?>">
            <input type="hidden" name="user_pro_nonce" value="<?php echo wp_create_nonce('user_pro_nonce'); ?>">
			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			//do_action('userpro_before_fields', $hook_args);
			?>
		
			<!-- fields -->
			<div class='userpro-field' data-key='username_or_email'>
				<div class='userpro-label <?php if ($args['field_icons'] == 1) { echo 'iconed'; } ?>'><label for='username_or_email-<?php echo $i; ?>'><?php _e('Username or Email','userpro'); ?></label></div>
				<div class='userpro-input'>
					<input type='text' name='username_or_email-<?php echo $i; ?>' id='username_or_email-<?php echo $i; ?>' />
					<div class='userpro-clear'></div>
				</div>
			</div><div class='userpro-clear'></div>
			
			<?php  $key = 'antispam'; $array = $userpro->fields[$key];
				if (isset($array) && is_array($array)) echo userpro_edit_field( $key, $array, $i, $args ); ?>
			
			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			do_action('userpro_after_fields', $hook_args);
			?>
						
			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			do_action('userpro_before_form_submit', $hook_args);
			if( userpro_get_option('enable_reset_by_mail') == 'y' ){
				$args["{$template}_button_primary"] = __('Request reset link', 'userpro');
			}
			?>
			
			<?php if ($args["{$template}_button_primary"] ||  $args["{$template}_button_secondary"] ) { ?>
			<div class="userpro-field userpro-submit userpro-column">
				
				<?php if ($args["{$template}_button_primary"]) { ?>
				<input type="submit" value="<?php echo $args["{$template}_button_primary"]; ?>" class="userpro-button" />
				<?php } ?>
				
				<?php if ($args["{$template}_button_secondary"] && userpro_get_option('enable_reset_by_mail') == 'n' ) { ?>
				<input type="button" value="<?php echo $args["{$template}_button_secondary"]; ?>" class="userpro-button secondary" data-template="<?php echo $args["{$template}_button_action"]; ?>" />
				<?php } ?>

				<img src="<?php echo $userpro->skin_url(); ?>loading.gif" alt="" class="userpro-loading" />
				<div class="userpro-clear"></div>
				
			</div>
			<?php } ?>
		
		</form>
	
	</div>

</div>
