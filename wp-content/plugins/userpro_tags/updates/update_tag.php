<?php

	/* Add the update info on init */
	add_action('init', 'userpro_update_01', 21);
	function userpro_update_01(){
	global $userpro;
		if (!userpro_update_installed('01') && get_option('userpro_fields') && get_option('userpro_fields_builtin') ) {
							
			global $userpro;
			
			$fields = get_option('userpro_fields');
			$builtin = get_option('userpro_fields_builtin');
			
			$new_fields['tags'] = array(
			'_builtin' => 1,
			'type' => 'multiselect',
			'label' => 'Tags',
			
		);

				
			
			$all_fields = $new_fields+$fields;
			$all_builtin = $new_fields+$builtin;
			
			update_option('userpro_fields', $all_fields);
			update_option('userpro_fields_builtin', $all_builtin);
			update_option("userpro_update_01", 1);
			
	
		}
	
	}
