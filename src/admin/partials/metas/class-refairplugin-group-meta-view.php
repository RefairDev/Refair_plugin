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
use Refairplugin\Metas\Refairplugin_Utils;

/**
 * Class managing group meta view display and saving.
 */
class Refairplugin_Group_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'group';

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

		$groupview = $view_content;

		foreach ( $data['options']['metas'] as $meta ) {
			$meta_value = '';
			if ( isset( $value[ $meta->name ] ) ) {
				$meta_value = $value[ $meta->name ];}
			$meta_view_content         = apply_filters(
				'refairplugin_renderview_' . $meta->type,
				$view_content,
				array(
					'id'          => $data['id'] . '-' . $meta->name,
					'name'        => $data['name'] . '[' . $meta->name . ']',
					'meta_name'   => $meta->name,
					'description' => $meta->title,
					'options'     => $meta->options,
				),
				$meta_value
			);
			$wrapped_meta_view_content = apply_filters( 'refairplugin_wrapmeta_with_title', $meta_view_content, $meta->title );
			$groupview                 = $groupview . $wrapped_meta_view_content;
		}

		ob_start();
		?>
		<script>
			function group<?php echo esc_attr( $data['meta_name'] ); ?>Script(){
				<?php
				foreach ( $data['options']['metas'] as $meta ) {
					?>
					if (typeof <?php echo esc_attr( $meta->type . $meta->name ); ?>Script == 'function'){<?php echo esc_attr( $meta->type . $meta->name ); ?>Script()};
					<?php
				}
				?>
			}
		</script>
		<?php
		$group_script = ob_get_clean();
		$groupview    = $groupview . $group_script;

		$wrapped_meta_group_view_content = apply_filters( 'refairplugin_wrapmeta_with_title', $groupview, $data['title'] );
		return $wrapped_meta_group_view_content;
	}
}
