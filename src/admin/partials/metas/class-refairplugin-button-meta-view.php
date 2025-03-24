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
class Refairplugin_Button_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'button';

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
	public function get_view( $view_content, $data, $value = array(
		'text' => null,
		'link' => null,
	) ) {

		ob_start();
		?>
		<p>
			<span>Button text:</span> 
			<input type="text" 
			name="<?php echo esc_attr( $data['name'] ); ?>[text]" 
			id="<?php echo esc_attr( $data['id'] ); ?>" 
			class="meta-video regular-text" 
			value="<?php echo wp_kses_post( $value['text'] ); ?>"/>
		</p>
		<p>
			<span>Button link:</span>
			<input 
				type="text" 
				name="<?php echo esc_attr( $data['name'] ); ?>[link]" 
				id="<?php echo esc_attr( $data['id'] ); ?>" 
				class="meta-video regular-text" 
				value="<?php echo wp_kses_post( $value['link'] ); ?>"/>
		</p>
		<?php
		$meta_view = ob_get_clean();
		return $view_content . $meta_view;
	}
}
