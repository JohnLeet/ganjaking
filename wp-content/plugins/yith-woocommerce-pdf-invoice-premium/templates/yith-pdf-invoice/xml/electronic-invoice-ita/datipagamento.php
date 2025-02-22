<?php
/**
 * PDF Invoice Template
 *
 * Dati Pagamento XML.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\PDFInvoice\Templates
 */

/**
 * APPLY_FILTERS: ywpi_electronic_invoice_field_value
 *
 * Filter the electronic invoice fields value.
 *
 * @param string the value.
 * @param string field ID.
 * @param object $document the document object.
 *
 * @return string
 */
?>
<DatiPagamento>
	<CondizioniPagamento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $invoice_details['payment_info']['conditions'], 'CondizioniPagamento', $document ) ); ?></CondizioniPagamento>
	<DettaglioPagamento>
		<ModalitaPagamento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $invoice_details['payment_info']['mode'], 'ModalitaPagamento', $document ) ); ?></ModalitaPagamento>
		<ImportoPagamento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $invoice_details['payment_info']['total_amount'], 'ImportoPagamento', $document ) ); ?></ImportoPagamento>
	</DettaglioPagamento>
</DatiPagamento>
