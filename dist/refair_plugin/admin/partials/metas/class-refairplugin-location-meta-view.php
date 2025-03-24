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
 * Class managing location map meta view display and saving.
 */
class Refairplugin_Location_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'location';

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
	 * Function used to add inline script tag.
	 *
	 * @param  array $data Data to use to generate metabox content.
	 *
	 * @return void
	 */
	protected function script( $data ) {
		?>
			<script type="text/javascript">
	
				var moduleLOC = (function($){
	
					var API_KEY = get_option('google_api_key','[GOOGLE_API_KEY]');
	
					var $lat = $('[name="<?php echo esc_attr( $data['name'] ); ?>[lat]"]');
					var $lng = $('[name="<?php echo esc_attr( $data['name'] ); ?>[lng]"]');
					var $location = $('[name="<?php echo esc_attr( $data['name'] ); ?>[location]"]');
	
					return function(e) {    
						e.preventDefault();    
						var value = $(this).prev().val();    
						jQuery.ajax({
							url: 'https://maps.googleapis.com/maps/api/geocode/json?address=' + encodeURI(value) + '&key=' + API_KEY,
							success: function(resp) {
								var result = resp.results[0];
								var address = result.formatted_address;
	
								var location = result.geometry.location;
	
								$lat.val(location.lat);
								$lng.val(location.lng);
	
								$location.val(address);
							}
						});
	
					}
	
				})(jQuery);
	
				jQuery('#search').on('click', moduleLOC)                
	
				/*jQuery.ajax({
					url: 'https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=' + API_KEY,
					success: function(resp) {
						console.log(resp)
					}
				})*/
			</script>
		<?php
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
			<input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[location]" id="<?php echo esc_attr( $data['id'] ); ?>[location]" class="meta-video regular-text" value="<?php echo wp_kses_post( $value['location'] ); ?>"/>
			<button id="search">Search</button>

		</p>
		<p>
			<input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[lat]" id="<?php echo esc_attr( $data['id'] ); ?>[lat]" class="meta-video regular-text" value="<?php echo wp_kses_post( $value['lat'] ); ?>"/>

			<input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[lng]" id="<?php echo esc_attr( $data['id'] ); ?>[lng]" class="meta-video regular-text" value="<?php echo wp_kses_post( $value['lng'] ); ?>"/>
		</p>
		<?php
		$this->script( $data );
		$meta_view = ob_get_clean();
		return $view_content . $meta_view;
	}
}