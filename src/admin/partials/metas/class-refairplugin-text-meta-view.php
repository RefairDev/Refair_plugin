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
use Refairplugin;

require_once 'class-refairplugin-input-meta-view.php';

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Text_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'text';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
		$this->set_type( 'text' );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Number_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'number';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
		$this->set_type( 'number' );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Date_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'date';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
		$this->set_type( 'date' );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Time_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'time';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
		$this->set_type( 'time' );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_Email_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'mail';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
		$this->set_type( 'email' );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Refairplugin_LongText_Meta_View extends Refairplugin_Meta_View {


	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'long';

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
		ob_start();
		?>
		<p>
			<textarea style="max-width: 25em; min-height: 120px;" name="<?php echo esc_attr( $data['name'] ); ?>" id="<?php echo esc_attr( $data['name'] ); ?>" class="regular-text"><?php echo wp_kses_post( $value ); ?></textarea>
		</p>
		<?php
		$view_meta = ob_get_clean();
		return $view_content . $view_meta;
	}
}


if ( ! function_exists( 'wp_meta_editor' ) ) {
	/**
	 * Function initializing WP editor WYSIWYG
	 *
	 * @param  string $content Content to set on start.
	 * @param  string $editor_id Editor Id tag.
	 * @param  array $settings Settings of th tinymce editor.
	 * @return string Html content of the tinymce.
	 */
	function wp_meta_editor( $content, $editor_id, $settings ) {
		ob_start();
		wp_editor( htmlspecialchars_decode( $content, ENT_QUOTES ), $editor_id, $settings );
		$out           = ob_get_contents();
		$js            = wp_json_encode( $out );
		$id_editor_ctn = $editor_id . '-ctn';
		ob_clean();
		?>
			<div id="<?php echo esc_attr( $id_editor_ctn ); ?>"></div>
			<script>
			setTimeout(function() {
					// Inject editor later.
					var id_ctn = '#<?php echo esc_attr( $id_editor_ctn ); ?>';
					jQuery(id_ctn).append(<?php echo $js; ?>); 
					// Init editor.
					setTimeout(function() {
						// Find the editor button and simulate a click on it.
						jQuery('#<?php echo esc_attr( $editor_id ); ?>-tmce').trigger('click');
					},   500);
			}, 1000);
			</script>
			<?php
			$out = ob_get_contents();
			ob_get_clean();
			return $out;
	}
}

/**
 * Class managing button meta view display and saving.
 */
class Editor_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'editor';

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
		global $post;
		$settings = array(
			'textarea_name' => $data['name'],
			'quicktags'     => array( 'buttons' => 'em,strong,link' ),
			'tinymce'       => array(
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_css'    => '<style>.wp-editor-area{height:175px; width:100%;}</style>',
		);
		return $view_content . wp_meta_editor( $value, $data['id'] . '-' . $post->ID, $settings );
	}
}

/**
 * Class managing button meta view display and saving.
 */
class KeyValue_Meta_View extends Refairplugin_Meta_View {
	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'pair';

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
		$key_value   = '';
		$value_value = '';
		if ( ! empty( $value ) && is_array( $value ) ) {
			if ( array_key_exists( 'key', $value ) ) {
				$key_value = 'value="' . $value['key'] . '"';}
			if ( array_key_exists( 'value', $value ) ) {
				$value_value = 'value="' . $value['value'] . '"';}
		}
		$viewcontent = '<p>
        <input type="text" name="' . $data['name'] . '[key]" id="' . $data['name'] . '-key" ' . $key_value . '/>
        <input type="text" name="' . $data['name'] . '[value]" id="' . $data['name'] . '-value" ' . $value_value . '/>
        </p>';
		return $viewcontent;
	}
}
