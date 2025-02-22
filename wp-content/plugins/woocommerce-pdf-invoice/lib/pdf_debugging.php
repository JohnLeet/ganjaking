<?php 

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
  
// include DomPDF autoloader
require_once ( WP_PLUGIN_DIR . "/woocommerce-pdf-invoice/lib/dompdf/autoload.inc.php" );

// reference the Dompdf namespace
use Dompdf\Dompdf;

$current_user = wp_get_current_user();

$server_configs = array(
  "PHP Version" => array(
    "required" => "7.1",
    "value"    => phpversion(),
    "result"   => version_compare(phpversion(), "7.1"),
  ),
  "DOMDocument extension" => array(
    "required" => true,
    "value"    => phpversion("DOM"),
    "result"   => class_exists("DOMDocument"),
  ),
  "PCRE" => array(
    "required" => true,
    "value"    => phpversion("pcre"),
    "result"   => function_exists("preg_match") && @preg_match("/./u", "a"),
    "failure"  => "PCRE is required with Unicode support (the \"u\" modifier)",
  ),
  "Zlib" => array(
    "required" => true,
    "value"    => phpversion("zlib"),
    "result"   => function_exists("gzcompress"),
    "fallback" => "Recommended to compress PDF documents",
  ),
  "ZipArchive" => array(
    "required" => true,
    "value"    => phpversion("zip"),
    "result"   => class_exists("ZipArchive"),
    "fallback" => "Required for bulk exporting",
  ),
  "Iconv extension" => array(
    "required" => true,
    "value"    => ICONV_VERSION,
    "result"   => extension_loaded('iconv'),
    "failure"  => "ICONV is required, please contact your host to have it installed.",
  ),
  "Multibyte Support" => array(
    "required" => true,
    "value"    => extension_loaded('mbstring'),
    "result"   => extension_loaded('mbstring'),
    "failure"  => "Multibyte Support is required, please contact your host to have it installed.",
  ),
  "GD" => array(
    "required" => true,
    "value"    => phpversion("gd"),
    "result"   => function_exists("imagecreate"),
    "fallback" => "Required if you have images in your documents",
  ),
  "APC" => array(
    "required" => "For better performances",
    "value"    => phpversion("apc"),
    "result"   => function_exists("apc_fetch"),
    "fallback" => "Recommended for better performances",
  ),
  "GMagick or IMagick" => array(
    "required" => "Better with transparent PNG images",
    "value"    => null,
    "result"   => extension_loaded("gmagick") || extension_loaded("imagick"),
    "fallback" => "Recommended for better performances",
  ),
);

if (($gm = extension_loaded("gmagick")) || ($im = extension_loaded("imagick"))) {
  $server_configs["GMagick or IMagick"]["value"] = ($im ? "IMagick ".phpversion("imagick") : "GMagick ".phpversion("gmagick"));
}

?>

<table class="pdfsetup form-table">
  <tr class="pdfheaderrow">
    <th></th>
    <th>Required</th>
    <th>Present</th>
  </tr>
  
  <?php 
  $row 		= 'even';
  $rowcount = 0;
  foreach( $server_configs as $label => $server_config ) { 
  
  	$rowcount++;
	$row = ($rowcount % 2 == 0 ? 'even' : 'odd');
  ?>
    <tr class="pdf-<?php echo $row; ?>">
      <th class="title"><?php echo $label; ?></th>
      <td><?php echo ($server_config["required"] === true ? "Yes" : $server_config["required"]); ?></td>
      <td class="<?php echo ($server_config["result"] ? "ok" : (isset($server_config["fallback"]) ? "warning" : "failed")); ?>">
        <?php
        echo $server_config["value"];
        if ($server_config["result"] && !$server_config["value"]) echo "Yes";
        if (!$server_config["result"]) {
          if (isset($server_config["fallback"])) {
            echo "<div>No. ".$server_config["fallback"]."</div>";
          }
          if (isset($server_config["failure"])) {
            echo "<div>".$server_config["failure"]."</div>";
          }
        }
        ?>
      </td>
    </tr>
  <?php } ?>
  
</table>

<h3 class="dompdf-config"><?php _e("Send test email with PDF attachment" , 'woocommerce-pdf-invoice' ); ?></h3>
                    
<form method="post" action="" >
<table class="dompgf-debugging-table">
	<tr>
    	<th colspan="2"><?php _e("Enter email address" , 'woocommerce-pdf-invoice' ); ?></th>
  </tr>
  <tr>
      <td><input type="email" name="pdfemailtest-emailaddress" placeholder="Email Address"/></td>
      <td>
          <?php wp_nonce_field('pdf_test_nonce_action','pdf_test_nonce'); ?>
          <input type="hidden" name="pdfemailtest" value="1" />
          <input type="submit" class="dompgf-debugging-submit" value="<?php _e("Send test email with PDF Attachment" , 'woocommerce-pdf-invoice' ); ?>" />
      </td>
	</tr>
</table>
</form>

<?php if( in_array('administrator', $current_user->roles) ) { ?>
<h3 class="dompdf-config"><?php _e("Create Invoices For Past Orders" , 'woocommerce-pdf-invoice' ); ?></h3>
<p><?php _e("This option will create invoices for any orders that are complete and don't have an invoice number.<br />The process runs in the background in batches to avoid any server timeouts." , 'woocommerce-pdf-invoice' ); ?></p>
<form method="post" action="" name="pdf_past_orders">
<table class="dompgf-debugging-table">
  <tr>
      <th colspan="2"><?php _e("Type 'confirm' to create invoices for past orders." , 'woocommerce-pdf-invoice' ); ?></th>
  </tr>
  <tr>
      <td><input type="text" name="pdf_past_orders-confirmation" placeholder="Type 'confirm'"/></td>
      <td>
          <?php wp_nonce_field('pdf_past_orders_nonce_action','pdf_past_orders_nonce'); ?>
          <input type="hidden" name="pdf_past_orders" value="1" />
          <input type="submit" class="dompgf-debugging-submit" value="<?php _e("Create Invoices For Past Orders" , 'woocommerce-pdf-invoice' ); ?>" />
      </td>
  </tr>
</table>
</form>
<?php } ?>

<?php if( in_array('administrator', $current_user->roles) ) { ?>
<h3 class="dompdf-config"><?php _e("Create And Email Invoices For Past Orders" , 'woocommerce-pdf-invoice' ); ?></h3>
<p><?php _e("This option will create invoices for any orders that are complete and don't have an invoice number.<br />The invoice will be emailed to the customer. The process runs in the background in batches to avoid any server timeouts.<br /><strong>Warning : sending large numbers of emails in short periods can cause deliverability issues.</strong>" , 'woocommerce-pdf-invoice' ); ?></p>
<form method="post" action="" name="pdf_past_orders_email">
<table class="dompgf-debugging-table">
  <tr>
      <th colspan="2"><?php _e("Type 'confirm' to create invoices for past orders." , 'woocommerce-pdf-invoice' ); ?></th>
  </tr>
  <tr>
      <td><input type="text" name="pdf_past_orders_email-confirmation" placeholder="Type 'confirm'"/></td>
      <td>
          <?php wp_nonce_field('pdf_past_orders_email_nonce_action','pdf_past_orders_email_nonce'); ?>
          <input type="hidden" name="pdf_past_orders_email" value="1" />
          <input type="submit" class="dompgf-debugging-submit" value="<?php _e("Create And Email Invoices For Past Orders" , 'woocommerce-pdf-invoice' ); ?>" />
      </td>
  </tr>
</table>
</form>
<?php } ?>

<?php if( in_array('administrator', $current_user->roles) ) { ?>
<h3 class="dompdf-config"><?php _e("Delete Invoice Information" , 'woocommerce-pdf-invoice' ); ?></h3>
<p><?php _e("This is an unrecoverable option, use with caution." , 'woocommerce-pdf-invoice' ); ?></p>
<p><?php _e('You can delete the invoice information store in each order.<br /><strong>The information can only be recovered using a backup of your database. USE WITH CAUTION!</strong>' , 'woocommerce-pdf-invoice' ); ?></p>
<form method="post" action="" name="pdfdelete">
<table class="dompgf-debugging-table">
  <tr>
      <th colspan="2"><?php _e("Type 'confirm' to confirm you understand that this will delete all of the invoice information stored in each order." , 'woocommerce-pdf-invoice' ); ?></th>
  </tr>
  <tr>
      <td><input type="text" name="pdfdelete-confirmation" placeholder="Type 'confirm'"/></td>
      <td>
          <?php wp_nonce_field('pdf_delete_nonce_action','pdf_delete_nonce'); ?>
          <input type="hidden" name="pdfdelete" value="1" />
          <input type="submit" class="dompgf-debugging-submit" value="<?php _e("Delete invoice information from orders and reset invoice numbers" , 'woocommerce-pdf-invoice' ); ?>" />
      </td>
  </tr>
</table>
</form>
<?php } ?>

<?php if( in_array('administrator', $current_user->roles) ) { ?>
<h3 class="dompdf-config"><?php _e("Fix Invoice dates" , 'woocommerce-pdf-invoice' ); ?></h3>
<p><?php _e("<strong>This is an unrecoverable option, use with caution.</strong> The process runs in the background in batches to avoid any server timeouts." , 'woocommerce-pdf-invoice' ); ?></p>
<p><?php _e('You can update the invoice date and date format using this option.<br /><strong>This change can only be undone using a backup of your database. USE WITH CAUTION!</strong>' , 'woocommerce-pdf-invoice' ); ?></p>
<form method="post" action="" name="pdffixdates">
<table class="dompgf-debugging-table">
  <tr>
      <th colspan="2"><?php _e("Type 'confirm' to confirm you understand that this will change correct the date and date format for ALL invoices, based on the current PDF Invoice date settings." , 'woocommerce-pdf-invoice' ); ?></th>
  </tr>
  <tr>
      <td><input type="text" name="pdffix-dates-confirmation" placeholder="Type 'confirm'"/></td>
      <td>
          <?php wp_nonce_field('pdf_fix_dates_nonce_action','pdf_fix_dates_nonce'); ?>
          <input type="hidden" name="pdffixdates" value="1" />
          <input type="submit" class="dompgf-debugging-submit" value="<?php _e("Fix Invoice Dates" , 'woocommerce-pdf-invoice' ); ?>" />
      </td>
  </tr>
</table>
</form>
<?php } ?>
