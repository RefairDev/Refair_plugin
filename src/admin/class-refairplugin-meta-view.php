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
 * Meta view abstract to be extend extend for all type of meta box.
 */
abstract class Refairplugin_Meta_View {

	/**
	 * Class instance for the singleton.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Options used generate view properly.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Type of meta.
	 *
	 * @var string
	 */
	public static $type;

	/**
	 * Constructor which set internal variables and view rendering filter.
	 *
	 * @param  array $options Options used to init meta view.
	 */
	public function __construct(
		$options = array()
	) {
		$this->options = $options;

		$filter_name = 'refairplugin_renderview_' . static::$type;
		add_filter( $filter_name, array( $this, 'get_view' ), 10, 3 );
	}

	/**
	 * Accessor to singleton instance.
	 *
	 * @return Refairplugin_Meta_View singleton instance.
	 */
	public static function get_instance() {

		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}
		return self::$instances[ $class ];
	}

	/**
	 * Abstract function to get view content of the metabox.
	 *
	 * @param  mixed $value Input value for metabox inputs.
	 * @param  array $data Data to set metabox.
	 * @return string View content.
	 */
	abstract public function get_view( $value, $data );
}
