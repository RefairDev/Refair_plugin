<?php
/**
 * Fired during plugin activation
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */
class Refairplugin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// If there is no advanced product type taxonomy, add it.
		if ( ! get_term_by( 'slug', 'deposit_item', 'product_type' ) ) {
			wp_insert_term( 'deposit_item', 'product_type' );
		}
	}
}
