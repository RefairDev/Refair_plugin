<?php
/**
 * The admin-partials setting view of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin\Settings\Views;

use Refairplugin\Refairplugin_Setting_View;


class Refairplugin_Action_Setting_View extends Refairplugin_Setting_View {

	public static $type = 'action';

	public function __construct(
		$options = array()
	) {
		parent::__construct(
			$options = array()
		);
	}

	public function get_data_view( $view_content, $data, $value = null ) {

		ob_start();
		if ( isset( $data['options']['button_text'] ) && ! empty( $data['options']['button_text'] ) ) {
			echo '<button type="button" class="button button-secondary refairplugin-action-button-' . $data['name'] . '" data-action="' . esc_attr( $data['options']['url'] ) . '">' . esc_html( $data['options']['button_text'] ) . '</button>';
			//between script tag in javascript using data-action attribute fetch the url and execute the action.
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$("<?php echo '.refairplugin-action-button-' . $data['name']; ?>").on('click', function() {
						var actionUrl = $(this).data('action');
						if (actionUrl) {
							fetch(actionUrl, {
								method: 'GET',
								headers: {
									'Content-Type': 'application/json',
								}
							})
							.then(response => response.json())
							.then(data => {
								if ('success' === data.status) {
									alert(data.message);
								} else {
									alert('Error: ' + data.message);
								}
							})
							.catch(error => {
								alert('An error occurred: ' + error.message);
							});
						}
					});
				});
			</script>
			<?php
		}
		$out = ob_get_clean();

		return $view_content . $out;
	}

	/**
	 * Get heading html content of the setting.
	 *
	 * @param  string $heading_view Previous part of the view.
	 * @param  array  $data Data to use to generate setting content.
	 * @return string Heading view with this setting part.
	 */
	public function get_heading_view( $heading_view, $data ): string {
		return $data['label'];
	}
}
