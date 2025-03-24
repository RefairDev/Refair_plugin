<?php
/**
 * The admin-partials view of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin\Metas\Views;

use Refairplugin\Refairplugin_Meta_View;

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Term_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Meta view slug
	 *
	 * @var string
	 */
	public static $type = 'term';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
	}

	/**
	 * Get html content of the meta box.
	 *
	 * @param  string $view_content Previous content of the view of post meta boxes.
	 * @param  array  $data Data to use to generate metabox content.
	 * @param  mixed  $value Value to set to metabox inputs.
	 * @return string Content of view added with the current metabox.
	 */
	public function get_view( $view_content, $data, $value = null ) {

		$options                = array();
		$term_meta_view_content = '';
		$get_taxo_args          = array();
		$multiple_attr          = '';

		if ( array_key_exists( 'options', $data ) ) {
			$options = $data['options'];
		}

		if ( array_key_exists( 'taxonomy', $options ) && taxonomy_exists( $options['taxonomy'] ) ) {
			$get_taxo_args['name'] = $options['taxonomy'];
		}

		if ( array_key_exists( 'post_type', $options ) && post_type_exists( $options['post_type'] ) ) {
			$get_taxo_args['object_type'] = $options['post_type'];
		}
		$taxonomies = get_taxonomies( $get_taxo_args, 'objects' );

		if ( array_key_exists( 'multiple', $options ) && ( true === $options['multiple'] ) ) {
			$multiple_attr = 'multiple';
		}

		ob_start();

		?>

		<select name="<?php echo esc_attr( $data['name'] ); ?>" id="<?php echo esc_attr( $data['id'] ); ?>" <?php echo esc_attr( $multiple_attr ); ?>>
		<?php
		foreach ( $taxonomies as $current_taxo ) {
			?>
			<optgroup label="<?php echo wp_kses( $current_taxo->label, wp_kses_allowed_html( 'strip' ) ); ?>">
			<?php
			$terms = get_terms(
				array(
					'taxonomy'   => $current_taxo->name,
					'hide_empty' => false,
				)
			);

			foreach ( $terms as $term ) {

				$selected = '';
				if ( ! empty( $value ) && ( intval( $value ) === $term->term_taxonomy_id ) ) {
					$selected = ' selected ';
				}
				?>
				<option value="<?php echo wp_kses( $term->term_taxonomy_id, wp_kses_allowed_html( 'strip' ) ); ?>"<?php echo wp_kses( $selected, wp_kses_allowed_html( 'strip' ) ); ?>><?php echo wp_kses( $term->name, wp_kses_allowed_html( 'strip' ) ); ?></option>
				<?php
			}
			?>
			</optgroup>
			<?php
		}
		?>

		</select>

		<?php

		$term_meta_view_content = ob_get_clean();

		return $view_content . $term_meta_view_content;
	}
}
