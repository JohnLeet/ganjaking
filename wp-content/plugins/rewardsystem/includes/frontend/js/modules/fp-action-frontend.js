/*
 * Action - Module
 */
jQuery( function ( $ ) {
   'use strict' ;
    var RSActionFrontend = {
        init : function () {
            if ( fp_action_frontend_params.user_id !== '0' ) {
                $( '.rsgatewaypointsmsg' ).hide() ;
                $( '#order_review' ).on( 'click' , '.payment_methods input.input-radio' , this.msg_for_rewardgateway ) ;
                
                $('.rs-coupon-reward-message').show();
                $(document).on('applied_coupon',this.woocommerce_applied_coupon);
            }
        } ,
        woocommerce_applied_coupon:function(){
          $('.rs-coupon-reward-message').hide();
        },
        msg_for_rewardgateway : function () {

            var gatewayid = $( this ).val() ;
            var gatewaytitle = $( '.payment_method_' + gatewayid ).find( 'label' ).html() ;
            var data = ( {
                action : 'rewardgatewaymsg' ,
                gatewayid : gatewayid ,
                gatewaytitle : gatewaytitle ,
                sumo_security : fp_action_frontend_params.fp_gateway_msg
            } ) ;
            $.post( fp_action_frontend_params.ajaxurl , data , function ( response ) {
                if ( true === response.success ) {
                    $( '.rsgatewaypointsmsg' ).css( 'display' , 'none' ) ;
                    $( '.rspgpoints' ).css( 'display' , 'none' ) ;

                    if ( response.data.restrictedmsg !== '' ) {
                        $( '.rsgatewaypointsmsg' ).css( 'display' , 'inline-block' ) ;
                        $( '.rsgatewaypointsmsg' ).html( response.data.restrictedmsg ) ;
                    }

                    if ( response.data.rewardpoints != '' ) {
                        $( '.rspgpoints' ).css( 'display' , 'inline-block' ) ;
                        $( '.rspgpoints' ).html( response.data.earn_gateway_message ) ;
                    }
                } else {
                    window.alert( response.data.error ) ;
                }
            } ) ;
        } ,
    } ;
    RSActionFrontend.init() ;
} ) ;