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

use Refairplugin\Refairplugin_Meta_Parameters;
use Refairplugin\Refairplugin_Utils;
use Metas;
use Refairplugin\Refairplugin_Meta_View;
use Refairplugin\Meta;

/**
 * Class used to build all meta view builders.
 */
class Refairplugin_Metas_Factory {

	/**
	 * Slug identifing factory.
	 *
	 * @var string
	 */
	private $slug = '';

	/**
	 * Post type associated with the factory.
	 *
	 * @var string
	 */
	private $post_type = '';

	/**
	 * For page post type template is used to target specific type of page.
	 *
	 * @var string
	 */
	private $template = '';

	/**
	 * Array of meta view class instances.
	 *
	 * @var array
	 */
	private static $meta_classes = array();

	/**
	 * Meta views namespace.
	 *
	 * @var string
	 */
	private static $metas_views_namespace = '\\Refairplugin\\Metas\\Views\\';

	/**
	 * Class constructor to set internal variables and get reference to all meta View singleton instance.
	 *
	 * @param  string $post_type Post type linked to factory.
	 * @param  array  $options Options for Factory creation.
	 */
	public function __construct( string $post_type, array $options = array() ) {

		if ( isset( $post_type ) && ! empty( $post_type ) ) {
			if ( isset( $options['template'] ) && ! empty( $options['template'] ) ) {
				$this->template = $options['template'];
				$this->slug     = $post_type . '_' . $this->template;
			} else {
				$this->slug = $post_type;
			}
			$this->post_type = $post_type;

			$metas_views_dir = plugin_dir_path( __DIR__ ) . 'admin/partials/metas';

			$files = Refairplugin_Utils::require( $metas_views_dir );

			foreach ( $files as $file ) {
				$classes = Refairplugin_Utils::file_get_php_classes( $file );
				foreach ( $classes as $class ) {
					try {
						$full_class_name = self::$metas_views_namespace . $class;
						if ( property_exists( $full_class_name, 'type' ) ) {
							self::$meta_classes[ $full_class_name::$type ] = $full_class_name::get_instance();
						}
					} catch ( \Exception $exception ) {
						trigger_error( wp_kses_post( 'Meta control has no type:' . $full_class_name ) );
					}
				}
			}
		} else {
			trigger_error( wp_kses_post( 'No post type supplied' ) );
		}
	}

	/**
	 * Accessor to factory slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Accessor to factory template.
	 *
	 * @return string
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Create Meta according to input parameters.
	 *
	 * @param  Refairplugin_Meta_Parameters $meta input parameters for metea creation.
	 * @return Refairplugin_Meta Meta created.
	 */
	public function create( Refairplugin_Meta_Parameters $meta ) {

		if ( ! isset( $meta->options ) ) {
			$meta->options = array();}
		$meta->options['template'] = $this->template;

		$meta = new Refairplugin_Meta( $this->post_type, $meta );

		return $meta;
	}
}
