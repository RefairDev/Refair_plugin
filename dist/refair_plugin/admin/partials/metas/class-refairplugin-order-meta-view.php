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

use Refairplugin\Metas\Views\Refairplugin_Input_Meta_View;

/**
 * Class managing order meta view display and saving.
 */
class Refairplugin_Order_Meta_View extends Refairplugin_Input_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'order';

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
