

	<!-- MEMBERS SECTION -->	
	<section id="members">
		<div class="separator"></div>
                    <div class="col-md-8 col-md-offset-2">
                        <!-- Section Description -->
                        <?php 
                        global $userpro;
                        if ($args['search']){ ?>
                            <div class="userpro-search">
                                <form class="userpro-search-form" action="" method="get">

                                    <?php if ($args['memberlist_default_search']) { ?><input type="text" name="searchuser" id="searchuser" value="<?php if(isset($args['GET']['searchuser'])) echo $args['GET']['searchuser']; ?>" placeholder="<?php _e('Search for a user...','userpro'); ?>" /><?php } ?>

                                    <?php do_action('userpro_modify_search_filters', $args); ?>

                                    <input type="hidden" name="page_id" value="<?php echo get_the_ID();?>">

                                    <button type="submit" class="userpro-icon-search userpro-tip" title="<?php _e('Search','userpro'); ?>"></button>

                                    <button type="button" class="userpro-icon-remove userpro-clear-search userpro-tip" title="<?php _e('Clear your Search','userpro'); ?>"></button>

                                </form>
                            </div>
                            <?php
                            if (isset($args['users']['total']) && !empty($args['users']['total']) && $userpro->memberlist_in_search_mode($args) ){
                                echo '<div class="userpro-search-results">'.$userpro->found_members( $args['users']['total'] ).'</div>';
                            }
                            ?>
                            <?php } if(userpro_get_option('alphabetical_pagination') == 1 ){?>
                            <div class="alphabetical-pagination">
                            <?php
                                $alphabets = range('A','Z');
                                foreach ($alphabets as $k => $v){?>
                                        <span class="alpha-pagination-list <?php if(isset($args['GET']['userpa']) && $args['GET']['userpa'] == $v ) {echo 'current-alphabet';}?>"><a class="alpha-pagination-link" href="?userpa=<?php echo $v;?>"><?php echo $v;?></a></span>
                                <?php }?>		
                            </div>

                        <?php }?>
                    </div>
			
			<!-- First Row of Members -->
                    <div class="row1">
                        <?php if ( $userpro->memberlist_in_search_mode($args) ) { ?>

                        <?php if ( $args['memberlist_paginate'] == 1 && $args['memberlist_paginate_top'] == 1 && isset($args['users']['paginate'])) { ?><div class="userpro-paginate top"><?php echo $args['users']['paginate']; ?></div><?php } ?>

                        <?php if (isset($args['users']['users']) && !empty($args['users']['users'])){ ?>
                        <?php foreach($args['users']['users'] as $user) : $user_id = $user->ID; ?>
                            <!-- Member 1 -->
                            <div class="col-md-4">
                                <div class="member-profile">
                                    <div class="member-img">
                                    <?php if ( userpro_get_option('lightbox') && userpro_get_option('profile_lightbox') ) { ?>
                                        <a href="<?php echo $userpro->profile_photo_url($user_id); ?>" class="lightview" data-lightview-caption="<?php echo $userpro->profile_photo_title( $user_id ); ?>" title="<?php echo userpro_profile_data('display_name', $user_id); ?>"><?php echo get_avatar( $user_id, $args['memberlist_v2_pic_size'] ); ?></a>
                                    <?php } else { ?>
                                        <a href="<?php echo $userpro->permalink($user_id); ?>"><?php echo get_avatar( $user_id,$args['memberlist_v2_pic_size'] ); ?></a>
                                    <?php } ?>
                                    </div>
                                    <div class="member-details">
                                        <h3><a href="<?php echo $userpro->permalink($user_id); ?>" class="<?php userpro_user_via_popup($args); ?> userpro-transition" data-up_username="<?php echo $userpro->id_to_member($user_id); ?>"><?php echo userpro_profile_data('display_name', $user_id); ?></a></h3>
                                        <?php do_action('userpro_after_profile_img' , $user_id); ?>
                                        <ul class="social_links">
                                            <?php 
                                            $default_social_fields = array('facebook', 'twitter', 'google_plus', 'user_url');
                                            foreach( userpro_fields_group_by_template( 'social', $args["social_group"] ) as $key  => $array ) {
                                                if(in_array($key,$default_social_fields)){ 
                                                    if(!empty(userpro_profile_data($key,$user_id))){  $key = str_ireplace('_', '-', $key);?>
                                                        <li><a href="<?php echo userpro_link_filter(userpro_profile_data($key,$user_id), $key);?>"><i class="fa fa-<?php echo $key ?>"></i></a></li>
                                                    <?php }
                                                }
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php } else { ?>
                                <div class="userpro-search-noresults"><?php _e('No users match your search. Please try again.','userpro'); ?></div>
                            <?php } ?>

                            <?php if ($args['memberlist_paginate'] == 1 && $args['memberlist_paginate_bottom'] == 1 && isset($args['users']['paginate'])) { ?><div class="userpro-paginate bottom"><?php echo $args['users']['paginate']; ?></div><?php } ?>

                            <?php } // initial results off/on ?>

                    </div>
                    <div class="clear"></div>

	</section>
	<!-- //MEMBERS SECTION -->	

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> <!-- Load jQuery -->

	<!-- jQuery Code for the View All Button -->
	<script>
		$(document).ready(function(){
		  $(".fadeIn").click(function(){
			$("div.row2").fadeIn();
			$("button.fadeIn").hide();
		  });
		});
	</script>
	
	