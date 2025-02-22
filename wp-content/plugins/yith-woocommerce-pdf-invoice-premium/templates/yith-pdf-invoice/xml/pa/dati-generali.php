<?php
/**
 * PDF Invoice Template
 *
 * Dati generali Pa.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\PDFInvoice\Templates
 */

?>
<?php $causale = apply_filters( 'ywpi_electronic_invoice_field_value', '', 'Causale', $document ); ?>
<?php $riferimento_numero_linea = apply_filters( 'ywpi_electronic_invoice_field_value', 1, 'RiferimentoNumeroLinea', $document ); ?>
<?php $order_total = $document->order->get_total(); ?>
<DatiGenerali>
	<DatiGeneraliDocumento>
		<TipoDocumento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', 'TD01', 'TipoDocumento', $document ) ); ?></TipoDocumento>
		<Divisa><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $document->order->get_currency(), 'Divisa', $document ) ); ?></Divisa>
		<Data><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $document->get_formatted_document_date(), 'Data', $document ) ); ?></Data>
		<Numero><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $document->formatted_number, 'Numero', $document ) ); ?></Numero> <!-- numero fattura -->
		<?php if ( '' !== $causale ) : ?>
			<Causale><?php echo wp_kses_post( $causale ); ?></Causale>
		<?php endif; ?>
		<ImportoTotaleDocumento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $order_total, 'ImportoTotaleDocumento', $document ) ); ?></ImportoTotaleDocumento>
	</DatiGeneraliDocumento>
	<DatiOrdineAcquisto>
		<IdDocumento><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', $document->order->get_meta( '_ywpi_invoice_number' ), 'IdDocumento', $document ) ); ?></IdDocumento>
		<?php if ( $document->is_pa_customer() ) : ?>
			<CodiceCUP><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'CodiceCUP', $document ) ); ?></CodiceCUP>
			<CodiceCIG><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'CodiceCIG', $document ) ); ?></CodiceCIG>
		<?php endif; ?>
	</DatiOrdineAcquisto>

	<?php if ( YITH_Electronic_Invoice()->include_tracking_info === 'yes' ) : ?>
		<DatiTrasporto>
			<DatiAnagraficiVettore>
				<IdFiscaleIVA>
					<IdPaese><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'IdPaese', $document ) ); ?></IdPaese>
					<IdCodice><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'IdCodice', $document ) ); ?></IdCodice>
				</IdFiscaleIVA>
				<Anagrafica>
					<Denominazione><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'Denominazione', $document ) ); ?></Denominazione>
				</Anagrafica>
			</DatiAnagraficiVettore>
			<DataOraConsegna><?php echo wp_kses_post( apply_filters( 'ywpi_electronic_invoice_field_value', '', 'DataOraConsegna', $document ) ); ?></DataOraConsegna>
		</DatiTrasporto>
	<?php endif; ?>

	<?php if ( $document->is_pa_customer() ) : ?>
		<DatiContratto>
			<RiferimentoNumeroLinea></RiferimentoNumeroLinea>
			<IdDocumento></IdDocumento>
			<Data></Data>
			<NumItem></NumItem>
			<CodiceCUP></CodiceCUP>
			<CodiceCIG></CodiceCIG>
		</DatiContratto>
		<DatiConvenzione>
			<RiferimentoNumeroLinea></RiferimentoNumeroLinea>
			<IdDocumento></IdDocumento>
			<NumItem></NumItem>
			<CodiceCUP></CodiceCUP>
			<CodiceCIG></CodiceCIG>
		</DatiConvenzione>
		<DatiRicezione>
			<RiferimentoNumeroLinea></RiferimentoNumeroLinea>
			<IdDocumento></IdDocumento>
			<NumItem></NumItem>
			<CodiceCUP></CodiceCUP>
			<CodiceCIG></CodiceCIG>
		</DatiRicezione>
	<?php endif; ?>
</DatiGenerali>
