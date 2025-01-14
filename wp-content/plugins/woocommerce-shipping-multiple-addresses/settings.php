<?php
global $wpdb;

$duplication    = get_option( '_wcms_cart_duplication', false );
$settings       = $this->settings;
$categories     = get_terms( 'product_cat', array( 'order_by' => 'name', 'order' => 'ASC' ) );
$ship           = WC()->shipping;

$shipping_methods   = WC()->shipping->load_shipping_methods(false);
$ship_methods_array = array();
$categories_array   = array();

foreach ($shipping_methods as $id => $object) {
    if ($object->enabled == 'yes' && $id != 'multiple_shipping') {
        $ship_methods_array[$id] = $object->method_title;
    }
}

foreach ($categories as $category) {
    $categories_array[$category->term_id] = $category->name;
}

$ship_methods_json = wp_json_encode( $ship_methods_array );
$categories_json   = wp_json_encode( $categories_array );
?>
<style type="text/css">
span.button-icon {font-size: 16px;}
td.remove {vertical-align: middle;}
</style>
<div class="wrap">
    <div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br></div>
    <h2><?php esc_html_e('Multiple Shipping Settings', 'wc_shipping_multiple_address'); ?></h2>

    <?php
    // phpcs:disable WordPress.Security.NonceVerification.Recommended --- Nonce verification is escaped here as $_GET variables are not stored or acted upon.
    if ( isset( $_GET['saved'] ) ):
    ?>
    <div class="message updated"><p><?php esc_html_e('Your settings have been saved', 'wc_shipping_multiple_address'); ?></p></div>
    <?php endif; ?>

    <p><?php esc_html_e('Below you can set the shipping methods available for a specific product or product category. This functionality will allow you to define the shipping methods that are available to be used by your customers when purchasing products from your store. Pricing is managed by your existing shipping methods, or by the rates that you set in your store\'s settings. This just enables/disables specific methods for specific products.', 'wc_shipping_multiple_address'); ?></p>

    <form action="admin-post.php" method="post">

        <table class="wp-list-table widefat fixed posts" cellspacing="0">
            <thead>
                <tr>
                    <!--<th scope="col" class="" width="100"><?php esc_html_e( 'Origin Zip Code', 'wc_shipping_multiple_address'); ?></th>-->
                    <th scope="col" class="" width="250"><?php esc_html_e( 'Shipping Method', 'wc_shipping_multiple_address'); ?></th>
                    <th scope="col" class=""><?php esc_html_e( 'Products', 'wc_shipping_multiple_address'); ?></th>
                    <th scope="col" class=""><?php esc_html_e( 'Categories', 'wc_shipping_multiple_address'); ?></th>
                    <th scope="col" class="" width="100" align="center">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                $x = 1;
                foreach ((array)$settings as $setting):
                ?>
                <tr>
                    <td>
                        <select name="shipping_methods[<?php echo esc_attr( $x ); ?>][]" style="width: 200px;" class="chzn-select" multiple>
                            <?php
                            foreach ($ship_methods_array as $value => $label):
                                $selected = (in_array($value, $setting['method'])) ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="products[<?php echo esc_attr( $x ); ?>][]" class="ajax_chosen_select_products_and_variations" style="width: 300px;" multiple>
                            <?php foreach ($setting['products'] as $pid): ?>
                            <option value="<?php echo esc_attr( $pid ); ?>" selected><?php echo esc_html( get_the_title( $pid ) ); ?> &ndash; #<?php echo esc_html( $pid ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="categories[<?php echo esc_attr( $x ); ?>][]" class="chzn-select" style="width: 300px;" multiple>
                            <?php
                            foreach ($categories as $category):
                                $selected = (in_array($category->term_id, $setting['categories'])) ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $category->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td></td>
                </tr>
                <?php
                    $x++;
                endforeach;
                ?>
            </tbody>
        </table>
        <button type="button" class="button-secondary add-row"><span class="button-icon">+</span> <?php esc_html_e( 'Add Another', 'wc_shipping_multiple_address'); ?></button>

        <h3><?php esc_html_e( 'Cart Duplication', 'wc_shipping_multiple_address' ); ?></h3>
			<p><?php esc_html_e( 'This functionality will allow your customers to duplicate the contents of their cart in order to be able to ship the same cart to multiple addresses in addition to individual products.', 'wc_shipping_multiple_address' ); ?></p>

        <p>
            <label>
                <input type="checkbox" name="cart_duplication" value="1" <?php echo ($duplication) ? 'checked' : ''; ?>/>
                <?php esc_html_e( 'Enable cart duplication', 'wc_shipping_multiple_address' ); ?>
            </label>
        </p>

        <h3><?php esc_html_e( 'Text your shoppers see when Multiple Shipping is enabled at checkout', 'wc_shipping_multiple_address' ); ?></h3>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th><label for="lang_notification"><?php esc_html_e( 'Checkout Notification', 'wc_shipping_multiple_address' ); ?></label></th>
                    <td><input type="text" name="lang[notification]" id="lang_notification" value="<?php echo esc_attr(WC_Ship_Multiple::$lang['notification']); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th><label for="lang_btn_items"><?php esc_html_e( 'Button: Item Addresses', 'wc_shipping_multiple_address' ); ?></label></th>
                    <td><input type="text" name="lang[btn_items]" id="lang_btn_items" value="<?php echo esc_attr(WC_Ship_Multiple::$lang['btn_items']); ?>" size="50" /></td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="hidden" name="action" value="wcms_update" />
            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wc_shipping_multiple_address' ); ?>" />
        </p>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        jQuery("select.ajax_chosen_select_products_and_variations").ajaxChosen({
            method:     'GET',
            url:        ajaxurl,
            dataType:   'json',
            afterTypeDelay: 100,
            data:       {
                action:         'woocommerce_json_search_products_and_variations',
                security:       '<?php echo esc_js( wp_create_nonce("search-products") ); ?>'
            }
        }, function (data) {
            var terms = {};

            jQuery.each(data, function (i, val) {
                terms[i] = val;
            });

            return terms;
        });
        jQuery("select.chzn-select").chosen();

        var methods_json    = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $ship_methods_json ) ?>' ) );
        var methods_options = '';

        for (method in methods_json) {
            methods_options += '<option value="'+ method +'">'+ methods_json[method] +'</option>';
        }

        var categories_json    = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $categories_json ) ?>' ) );
        var categories_options = '';

        for (id in categories_json) {
            categories_options += '<option value="'+ id +'">'+ categories_json[id] +'</option>';
        }

        $(".add-row").click(function() {
            // build the row template
            var rand = Math.floor(Math.random()*99999999);
            var tmpl = '\
            <tr id="'+ rand +'">\
                <td>\
                    <select name="shipping_methods['+ rand +'][]" class="chzn-select" style="width: 200px;" multiple>'+ methods_options +'</select>\
                </td>\
                <td>\
                    <select name="products['+ rand +'][]" class="ajax_chosen_select_products_and_variations" style="width: 300px;" multiple>\
                    </select>\
                </td>\
                <td>\
                    <select name="categories['+ rand +'][]" class="chzn-select" style="width: 300px;" multiple>'+ categories_options +'</select>\
                </td>\
                <td class="remove"><a class="button remove" href="#"><?php echo esc_html__( 'Remove', 'wc_shipping_multiple_address' ); ?></a></td>\
            </tr>\
            ';
            $("#tbody").append(tmpl);

            jQuery("select.chzn-select").chosen();

            jQuery(".ajax_chosen_select_products_and_variations").ajaxChosen({
                method:     'GET',
                url:        ajaxurl,
                dataType:   'json',
                afterTypeDelay: 100,
                data:       {
                    action:         'woocommerce_json_search_products_and_variations',
                    security:       '<?php echo esc_js( wp_create_nonce("search-products") ); ?>'
                }
            }, function (data) {
                var terms = {};

                jQuery.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            });
        });

        $("a.remove").on("click", function() {
            $(this).parents("tr").remove();
        });
    });
</script>
