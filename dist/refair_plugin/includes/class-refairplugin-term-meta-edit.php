<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin;

/**
 * Class for creating term meta edit.
 */
class Refairplugin_Term_Meta_Edit {

	/**
	 * Meta name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Meta description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Taxonomy of the term.
	 *
	 * @var string
	 */
	protected $taxonomy = '';

	/**
	 * Post type associated with the Term.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * Options used on meta view creation.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Type of meta.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Nonce for security purpose.
	 *
	 * @var string
	 */
	protected $nonce = '';

	/**
	 * Class constructor
	 *
	 * @param  int                          $post_type post type associated with meta.
	 * @param  string                       $taxonomy taxonomy associated with Term.
	 * @param  Refairplugin_Meta_Parameters $meta input parameter for meta creation.
	 */
	public function __construct( $post_type, $taxonomy, $meta ) {
		$this->name = $meta['name'];

		$this->description = $meta['title'];

		$this->taxonomy = $taxonomy;

		$this->post_type = $post_type;

		if ( ! array_key_exists( 'options', $meta ) || ! is_array( $meta['options'] ) ) {
			$meta['options'] = array();
		}
		$this->options = $meta['options'];

		$this->type = $meta['type'];

		$this->nonce = str_replace( '_meta', '', $this->name ) . '_nonce';

		$this->init();
	}

	/**
	 * Set all actions hooks.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, '___register_term_meta_text' ) );
		add_action( "{$this->taxonomy}_add_form_fields", array( $this, '___add_form_field_term_meta_text' ) );
		add_action( "{$this->taxonomy}_edit_form_fields", array( $this, '___edit_form_field_term_meta_text' ) );
		add_action( "edit_{$this->taxonomy}", array( $this, '___save_term_meta_text' ) );
		add_action( "create_{$this->taxonomy}", array( $this, '___save_term_meta_text' ) );

		if ( ( array_key_exists( 'show_in_columns', $this->options ) && true === $this->options['show_in_columns'] ) || ( ! array_key_exists( 'show_in_columns', $this->options ) ) ) {
			add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, '___edit_term_columns' ), 10, 3 );
		}
		add_filter( "manage_{$this->taxonomy}_custom_column", array( $this, '___manage_term_custom_column' ), 10, 3 );
	}

	/**
	 * Meta registering.
	 *
	 * @return void
	 */
	public function ___register_term_meta_text() {
		register_term_meta(
			$this->taxonomy,
			$this->name,
			array(
				'sanitize_callback' => array( $this, '___sanitize_term_meta_text' ),
				'show_in_rest'      => true,
				'single'            => true,
			)
		);
	}

	/**
	 * Sanitize meta term value.
	 *
	 * @param  string $value Value to sanitize.
	 * @return string Sanitized value.
	 */
	public function ___sanitize_term_meta_text( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * GETTER (will be sanitized).
	 *
	 * @param  int $term_id Term ID.
	 * @return mixed value of the term meta.
	 */
	public function ___get_term_meta_text( $term_id ) {
		$value = get_term_meta( $term_id, $this->name, true );
		$value = $this->___sanitize_term_meta_text( $value );
		return $value;
	}


	/**
	 * ADD FIELD TO CATEGORY TERM PAGE.
	 *
	 * @return void
	 */
	public function ___add_form_field_term_meta_text() {
		?>
		<?php wp_nonce_field( basename( __FILE__ ), '<?php echo $this->name;?>_nonce' ); ?>
		<div class="form-field <?php echo esc_attr( $this->name ); ?>-wrap">
			<label for="<?php echo esc_attr( $this->name ); ?>"><?php echo wp_kses_post( $this->description ); ?></label>			
			<?php
			switch ( $this->type ) {
				case 'text':
				case 'number':
				case 'url':
				case 'email':
					?>
					<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name ); ?>" value="" class="<?php echo esc_attr( $this->name ); ?>-field" />
					<?php
					break;

				case 'radio':
					foreach ( $this->options['choices'] as $choice ) {
						?>
						<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name . '-' . $choice['value'] ); ?>" value="<?php echo esc_attr( $choice['value'] ); ?>" class="<?php echo esc_attr( $this->name ); ?>-field" />
						<label for='<?php echo esc_attr( $this->name . '-' . $choice['value'] ); ?>'><?php echo esc_html( $choice['label'] ); ?></label>
						<?php
					}
					break;
				default:
					?>
					<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name ); ?>" value="" class="<?php echo esc_attr( $this->name ); ?>-field" />
					<?php

			}
			if ( $this->options && array_key_exists( 'description', $this->options ) && ! empty( $this->options['description'] ) ) {
				?>
				<p class="description"><?php echo esc_html( $this->options['description'] ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
	}


		/**
		 * ADD FIELD TO CATEGORY EDIT PAGE.
		 *
		 * @param  WP_Term $term Term which is edited.
		 * @return void
		 */
	public function ___edit_form_field_term_meta_text( $term ) {

		$value = $this->___get_term_meta_text( $term->term_id );

		if ( ! $value ) {
			$value = '';
		}
		?>

		<tr class="form-field term-meta-text-wrap">
			<th scope="row"><label for="term-meta-text"><?php echo $this->description; ?></label></th>
			<td>
				<?php wp_nonce_field( basename( __FILE__ ), 'term_meta_text_nonce' ); ?>
				<?php
				switch ( $this->type ) {
					case 'text':
					case 'number':
					case 'url':
					case 'email':
						?>
						<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_html( $value ); ?> " class="<?php echo esc_attr( $this->name ); ?>-field" />
						<?php
						break;
					case 'radio':
						foreach ( $this->options['choices'] as $choice ) {
							?>

						<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name . '-' . $choice['value'] ); ?>" value="<?php echo esc_attr( $choice['value'] ); ?>" class="<?php echo esc_attr( $this->name ); ?>-field" 
												<?php
												if ( $value === $choice['value'] ) {
													echo 'checked'; }
												?>
						/>
						<label for='<?php echo esc_attr( $this->name . '-' . $choice['value'] ); ?>'><?php echo esc_html( $choice['label'] ); ?></label>
							<?php
						}
						break;
					default:
						?>
					<input type="<?php echo esc_attr( $this->type ); ?>" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name ); ?>" value="<?php echo esc_html( $value ); ?>" class="<?php echo esc_attr( $this->name ); ?>-field" />
						<?php
				}
				if ( $this->options && array_key_exists( 'description', $this->options ) && ! empty( $this->options['description'] ) ) {
					?>
					<p class="description"><?php echo esc_html( $this->options['description'] ); ?></p>
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * SAVE TERM META (on term edit & create)
	 *
	 * @param  int $term_id Term Id.
	 * @return void
	 */
	public function ___save_term_meta_text( $term_id ) {

		// verify the nonce --- remove if you don't care.
		if ( ! isset( $_POST['term_meta_text_nonce'] ) || ! wp_verify_nonce( $_POST['term_meta_text_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		$old_value = $this->___get_term_meta_text( $term_id );
		$new_value = isset( $_POST[ $this->name ] ) ? $this->___sanitize_term_meta_text( $_POST[ $this->name ] ) : '';

		if ( $old_value && '' === $new_value ) {
			delete_term_meta( $term_id, $this->name );

		} elseif ( $old_value !== $new_value ) {
			update_term_meta( $term_id, $this->name, $new_value );
		}
	}

	/**
	 * MODIFY COLUMNS (add our meta to the list)
	 *
	 * @param  array $columns All columns of Term edit list.
	 * @return array Columns list modified.
	 */
	public function ___edit_term_columns( $columns ) {

		$columns[ $this->name ] = $this->description;

		return $columns;
	}


	/**
	 * RENDER COLUMNS (render the meta data on a column)
	 *
	 * @param  array  $out Column cell content.
	 * @param  string $column Column name.
	 * @param  int    $term_id Term ID.
	 * @return string Column cell content modified.
	 */
	public function ___manage_term_custom_column( $out, $column, $term_id ) {

		if ( $this->name === $column ) {

			$value = $this->___get_term_meta_text( $term_id );

			if ( ! $value ) {
				$value = '';
			}

			$out = sprintf( '<span class="term-meta-text-block" style="" >%s</div>', esc_attr( $value ) );
		}

		return $out;
	}
}
