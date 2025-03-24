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
class Refairplugin_Image_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'image';

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
	 * Rebuild image url accodring to site url.
	 *
	 * @param  string $src Original url of the image.
	 * @return string Rebuilt image url.
	 */
	public static function url_img( $src ) {
		$url      = wp_parse_url( $src );
		$site_url = wp_parse_url( get_site_url() );

		return trim( $site_url['scheme'] . '://' . $site_url['host'] . $url['path'] );
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

		$meta_value = array(
			'url'     => '#',
			'caption' => '',
			'id'      => 0,
		);
		if ( isset( $value ) && is_array( $value ) && ! empty( $value ) ) {
			$meta_value = array_merge( $meta_value, $value );
		}
		ob_start();
		?>
		<p>
			<input type="hidden" name="<?php echo esc_attr( $data['name'] . '[url]' ); ?>" id="<?php echo esc_attr( $data['id'] . '-url' ); ?>" class="media-url regular-text" value="<?php echo esc_url( $meta_value['url'] ); ?>"/>
			<input type="hidden" name="<?php echo esc_attr( $data['name'] . '[id]' ); ?>" id="<?php echo esc_attr( $data['id'] . '-id' ); ?>" class="media-id regular-text" value="<?php echo esc_textarea( $meta_value['id'] ); ?>"/>
			<input type="button" class="button browse-image" value="Browse"/>
			<input type="text" name="<?php echo esc_attr( $data['name'] . '[caption]' ); ?>" id="<?php echo esc_attr( $data['id'] . '-caption' ); ?>" class="regular-text" value="<?php echo wp_kses_post( $meta_value['caption'] ); ?>"/>
		</p>
		<div class="image-preview">
			
			<?php
			$img_src = 'https://via.placeholder.com/250x150.jpg?text=Aucune+image';
			if ( $meta_value['url'] && '#' !== $meta_value['url'] ) {
				$img_src = $this->url_img( $meta_value['url'] );
			}
			if ( $meta_value['url'] && '#' !== $meta_value['url'] && ( 'pdf' === pathinfo( $meta_value['url'], PATHINFO_EXTENSION ) ) ) {
				$img_src = 'https://via.placeholder.com/250x150.jpg?text=Document+pdf';
			}
			?>
				<img style="max-width: 250px; box-shadow: 1px 2px 4px 0 #324664" src="<?php echo esc_url( $img_src ); ?>">
			
		</div>
		<script  type="text/javascript">
			function image<?php echo esc_attr( $data['meta_name'] ); ?>Script(){
				setBrowseImgHandling();
			}
		</script>
		<?php
		$image_meta_view = ob_get_clean();
		return $view_content . $image_meta_view;
	}
}
