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

if ( ! class_exists( 'Refairplugin_Input_Meta_View' ) ) {
	/**
	 * Class managing input meta view display and saving.
	 */
	class Refairplugin_Input_Meta_View extends Refairplugin_Meta_View {


		/**
		 * Slug identifiing type of the view meta.
		 *
		 * @var string
		 */
		protected $input_type;

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
		 * Set the type of the meta box
		 *
		 * @param  string $type Type of the meta box.
		 * @return void
		 */
		public function set_type( $type ) {
			$this->input_type = $type;
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
			if ( isset( $data['id'] ) ) {
				$id = $data['id'];} else {
				$re    = '/(.*)\[([0-9]+)\]/m';
				$subst = '$1-$2';
				$id    = preg_replace( $re, $subst, $data['name'] );
				}
				ob_start();
				?><p>
				<input type="<?php echo esc_attr( $this->input_type ); ?>" name="<?php echo esc_attr( $data['name'] ); ?>" id="<?php echo esc_attr( $id ); ?>" class="meta-video regular-text" value="<?php echo wp_kses_post( $value ); ?>"/>
			</p>
			<?php
			$meta_viewcontent = ob_get_clean();
			return $view_content . $meta_viewcontent;
		}
	}
}
