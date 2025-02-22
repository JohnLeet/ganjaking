<?php
/**
 * Ajax widget to control layered navigation
 *
 * @package ajax-layered-nav
 */

// phpcs:disable Squiz.Commenting.FunctionComment.Missing, WordPress.Security.NonceVerification.Recommended

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax Layered Nav Widget
 */
class SOD_Widget_Ajax_Layered_Nav extends WC_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_widget_field_typeselector', array( $this, 'add_typeselector_field' ), 10, 4 );
		add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

		$this->widget_cssclass    = 'widget_layered_nav';
		$this->widget_description = __( 'Shows a custom attribute in a widget which lets you narrow down the list of products when viewing product categories.', 'woocommerce-ajax-layered-nav' );
		$this->widget_id          = 'sod_ajax_layered_nav';
		$this->widget_name        = __( 'WooCommerce Ajax Layered Nav', 'woocommerce-ajax-layered-nav' );
		parent::__construct();
	}

	/**
	 * Widgets Fields
	 *
	 * @param string $key Key.
	 * @param string $value Value.
	 * @param string $setting Setting.
	 * @param array  $instance Widget instance.
	 */
	public function add_typeselector_field( $key, $value, $setting, $instance ) {
		$display_type = isset( $instance['display_type'] ) ? $instance['display_type'] : 'list';
		$args         = array(
			'hide_empty' => '0',
		);
		$attributes   = isset( $instance['attribute'] ) ? get_terms( 'pa_' . $instance['attribute'], $args ) : null;
		$labels       = isset( $instance['labels'] ) ? maybe_unserialize( $instance['labels'] ) : false;
		$colors       = isset( $instance['colors'] ) ? maybe_unserialize( $instance['colors'] ) : false;

		?>
		<div id="<?php echo esc_attr( $this->get_field_id( 'labels' ) ); ?>">

		<?php
		switch ( $display_type ) {
			case 'colorpicker':
				?>
				<table class="color">
					<thead>
						<tr>
							<td><?php esc_html_e( 'Name', 'woocommerce-ajax-layered-nav' ); ?></td>
							<td><?php esc_html_e( 'Color Code', 'woocommerce-ajax-layered-nav' ); ?></td>
						</tr>
					</thead>
					<tbody>
					<?php
					if ( $attributes ) :
						foreach ( $attributes as $attribute ) {
							if ( $colors ) {
								if ( isset( $colors[ $attribute->term_id ] ) ) {
									$term_color = esc_attr( $colors[ $attribute->term_id ] );
									$value      = $term_color ? $term_color : '';
								} else {
									$value = '';
								}
							} else {
								$value = '';
							}
							?>
							<tr>
								<td class="labels">
									<label for="
											<?php echo esc_attr( $this->get_field_name( 'colors' ) ); ?>[<?php echo esc_attr( $instance['attribute'] ); ?>]">
											<?php echo esc_html( $attribute->name ); ?>
									</label>
								</td>
								<td class="inputs">
									<input
										class="color_input"
										type="text"
										name="<?php echo esc_attr( $this->get_field_name( 'colors' ) ); ?>[<?php echo esc_attr( $attribute->term_id ); ?>]"
										id="<?php echo esc_attr( $this->get_field_id( 'colors' ) ); ?>[<?php echo esc_attr( $attribute->term_id ); ?>]"
										value="<?php echo esc_attr( $value ); ?>"
										size="10"
										maxlength="7"
									/>
									<div class="colorSelector">
										<div style="background-color:<?php echo esc_attr( $value ); ?>"></div>
									</div>
								</td>
							</tr>
							<?php
						}
					endif;
					?>
					</tbody>
				</table>
				<?php
				break;
			case 'sizeselector':
				?>
				<table class="sizes">
					<thead>
						<tr>
							<td><?php esc_html_e( 'Name', 'woocommerce-ajax-layered-nav' ); ?></td>
							<td><?php esc_html_e( 'Label', 'woocommerce-ajax-layered-nav' ); ?></td>
						</tr>
					</thead>
					<tbody>
				<?php
				if ( $attributes ) :
					foreach ( $attributes as $attribute ) {
						if ( $labels ) {
							if ( isset( $labels[ $attribute->term_id ] ) ) {
								$label = esc_attr( $labels[ $attribute->term_id ] );
								$value = $label ? $label : '';
							} else {
								$value = '';
							}
						} else {
							$value = '';
						}
						?>
						<tr>
							<td class="labels">
								<label for="<?php echo esc_attr( $this->get_field_name( 'labels' ) ); ?>[<?php echo esc_attr( $instance['attribute'] ); ?>]">
								<?php echo esc_html( $attribute->name ); ?>
								</label>
							</td>
							<td class="inputs">
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_field_name( 'labels' ) ); ?>[<?php echo esc_attr( $attribute->term_id ); ?>]"
									id="<?php echo esc_attr( $this->get_field_id( 'labels' ) ); ?>[<?php echo esc_attr( $attribute->term_id ); ?>]"
									value="<?php echo esc_attr( $value ); ?>"
									size="3"
								/>
							</td>
						</tr>
							<?php
					}
					endif;
				?>
					</tbody>
				</table>
				<?php
				break;
		}
		?>
		</div>
		<?php
	}

	/**
	 * Update
	 *
	 * @param array $new_instance Widget instance.
	 * @param array $old_instance Widget instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$this->init_settings();
		$instance['title']        = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['attribute']    = isset( $new_instance['attribute'] ) ? $new_instance['attribute'] : '';
		$instance['query_type']   = $new_instance['query_type'];
		$instance['display_type'] = $new_instance['display_type'];
		$instance['labels']       = isset( $new_instance['labels'] ) ? $new_instance['labels'] : '';
		$instance['colors']       = isset( $new_instance['colors'] ) ? $new_instance['colors'] : '';
		$instance['show_count']   = isset( $new_instance['show_count'] ) ? $new_instance['show_count'] : '';
		$instance['hide_empty']   = isset( $new_instance['hide_empty'] ) ? $new_instance['hide_empty'] : '';
		parent::flush_widget_cache();
		return $instance;
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @see WP_Widget->form
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$this->init_settings();
		parent::form( $instance );
	}

	/**
	 * Init settings after post types are registered.
	 */
	public function init_settings() {
		$attribute_array      = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
					$attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
				}
			}
		}

		$this->settings = array(
			'title'        => array(
				'type'  => 'text',
				'std'   => __( 'Filter by', 'woocommerce-ajax-layered-nav' ),
				'label' => __( 'Title', 'woocommerce-ajax-layered-nav' ),
			),
			'attribute'    => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => __( 'Attribute', 'woocommerce-ajax-layered-nav' ),
				'class'   => 'layered_nav_attributes',
				'options' => $attribute_array,
			),
			'show_count'   => array(
				'type'  => 'checkbox',
				'std'   => '',
				'label' => __( 'Show Attribute Count', 'woocommerce-ajax-layered-nav' ),
			),
			'hide_empty'   => array(
				'type'  => 'checkbox',
				'std'   => '',
				'label' => __( 'Hide attributes if filtered count goes to zero', 'woocommerce-ajax-layered-nav' ),
			),
			'display_type' => array(
				'type'    => 'select',
				'std'     => 'list',
				'class'   => 'layered_nav_type',
				'label'   => __( 'Display type', 'woocommerce-ajax-layered-nav' ),
				'options' => array(
					'list'         => __( 'List', 'woocommerce-ajax-layered-nav' ),
					'dropdown'     => __( 'Dropdown', 'woocommerce-ajax-layered-nav' ),
					'checkbox'     => __( 'Checkbox', 'woocommerce-ajax-layered-nav' ),
					'sizeselector' => __( 'Size Selector', 'woocommerce-ajax-layered-nav' ),
					'colorpicker'  => __( 'Color Picker', 'woocommerce-ajax-layered-nav' ),
				),
			),
			'types'        => array(
				'type'  => 'typeselector',
				'std'   => '',
				'label' => '',
			),
			'query_type'   => array(
				'type'    => 'select',
				'std'     => 'and',
				'label'   => __( 'Query type', 'woocommerce-ajax-layered-nav' ),
				'options' => array(
					'and' => __( 'AND', 'woocommerce-ajax-layered-nav' ),
					'or'  => __( 'OR', 'woocommerce-ajax-layered-nav' ),
				),
			),
		);
	}

	protected function ajax_layered_nav_dropdown( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
		$found = false;

		if ( $taxonomy !== $this->get_current_taxonomy() ) {
			if ( $show_count ) {
				$ul_class = 'show-count';
			} else {
				$ul_class = '';
			}
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
			$link                 = $this->get_page_base_url( $taxonomy );
			echo '<nav><div class="ajax-layered"><select class="dropdown">';
			// translators: %s Taxonomy name.
			echo '<option data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" value="">' . esc_html( sprintf( __( 'Any %s', 'woocommerce-ajax-layered-nav' ), wc_attribute_label( $taxonomy ) ) ) . '</option>';

			foreach ( $terms as $term ) {
				$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
				$option_is_set  = in_array( $term->slug, $current_values, true );
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

				// skip the term for the current archive.
				if ( $this->get_current_term_id() === $term->term_id ) {
					continue;
				}
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
					continue;
				}
				$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
				$current_filter = array();
				if ( ! is_array( $current_filter ) ) {
					$current_filter = array();
				};
				$current_filter[] = $term->slug;
				$link             = $this->get_page_base_url( $taxonomy );
				if ( ! empty( $current_filter ) ) {
					$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
					if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
						$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
					}
				}

				// Allow themes and plugins to filter here.
				$link = apply_filters( 'woocommerce_ajax_layered_nav_term_link', $link, $term );

				if ( $option_is_set ) {
					$class = 'chosen filter-selected';
				} else {
					$class = '';
				}
				if ( $count > 0 || $option_is_set ) :
					if ( 0 === $count && $hide_empty ) {
						continue;
					} else {
						$taxonomy_filter = str_replace( 'pa_', '', $taxonomy );
						if ( $show_count ) {
							$name = sprintf( '%s (%s)', $term->name, $count );
						} else {
							$name = $term->name;
						}
						echo '<option data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" value="' . esc_attr( $term->slug ) . '" ' . selected( $option_is_set, true, false ) . '>' . esc_html( $name ) . '</option>';
					}
					endif;
			}
				echo '</select></div></nav>';
		}
		return $found;
	}

	protected function ajax_layered_nav_list( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
		$found = false;

		if ( '' !== $show_count ) {
			$ul_class = 'show-count';
		} else {
			$ul_class = '';
		}

		$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
		$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
		$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
		echo '<nav>
        <div class="ajax-layered"><ul>';
		foreach ( $terms as $term ) {
			$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
			$option_is_set  = in_array( $term->slug, $current_values, true );
			$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
				// skip the term for the current archive.
			if ( $this->get_current_term_id() === $term->term_id ) {
				continue;
			}
			if ( 0 < $count ) {
				$found = true;
			} elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
				continue;
			}
			$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
			$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
			$current_filter = array_map( 'sanitize_title', $current_filter );

			if ( ! in_array( $term->slug, $current_filter, true ) ) {
				$current_filter[] = $term->slug;
			}

			$link = $this->get_page_base_url( $taxonomy );
			// Add current filters to URL.
			foreach ( $current_filter as $key => $value ) {
				// Exclude query arg for current term archive term.
				if ( $value === $this->get_current_term_slug() ) {
					unset( $current_filter[ $key ] );
				}

				// Exclude self so filter can be unset on click.
				if ( $option_is_set && $value === $term->slug ) {
					unset( $current_filter[ $key ] );
				}
			}

			if ( ! empty( $current_filter ) ) {
				$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

				// Add Query type Arg to URL.
				if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
					$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
				}
			}

			// Allow themes and plugins to filter here.
			$link = apply_filters( 'woocommerce_ajax_layered_nav_term_link', $link, $term );

			if ( $option_is_set ) {
				$class = 'chosen filter-selected';
			} else {
				$class = '';
			}
			echo '<li class="wc-layered-nav-term ' . ( $option_is_set ? 'chosen filter-selected' : '' ) . '">';

			echo ( $count > 0 ) ? '<a href="#" data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-count="' . esc_attr( $count ) . '" data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '">' : '<span>';
			if ( 0 === $count && $hide_empty ) {
				continue;
			} else {
				echo esc_html( $term->name );
			}
			echo ( $count > 0 || $option_is_set ) ? '</a> ' : '</span> ';
			if ( $show_count ) {
				echo wp_kses_post( apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term ) );
			}
			echo '</li>';

		}
		echo '</ul></div></nav>';
		return $found;
	}

	protected function ajax_layered_nav_checkbox( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
		$found = false;

		if ( $taxonomy !== $this->get_current_taxonomy() ) {
			if ( $show_count ) {
				$ul_class = 'show-count';
			} else {
				$ul_class = '';
			}
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
			echo '<nav>
            <div class="ajax-layered"><ul class="checkboxes">';
			foreach ( $terms as $term ) {
				$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
				$option_is_set  = in_array( $term->slug, $current_values, true );
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
				// skip the term for the current archive.
				if ( $this->get_current_term_id() === $term->term_id ) {
					continue;
				}
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
					continue;
				}
				$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
				$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
				$current_filter = array_map( 'sanitize_title', $current_filter );
				if ( ! in_array( $term->slug, $current_filter, true ) ) {
					$current_filter[] = $term->slug;
				}
				$link = $this->get_page_base_url( $taxonomy );
				foreach ( $current_filter as $key => $value ) {
					// Exclude query arg for current term archive term.
					if ( $value === $this->get_current_term_slug() ) {
						unset( $current_filter[ $key ] );
					}

					// Exclude self so filter can be unset on click.
					if ( $option_is_set && $value === $term->slug ) {
						unset( $current_filter[ $key ] );
					}
				}

				if ( ! empty( $current_filter ) ) {
					$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
					if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
						$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
					}
				}

				// Allow themes and plugins to filter here.
				$link = apply_filters( 'woocommerce_ajax_layered_nav_term_link', $link, $term );

				if ( $option_is_set ) {
					$class = 'chosen filter-selected';
				} else {
					$class = '';
				}
				if ( $show_count ) {
					$class = 'chosen filter-selected show-count';
				}
				if ( $count > 0 || $option_is_set ) :
					echo '<li class="' . esc_attr( $class ) . '">';
					echo '<input type="checkbox" data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" ' . checked( $option_is_set, true, false ) . ' data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-count="' . esc_attr( $count ) . '" id="' . esc_attr( $term->name ) . '" name="' . esc_attr( $term->name ) . '" value="' . esc_attr( $term->name ) . '" />';
					echo '<label for="' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . '</label>';
					if ( $show_count ) {
						echo ' <small class="count">' . esc_attr( $count ) . '</small>';
					}
					echo '</li>';
				endif;
			}
			echo '</ul></div></nav>';
		}
		return $found;
	}

	protected function ajax_layered_nav_sizeselector( $terms, $taxonomy, $query_type, $labels, $show_count, $hide_empty ) {
		$found = false;

		if ( $taxonomy !== $this->get_current_taxonomy() ) {

			if ( $show_count ) {
				$ul_class = 'show-count';
			} else {
				$ul_class = '';
			}
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
			echo '<nav>
            <div class="ajax-layered"><ul class="sizes ' . esc_attr( $ul_class ) . '">';
			foreach ( $terms as $term ) {
				$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
				$option_is_set  = in_array( $term->slug, $current_values, true );
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
				// skip the term for the current archive.
				if ( $this->get_current_term_id() === $term->term_id ) {
					continue;
				}
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
					continue;
				}
				$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
				$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
				$current_filter = array_map( 'sanitize_title', $current_filter );
				if ( ! in_array( $term->slug, $current_filter, true ) ) {
					$current_filter[] = $term->slug;
				}
				$link = $this->get_page_base_url( $taxonomy );
				foreach ( $current_filter as $key => $value ) {
					// Exclude query arg for current term archive term.
					if ( $value === $this->get_current_term_slug() ) {
						unset( $current_filter[ $key ] );
					}

					// Exclude self so filter can be unset on click.
					if ( $option_is_set && $value === $term->slug ) {
						unset( $current_filter[ $key ] );
					}
				}

				if ( ! empty( $current_filter ) ) {
					$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
					if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
						$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
					}
				}

				// Allow themes and plugins to filter here.
				$link  = apply_filters( 'woocommerce_ajax_layered_nav_term_link', $link, $term );

				if ( $count > 0 || $option_is_set ) {
					if ( 0 === $count && $hide_empty ) {
						continue;
					} else {
						$temp_term_id = apply_filters( 'wc_ajax_layered_nav_sizeselector_term_id', $term->term_id );

						$label = ( ! empty( $labels[ $temp_term_id ] ) ? $labels[ $temp_term_id ] : $term->name );
						$class = ( $option_is_set ? 'chosen filter-selected' : '' );

						echo '<li class="' . esc_attr( $class ) . '">';
						echo '<a href="#" data-count="' . esc_attr( $count ) . '" data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" >';
						echo '<div class="size-filter">' . esc_html( $label ) . '</div>';
						echo '</a>';
						if ( $show_count ) {
							echo ' <small class="count">' . esc_html( $count ) . '</small>';
						}
						echo '</li>';
					}
				}
			}
			echo '</ul></div></nav>';
		}
		return $found;
	}

	protected function ajax_layered_nav_colorpicker( $terms, $taxonomy, $query_type, $colors, $show_count, $hide_empty ) {
		$found = false;
		if ( $taxonomy !== $this->get_current_taxonomy() ) {

			if ( $show_count ) {
				$ul_class = 'show-count';
			} else {
				$ul_class = '';
			}
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );

			echo '<nav>
            <div class="ajax-layered"><ul class="colors ' . esc_attr( $ul_class ) . '">';

			foreach ( $terms as $term ) {
				$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
				$option_is_set  = in_array( $term->slug, $current_values, true );
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
				// skip the term for the current archive.
				if ( $this->get_current_term_id() === $term->term_id ) {
					continue;
				}
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
					continue;
				}
				$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
				$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
				$current_filter = array_map( 'sanitize_title', $current_filter );
				if ( ! in_array( $term->slug, $current_filter, true ) ) {
					$current_filter[] = $term->slug;
				}
				$link = $this->get_page_base_url( $taxonomy );
				foreach ( $current_filter as $key => $value ) {
					// Exclude query arg for current term archive term.
					if ( $value === $this->get_current_term_slug() ) {
						unset( $current_filter[ $key ] );
					}

					// Exclude self so filter can be unset on click.
					if ( $option_is_set && $value === $term->slug ) {
						unset( $current_filter[ $key ] );
					}
				}

				if ( ! empty( $current_filter ) ) {
					$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
					if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
						$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
					}
				}

				// Allow themes and plugins to filter here.
				$link = apply_filters( 'woocommerce_ajax_layered_nav_term_link', $link, $term );

				$temp_term_id = apply_filters( 'wc_ajax_layered_nav_sizeselector_term_id', $term->term_id );

				$color = ( ! empty( $colors[ $temp_term_id ] ) ? $colors[ $temp_term_id ] : '#fff' );
				$class = ( $option_is_set ? 'chosen filter-selected' : '' );

				echo '<li class="' . esc_attr( $class ) . '">';

				if ( $count > 0 || $option_is_set ) :
					if ( 0 === $count && $hide_empty ) {
						continue;
					} else {
						echo '<a href="#" data-filter="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" data-count="' . esc_attr( $count ) . '" data-link="' . esc_attr( urldecode( esc_url( $link ) ) ) . '" >';
						echo '<div class="box has-count" style="background-color:' . esc_attr( $color ) . ';"></div>';
					}
				else :
					echo '<div class="box no-count" style="background-color:' . esc_attr( $color ) . ';"></div>';

				endif;
				if ( $count > 0 || $option_is_set ) :
					echo '</a>';
				endif;
				if ( $show_count ) {
					echo ' <small class="count">' . esc_html( $count ) . '</small>';
				}
				echo '</li>';
			}
			echo '</ul></div></nav>';
		}
		return $found;
	}

	/**
	 * Get current page URL for layered nav items.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	protected function get_page_base_url( $taxonomy ) {
		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {
			$link = get_post_type_archive_link( 'product' );
		} elseif ( is_product_category() ) {
			$link = get_term_link( get_query_var( 'product_cat' ), 'product_cat' );
		} elseif ( is_product_tag() ) {
			$link = get_term_link( get_query_var( 'product_tag' ), 'product_tag' );
		} else {
			$link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		}

		$link .= ( substr( $link, -1 ) === '/' ? '' : '/' );

		if ( isset( $_GET['min_price'] ) ) {
			$link = add_query_arg( 'min_price', wc_clean( wp_unslash( $_GET['min_price'] ) ), $link );
		}

		if ( isset( $_GET['max_price'] ) ) {
			$link = add_query_arg( 'max_price', wc_clean( wp_unslash( $_GET['max_price'] ) ), $link );
		}

		if ( isset( $_GET['orderby'] ) ) {
			$link = add_query_arg( 'orderby', wc_clean( wp_unslash( $_GET['orderby'] ) ), $link );
		}

		if ( get_search_query() ) {
			$link = add_query_arg( 's', get_search_query(), $link );
		}

		if ( isset( $_GET['post_type'] ) ) {
			$link = add_query_arg( 'post_type', wc_clean( wp_unslash( $_GET['post_type'] ) ), $link );
		}

		if ( isset( $_GET['min_rating'] ) ) {
			$link = add_query_arg( 'min_rating', wc_clean( wp_unslash( $_GET['min_rating'] ) ), $link );
		}

		// All current filters.
		$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		if ( $_chosen_attributes ) {
			foreach ( $_chosen_attributes as $name => $data ) {
				if ( $name === $taxonomy ) {
					continue;
				}
				$filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );
				if ( ! empty( $data['terms'] ) ) {
					$link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
				}
				if ( 'or' === $data['query_type'] ) {
					$link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
				}
			}
		}

		return $link;
	}

	public function widget( $args, $instance ) {
		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}

		$taxonomy = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$labels     = ( isset( $instance['labels'] ) ) ? maybe_unserialize( $instance['labels'] ) : null;
		$colors     = ( isset( $instance['colors'] ) ) ? maybe_unserialize( $instance['colors'] ) : null;
		$show_count = ( isset( $instance['show_count'] ) ) ? $instance['show_count'] : null;
		$hide_empty = ( isset( $instance['hide_empty'] ) ) ? $instance['hide_empty'] : null;

		$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		$query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];
		$display_type       = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];

		$get_terms_args = array( 'hide_empty' => '1' );

		$orderby = wc_attribute_orderby( $taxonomy );

		switch ( $orderby ) {
			case 'name':
				$get_terms_args['orderby']    = 'name';
				$get_terms_args['menu_order'] = false;
				break;
			case 'id':
				$get_terms_args['orderby']    = 'id';
				$get_terms_args['order']      = 'ASC';
				$get_terms_args['menu_order'] = false;
				break;
			case 'menu_order':
				$get_terms_args['menu_order'] = 'ASC';
				break;
		}

		$terms = get_terms( $taxonomy, $get_terms_args );

		if ( 0 === count( $terms ) ) {
			return;
		}

		ob_start();

		$this->widget_start( $args, $instance );
		switch ( $display_type ) {
			/* List of Checkboxes */
			case 'checkbox':
				$found = $this->ajax_layered_nav_checkbox( $terms, $taxonomy, $query_type, $show_count, $hide_empty );
				break;
			case 'dropdown':
				$found = $this->ajax_layered_nav_dropdown( $terms, $taxonomy, $query_type, $show_count, $hide_empty );
				break;

			/*Regular List of Terms*/
			case 'list':
				$found = $this->ajax_layered_nav_list( $terms, $taxonomy, $query_type, $show_count, $hide_empty );
				break;
					/* Size Labels */
			case 'sizeselector':
				$found = $this->ajax_layered_nav_sizeselector( $terms, $taxonomy, $query_type, $labels, $show_count, $hide_empty );
				break;
					/* Color Boxes*/
			case 'colorpicker':
				$found = $this->ajax_layered_nav_colorpicker( $terms, $taxonomy, $query_type, $colors, $show_count, $hide_empty );
				break;
		}

		echo '<div class="clear"></div>';
		$this->widget_end( $args );

		// Force found when option is selected - do not force found on taxonomy attributes.
		if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
			$found = true;
		}

		if ( ! $found ) {
			ob_end_clean();
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * @param  array  $term_ids List of term ids.
	 * @param  string $taxonomy Taxonomy name.
	 * @param  string $query_type Or or And.
	 * @return array
	 */
	protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		global $wpdb;

		$tax_query  = WC_Query::get_main_tax_query();
		$meta_query = WC_Query::get_main_meta_query();

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( isset( $query['taxonomy'] ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}
		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$query           = array();
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
            INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
            INNER JOIN {$wpdb->terms} AS terms USING( term_id )
            " . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
            WHERE {$wpdb->posts}.post_type IN ( 'product' )
            AND {$wpdb->posts}.post_status = 'publish'
            " . $tax_query_sql['where'] . $meta_query_sql['where'] . '
            AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')
        ';

		$search = WC_Query::get_main_search_query_sql();

		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
		$query             = implode( ' ', $query );
		$results           = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return wp_list_pluck( $results, 'term_count', 'term_count_id' );
	}

	/**
	 * Return the currently viewed taxonomy name.
	 *
	 * @return string
	 */
	protected function get_current_taxonomy() {
		return is_tax() ? get_queried_object()->taxonomy : '';
	}

	/**
	 * Return the currently viewed term ID.
	 *
	 * @return int
	 */
	protected function get_current_term_id() {
		return absint( is_tax() ? get_queried_object()->term_id : 0 );
	}

	/**
	 * Return the currently viewed term slug.
	 *
	 * @return int
	 */
	protected function get_current_term_slug() {
		return absint( is_tax() ? get_queried_object()->slug : 0 );
	}
}
