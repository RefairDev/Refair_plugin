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

namespace Refairplugin\Metas\Refairplugin_Utils;

/**
 * Wrap around view a div with a nonce hidden field.
 *
 * @param string $view meta controller view.
 * @param string $name name of the meta.
 * @return string html wrapped view with nonce field.
 */
function wrap_view( $view, $name ) {

	$html  = '<div><input type="hidden" name="' . $name . '_nonce" value="' . wp_create_nonce( 'post_meta' . $name ) . '">';
	$html .= $view;
	$html .= '</div>';

	return $html;
}
add_filter( 'refairplugin_wrapview', 'Refairplugin\Metas\Refairplugin_Utils\wrap_view', 10, 2 );



/**
 * Add a title tag and a wrap div around the meta controller view.
 *
 * @param string $view meta controller view.
 * @param string $title title of the meta.
 * @return string html wrapped view with title.
 */
function wrap_meta_with_title( $view, $title ) {

	$html  = '<h3>' . $title . '</h3><div>';
	$html .= $view;
	$html .= '</div>';

	return $html;
}
add_filter( 'refairplugin_wrapmeta_with_title', 'Refairplugin\Metas\Refairplugin_Utils\wrap_meta_with_title', 10, 2 );


/**
 *  Add image meta browse script has to be added this function add JS code to brose iamges.
 */
function add_image_browse_script() {

	ob_start();
	?>
	<script type="text/javascript">

	jQuery(document).ready(function ($) {
		// Instantiates the variable that holds the media library frame.
		var meta_image_frame;
		// Runs when the image button is clicked.
		setBrowseImgHandling();
	});

	function setBrowseImgHandling(){
		jQuery('.browse-image').off("click");
			jQuery('.browse-image').click(function (e) {

			e.preventDefault();
			var meta_image_preview = jQuery(this).parent().next('.image-preview');            
			var meta_image = jQuery(this).parent().children('.media-url');
			var meta_id = jQuery(this).parent().children('.media-id');
			// If the frame already exists, re-open it.
			/*if (meta_image_frame) {
				meta_image_frame.open();
				return;
			}*/
			// Sets up the media library frame
			var meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
				title: meta_image.title,
				// TVI: 2019_06_03 comment => conflict with jquery-ui button
				// button: {
				//   text: meta_image.button
				// }
			});
			// Runs when an image is selected.
			meta_image_frame.on('select', function () {
				// Grabs the attachment selection and creates a JSON representation of the model.
				var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
				// Sends the attachment URL to our custom image input field.
				meta_image.val(media_attachment.url);
				meta_id.val(media_attachment.id);
	
				meta_image_preview.html('<img style="max-width: 250px; box-shadow: 1px 2px 4px 0 #324664" />');
	
				meta_image_preview.children('img').attr('src', media_attachment.url);
				//meta_image_preview.children('video')[0].load();
			});
			// Opens the media library frame.
			meta_image_frame.open();
			});
	}
		
	</script>
	<?php
}


add_action( 'admin_print_footer_scripts', 'Refairplugin\Metas\Refairplugin_Utils\add_image_browse_script' );
add_filter( 'refairplugin_add_image_browse_script', 'Refairplugin\Metas\Refairplugin_Utils\add_image_browse_script' );

/**
 * Add video browse script.
 *
 * @return void
 */
function add_video_browse_script() {
	?>
	<script>
	jQuery(document).ready(function ($) {
	// Instantiates the variable that holds the media library frame.
	var meta_image_frame;
	// Runs when the image button is clicked.
	$('.video-upload').click(function (e) {
		// Get preview pane
		var meta_image_preview = $(this).parent().next('.video-preview');
		// Prevents the default action from occuring.
		e.preventDefault();
		var meta_image = $(this).parent().children('.meta-video');
		// If the frame already exists, re-open it.
		/*if (meta_image_frame) {
		meta_image_frame.open();
		return;
		}*/
		// Sets up the media library frame
		meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
		title: meta_image.title,
		button: {
			text: meta_image.button
		}
		});
		// Runs when an image is selected.
		meta_image_frame.on('select', function () {
		// Grabs the attachment selection and creates a JSON representation of the model.
		var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
		// Sends the attachment URL to our custom image input field.
		meta_image.val(media_attachment.url);

		meta_image_preview.html('<video style="max-width: 250px; box-shadow: 1px 2px 4px 0 #324664" controls><source src="" type="video/mp4"></video>');

		meta_image_preview.children('video').children('source').attr('src', media_attachment.url);
		meta_image_preview.children('video')[0].load();
		});
		// Opens the media library frame.
		meta_image_frame.open();
	});
	});
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'Refairplugin\Metas\Refairplugin_Utils\add_video_browse_script' );
add_filter( 'refairplugin_add_video_browse_script', 'Refairplugin\Metas\Refairplugin_Utils\add_video_browse_script' );
