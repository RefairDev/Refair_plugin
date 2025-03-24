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
 * Class managing checkbox meta view display and saving.
 */
class Refairplugin_Checkbox_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'checkbox';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct(
			$options
		);
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

		ob_start();
		?>
		<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo wp_kses_post( $data['description'] ); ?></label>

		<p id="<?php echo esc_attr( $data['id'] ); ?>">
			<?php
			foreach ( $data['options']['choices'] as $choice ) {
				if ( is_array( $choice ) ) {
					$choice_value = $choice['value'];
					$choice_name  = $choice['name'];
				} else {
					$choice_value = $choice->value;
					$choice_name  = $choice->name;
				}
				?>
				<label for="<?php echo esc_attr( $data['id'] . '-' . $choice_value ); ?>">
					<input 
						type="checkbox" 
						name="<?php echo esc_attr( $data['name'] ); ?>[]"
						id="<?php echo esc_attr( $data['id'] . '-' . $choice_value ); ?>"
						value="<?php echo wp_kses_post( $choice_value ); ?>"
								<?php if ( is_array( $value ) && in_array( $choice_value, $value, true ) ) : ?>
							checked
						<?php endif ?>
						/>
								<?php echo wp_kses_post( $choice_name ); ?>
				</label>
								<?php
			}
			?>
		</p>
		<?php
		$meta_view = ob_get_clean();
		return $view_content . $meta_view;
	}
}
