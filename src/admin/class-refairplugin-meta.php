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

use Refairplugin\Metas\Views;
use Refairplugin\Metas\Refairplugin_Utils;
/**
 * Meta class for metabox creation and saving management.
 */
class Refairplugin_Meta {

	/**
	 * Name of the meta
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Description of the meta
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Post type associated with the meta
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Options used on meta view settup.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Type of meta (texfield, radio, image, etc).
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Nonce for consistency verification.
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * View of the meta box.
	 *
	 * @var string.
	 */
	protected $view;

	/**
	 * Meta constructor
	 *
	 * @param  string                       $post_type Post type associated to the meta.
	 * @param  Refairplugin_Meta_Parameters $meta Parameters for meta creation.
	 */
	public function __construct( $post_type, $meta ) {
		$this->name = $meta->name;

		$this->description = $meta->title;

		$this->post_type = $post_type;

		$this->options = $meta->options;

		$this->type = $meta->type;

		$this->nonce = str_replace( '_meta', '', $this->name ) . '_nonce';

		$this->init();
	}

	/**
	 * Hooks initialization.
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'current_screen', array( $this, 'set_edit_actions' ) );
	}

	/**
	 * Set actions for metabox display and matabox saving.
	 *
	 * @param  WP_screen $screen current screen displayed.
	 * @return void
	 */
	public function set_edit_actions( $screen ) {

		if ( $screen->post_type === $this->post_type && 'post' === $screen->base ) {
			add_action( 'add_meta_boxes', array( $this, 'exec' ) );
			add_action( 'save_post_' . $this->post_type, array( $this, 'save' ) );
		}
	}

	/**
	 * Set view associated to the meta.
	 *
	 * @param  string $views View of the meta.
	 * @return void
	 */
	public function set_view( $views ) {
		$this->view = $views;
	}

	/**
	 * Save the meta.
	 *
	 * @param  int    $post_id Post ID associated with the meta.
	 * @param  string $meta_name slug of the meta.
	 * @param  string $nonce For security verification.
	 * @return mixed post id on failure nothing on success.
	 */
	private function save_meta( $post_id, $meta_name, $nonce ) {
		// Verify nonce.
		/*
		if ( !wp_verify_nonce( $_POST[$nonce], 'my-action_'.$post_id ) ) {
			return $post_id;
		}
		*/

		if ( isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		$old = get_post_meta( $post_id, $meta_name, true );
		$new = ( isset( $_POST[ $meta_name ] ) && ! empty( $_POST[ $meta_name ] ) ) ? $_POST[ $meta_name ] : '';

		if ( $new && $new !== $old ) {
			update_post_meta( $post_id, $meta_name, $new );
		} elseif ( '' === $new && $old ) {
			delete_post_meta( $post_id, $meta_name, $old );
		}
	}

	/**
	 * Save action callback.
	 *
	 * @param  int $post_id Post ID to link meta saved to Post.
	 * @return mixed post_id on failure nothing on success.
	 */
	public function save( $post_id ) {
		return $this->save_meta( $post_id, $this->name, $this->nonce );
	}

	/**
	 * Generate view of the meta box.
	 *
	 * @return void
	 */
	public function show_meta() {

		global $post;
		$meta = get_post_meta( $post->ID, $this->name, true );
		if ( ! empty( $meta ) ) {
			$value = $meta;
		} else {
			$value = null;
		}
		$view_content = '';
		$view_content = apply_filters(
			'refairplugin_renderview_' . $this->type,
			$view_content,
			array(
				'id'          => $this->name,
				'name'        => $this->name,
				'meta_name'   => $this->name,
				'description' => $this->description,
				'options'     => $this->options,
			),
			$value
		);

		$view_content              = $view_content . $this->get_script_launcher( $this->type, $this->name );
		$wrapped_meta_view_content = apply_filters( 'refairplugin_wrapmeta_with_title', $view_content, $this->description );
		echo apply_filters( 'refairplugin_wrapview', $wrapped_meta_view_content, $this->name );
	}

	/**
	 * Add metabox script in inline script tag.
	 *
	 * @param  string $type Type of meta box.
	 * @param  string $name Name identifieing the meta box.
	 * @return string Script tag content.
	 */
	protected function get_script_launcher( $type, $name ) {
		ob_start();
		?>
			<script>
				window.addEventListener("load", function(){
					if (typeof <?php echo esc_attr( $type . $name ); ?>Script === 'function'){<?php echo esc_attr( $type . $name ); ?>Script()};
				});            
			</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add metabox for edit page rendering.
	 *
	 * @return void
	 */
	public function exec() {
		global $post;
		if ( 'page' === $this->post_type ) {

			$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

			if ( is_array( $this->options ) && isset( $this->options['template'] ) ) {
				if ( $page_template === $this->options['template'] ) {
					add_meta_box(
						$this->name, // $id
						$this->description, // $title
						array( $this, 'show_meta' ), // $callback
						$this->post_type, // $screen
						'normal', // $context
						'high' // $priority
					);
				}
			} else {
				add_meta_box(
					$this->name, // $id
					$this->description, // $title
					array( $this, 'show_meta' ), // $callback
					$this->post_type, // $screen
					'normal', // $context
					'high' // $priority
				);
			}
		} else {
			add_meta_box(
				$this->name, // $id
				$this->description, // $title
				array( $this, 'show_meta' ), // $callback
				$this->post_type, // $screen
				'normal', // $context
				'high' // $priority
			);
		}
	}
}