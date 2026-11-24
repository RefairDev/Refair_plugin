<?php
/**
 * The documents generation functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */

use Refairplugin\Refairplugin_Files_Generator_Input;

/**
 * Refair Document manager Class: handle request request documents generation and send them.
 */
class Refairplugin_Documents_Manager {


	/**
	 * PDF sheet generator worker.
	 *
	 * @var Refairplugin_Pdf_Generator_Worker
	 */
	public $pdf_generator_worker;

	/**
	 * Excel sheet generator worker.
	 *
	 * @var Refairplugin_Xls_Generator_Worker
	 */
	public $xls_generator_worker;

	/**
	 * Number of tasks enqueued.
	 *
	 * @var int
	 */
	protected $task_count;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->pdf_generator_worker = new \Refairplugin_Pdf_Generator_Worker();
		$this->xls_generator_worker = new \Refairplugin_Xls_Generator_Worker();
		$this->task_count           = 0;

		add_action( 'wp_ajax_cancel_operation', array( $this, 'cancel_operation' ) );
		add_action( 'wp_ajax_remove_operation', array( $this, 'remove_operation' ) );
		add_action( 'wp_ajax_remove_all_operations', array( $this, 'remove_all_operations' ) );
		add_action( 'wp_ajax_restart_operation', array( $this, 'restart_operation_response' ) );
		add_action( 'wp_ajax_worker_status', array( $this, 'worker_status' ) );
		add_filter( 'cron_schedules', array( $this, 'add_scheduling_frequency' ) );
		add_action( 'watch_adding_tasks_end_cron_hook', array( $this, 'watch_adding_tasks_end_cron' ) );
		add_action( 'generate_material_pdf', array( $this, 'generate_material_pdf_on_save' ), 10, 3 );
		add_action( 'start_generation_pdf', array( $this, 'restart_operation' ), 10, 1 );
		register_deactivation_hook( __FILE__, array( $this, 'unarm_pdf_generation_trigger' ) );
	}

	/**
	 * Check if material file exist of not.
	 *
	 * @param  int $id Id of the material post.
	 * @return boolean Is material file exists
	 */
	public static function is_material_file_exists( $id ) {

		$is_existing = false;
		$status      = true;
		$zip_data    = \Refairplugin\Refairplugin_Utils::refair_build_material_filename_data( $id );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$is_existing = file_exists( $zip_data['path'] . $zip_data['filename'] );
		}

		return $is_existing;
	}

	/**
	 * Check if deposit archive file exist.
	 *
	 * @param  int $id ID of the deposit.
	 * @return boolean true/false if deposit archive file exist.
	 */
	public static function is_deposit_archive_file_exists( $id ) {
		$is_existing = false;
		$status      = true;
		$zip_data    = \Refairplugin\Refairplugin_Utils::refair_build_deposit_archive_filename_data( $id );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$is_existing = file_exists( $zip_data['path'] . $zip_data['filename'] );
		}

		return $is_existing;
	}

	/**
	 * Filter callback to add scheduling frequency to cron schedules.
	 *
	 * @param  array $schedules all schedules for cron functionality.
	 * @return array Schedules array modified.
	 */
	public function add_scheduling_frequency( $schedules ) {
		$schedules['five_seconds'] = array(
			'interval' => 5,
			'display'  => esc_html__( 'Every Five Seconds', 'refair-plugin' ),
		);
		return $schedules;
	}

	/**
	 * Set scheduling for documents generation tasks.
	 *
	 * @return void
	 */
	public function arm_pdf_generation_trigger() {
		if ( ! wp_next_scheduled( 'watch_adding_tasks_end_cron_hook' ) ) {
			wp_schedule_event( time(), 'five_seconds', 'watch_adding_tasks_end_cron_hook' );
		}
	}

	/**
	 * Unset scheduling for documents generation tasks.
	 *
	 * @return void
	 */
	public function unarm_pdf_generation_trigger() {
		$timestamp = wp_next_scheduled( 'watch_adding_tasks_end_cron_hook' );
		wp_unschedule_event( $timestamp, 'watch_adding_tasks_end_cron_hook' );
	}

	/**
	 * Look for scheduling status of documents generation tasks. Dispatch .
	 *
	 * @return void
	 */
	public function watch_adding_tasks_end_cron() {
		$this->write_log( 'watch for pdf generation cron execution' );
		$local_task_count = $this->pdf_generator_worker->get_task_count();

		if ( ( $this->task_count < $local_task_count ) || ( $this->task_count > $local_task_count ) ) {
			$this->task_count = $local_task_count;
			return;
		}

		$this->write_log( 'Do pdf generation cron execution' );
		$this->pdf_generator_worker->dispatch();
		$this->unarm_pdf_generation_trigger();
		$this->task_count = 0;
		$this->write_log( 'End pdf generation cron execution' );
	}

	/**
	 * Get pdf worker status.
	 *
	 * @return string Json encoded worker status.
	 */
	public function worker_status() {
		return json_encode( array( 'processing' => $this->pdf_generator_worker->is_processing() ) );
	}

	/**
	 * Cancel current pdf worker operation.
	 *
	 * @return void
	 */
	public function cancel_operation() {
		$this->pdf_generator_worker->cancel_process();

		wp_die();
	}

	/**
	 * Remove a specific operation identified by its batch id.
	 *
	 * @param  WP_Request $request request containing specific batch key operation.
	 * @return void
	 */
	public function remove_operation( $request ) {

		$key = $_POST['key'];

		$this->pdf_generator_worker->delete( $key );

		wp_die( '', 200 );
	}

	/**
	 * Remove all operations.
	 *
	 * @param  WP_Request $request Ajax call current request object.
	 * @return void
	 */
	public function remove_all_operations( $request ) {

		if ( array_key_exists( 'keys', $_POST ) ) {
			$decoded_keys = json_decode( stripslashes( $_POST['keys'] ) );
			if ( is_array( $decoded_keys ) ) {
				foreach ( $decoded_keys as $key ) {
					$this->pdf_generator_worker->delete( $key );
				}
			}
		}
		wp_die( '', 200 );
	}

	/**
	 * Restart operation executions.
	 *
	 * @param  WP_Request $request Ajax call current request object.
	 * @return void
	 */
	public function restart_operation( $request ) {

		$returned = $this->pdf_generator_worker->dispatch();

		$this->write_log( $returned );
	}

	/**
	 * Manage Restart operation request response.
	 *
	 * @param  WP_Request $request Ajax call current request object.
	 * @return void
	 */
	public function restart_operation_response( $request ) {

		$this->restart_operation( $request );

		wp_die( '', 200 );
	}

	/**
	 * Register route to download material sheets.
	 *
	 * @return void
	 */
	public function register_download_sheet_material() {
		register_rest_route(
			'material',
			'/(?P<id>\d+)/download',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_material_sheet' ),
					'permission_callback' => function () {
						return true;
					},
				),
			),
		);
	}

	/**
	 * Callback function to send material sheet.
	 *
	 * @param  WP_REST_Request $request Rest request.
	 * @return void
	 */
	public function get_material_sheet( $request ) {

		$status   = true;
		$zip_data = \Refairplugin\Refairplugin_Utils::refair_build_material_filename_data( $request['id'] );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$status = $this->refair_send_file( $zip_data['path'], $zip_data['filename'] );
		}
	}

	/**
	 * Get path to the generation template files.
	 *
	 * @param  array   $template_names all templates names.
	 * @param  boolean $load is template have to be loaded.
	 * @param  boolean $rq_once Is template has to be loaded once or each time.
	 * @return string The filesystem path of the directory that contains the plugin.
	 */
	public function refair_get_template_path( $template_names, $load = false, $rq_once = true ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}

			/* search file within the PLUGIN_DIR_PATH only */
			if ( file_exists( plugin_dir_path( __FILE__ ) . $template_name ) ) {
				$located = plugin_dir_path( __FILE__ ) . $template_name;
				break;
			}
		}

		if ( $load && '' !== $located ) {
			load_template( $located, $rq_once );
		}

		return $located;
	}

	/**
	 * Function to generate pdf material sheet.
	 *
	 * @return void
	 */
	public function generate_material_pdf_from_url() {

		if ( ( isset( $_GET['print'] ) ) && ( 'product' === get_post_type( intval( $_GET['print'] ) ) ) ) {
			$product_id = $_GET['print'];

			$args = array(
				'id'                 => $product_id,
				'destination'        => 'I',
				'public_type_name'   => __( 'Material', 'refair-plugin' ),
				'folder_name'        => 'materials',
				'template_part_name' => 'materials',
			);

			$inputs = new Refairplugin_Files_Generator_Input( $args );
			( new Refairplugin_Pdf_Generator( $inputs ) )->generate_pdf();
			exit;
		}
	}

	/**
	 * Pdf generation of the currently saved product.
	 *
	 * @param  int     $post_id id of the currently saved post.
	 * @param  WP_Post $post object of the currently saved post.
	 * @param  boolean $update if saving is an update or not.
	 * @return void
	 */
	public function generate_material_pdf_on_save( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ( isset( $post_id ) )
		&& false !== get_post_status( intval( $post_id ) )
		&& 'auto-draft' !== get_post_status( intval( $post_id ) )
		&& ( ( 'product' === get_post_type( intval( $post_id ) ) ) ||
		( 'product_variation' === get_post_type( intval( $post_id ) ) )
		) ) {
			$args = array(
				'id'                 => $post_id,
				'destination'        => 'F',
				'public_type_name'   => __( 'Material', 'refair-plugin' ),
				'folder_name'        => 'materials',
				'template_part_name' => 'material',
				'reference'          => wc_get_product( $post_id )->get_sku(),
				'edit_date'          => get_the_modified_date( '', $post_id ),
			);

			$this->pdf_generator_worker->push_to_queue( json_encode( $args ) )->save()->clear_queue();
			$this->arm_pdf_generation_trigger();

		}
	}

	/**
	 * Pdf generation of the currently saved deposit.
	 *
	 * @param  int     $post_id id of the currently saved deposit.
	 * @param  WP_Post $post object of the currently saved deposit.
	 * @param  boolean $update if saving is an update or not.
	 * @return void
	 */
	public function generate_deposit_pdf_on_save( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$this->generate_deposit_pdf( $post_id );
	}

	/**
	 * Pdf generation of a deposit.
	 *
	 * @param  int $post_id id of the currently saved deposit.
	 * @return void
	 */
	protected function generate_deposit_pdf( $post_id, $complete_callback = null ) {
		if ( ( isset( $post_id ) )
		&& false !== get_post_status( intval( $post_id ) )
		&& 'auto-draft' !== get_post_status( intval( $post_id ) )
		&& ( 'deposit' === get_post_type( intval( $post_id ) ) ) ) {

			$args = array(
				'id'                 => $post_id,
				'destination'        => 'F',
				'public_type_name'   => __( 'Deposit', 'refair-plugin' ),
				'folder_name'        => 'deposits',
				'template_part_name' => 'deposit',
				'orientation'        => 'L',
				'complete_callback'  => $complete_callback,
			);

			$ref = get_post_meta( $post_id, 'reference', true );

			if ( false !== $ref ) {
				$args['reference'] = $ref;

				$this->pdf_generator_worker->push_to_queue( json_encode( $args ) )->save()->clear_queue();
				$this->arm_pdf_generation_trigger();
			}
		}
	}

	/**
	 * Pdf deletion of the currently deleted product.
	 *
	 * @param  int     $post_id id of the currently removed product.
	 * @param  WP_Post $post object of the currently removed product.
	 * @return void
	 */
	public function remove_pdf_on_delete( $post_id, $post ) {
		if ( ( isset( $post_id ) )
		&& ( 'product' === get_post_type( intval( $post_id ) )
		|| 'product_variation' === get_post_type( intval( $post_id ) ) ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$path = wp_upload_dir()['basedir'] . '/materials/' . strval( $post_id ) . '/';
			if ( file_exists( $path ) ) {
				$files = list_files( $path );
				foreach ( $files as $file ) {
					wp_delete_file( $file );
				}
				rmdir( $path );
			}
		}
	}

	/**
	 * Add a custom action to order actions select box on edit order page
	 * Only added for paid orders that haven't fired this action yet
	 *
	 * @param  array $actions order actions array to display.
	 * @return array updated actions
	 */
	public function refair_add_order_meta_box_action( $actions ) {

		$actions['wc_get_materials_sheets_order_action'] = __( 'Download materials sheets', 'refair-plugin' );
		return $actions;
	}

	/**
	 * Create filename and paths for expression of interest zip archive generation.
	 *
	 * @param  int $id Id of the expression of interest whom archive is to zip.
	 * @return array Array of paths and filename informations.
	 */
	protected function refair_build_expression_of_interest_archive_filename_data( $id ) {

		$folder = '/expressions_of_interests/';

		/* make EoI directory */
		$path = str_replace( '\\', '/', wp_upload_dir()['basedir'] . $folder );
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
			chmod( $path, 0774 );
		}
		$root_url = wp_upload_dir()['baseurl'] . $folder;

		/* build filename */
		$zip_filename = __( 'Expression_of_interest', 'refair-plugin' ) . '_-_' . strval( $id ) . '.zip';

		return array(
			'path'        => $path,
			'filename'    => $zip_filename,
			'full_path'   => $path . $zip_filename,
			'full_url'    => $root_url . $zip_filename,
			'orientation' => 'L',
		);
	}


	/**
	 * Zip files provided in parameters.
	 *
	 * @param  array $zip_filename_data data used to zip files.
	 * @param  array $files_to_zip list of filespath to zip.
	 * @param  bool  $strict Throw on incomplete archives instead of using legacy error handling.
	 * @return boolean Returned status true on success false on error.
	 */
	public static function refair_zip_files( $zip_filename_data, $files_to_zip, $strict = false ) {

		if ( $strict ) {
			$zip = new ZipArchive();
			if ( true !== $zip->open( $zip_filename_data['full_path'], ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
				throw new RuntimeException( __( 'Impossible de créer le ZIP individuel.', 'refair-plugin' ) );
			}
			try {
				foreach ( $files_to_zip as $file ) {
					if ( ! is_readable( $file['path_name'] ) || ! $zip->addFile( $file['path_name'], $file['filename'] ) ) {
						throw new RuntimeException( __( 'Fichier absent ou illisible : ', 'refair-plugin' ) . $file['filename'] );
					}
				}
			} catch ( Throwable $error ) {
				$zip->close();
				wp_delete_file( $zip_filename_data['full_path'] );
				throw $error;
			}
			if ( ! $zip->close() ) {
				wp_delete_file( $zip_filename_data['full_path'] );
				throw new RuntimeException( __( 'Impossible de finaliser le ZIP individuel.', 'refair-plugin' ) );
			}
			return true;
		}

		$zip    = new ZipArchive();
		$status = true;
		$status = $zip->open( $zip_filename_data['full_path'], ZipArchive::CREATE | ZipArchive::OVERWRITE );

		self::write_log( 'Zipfile: ' . $zip_filename_data['full_path'] );

		if ( DIRECTORY_SEPARATOR === '/' ) {
			$from = '\\';
		}
		if ( DIRECTORY_SEPARATOR === '\\' ) {
			$from = '/';
		}
		$to = DIRECTORY_SEPARATOR;

		if ( true === $status ) {
			try {
				foreach ( $files_to_zip as $file ) {
					$status    = false;
					$full_path = str_replace( $from, $to, $file['path_name'] );

					if ( file_exists( $full_path ) ) {
						$status = $zip->addFile( str_replace( $from, $to, $file['path_name'] ), $file['filename'] );
						if ( false === $status ) {
							self::write_log( 'Zipfile: ERROR on add ' . $file['filename'] );
						}
					} else {
						self::write_log( "File to zip don't exist " . $full_path );
					}
				}
			} catch ( Throwable $t ) {
				$msg = $t->getMessage();
			}
			if ( false === $status ) {
				self::write_log( 'Zipfile: zip file erroneous before close' );
			}
			$status = $zip->close();

		} else {
			self::write_log( 'Error on open/create global zip file ' . $zip_filename_data['full_path'] );
		}

		return $status;
	}

	/**
	 * Build archive of specific expression of interest accordinf to order parameter.
	 *
	 * @param  WC_Order $order Expression of interest object.
	 * @param  bool     $strict Reject missing material sheets and ZIP failures.
	 * @return mixed return zip filename on success or false on error.
	 */
	protected function refair_build_expression_of_interest_archive( $order, $strict = false ) {

		$zip_filename_data = array(
			'path'      => '',
			'filename'  => '',
			'full_path' => '',
		);
		$files_to_zip      = array();

		$args = array(
			'id'                 => $order->get_id(),
			'title'              => $order->get_id(),
			'destination'        => 'F',
			'public_type_name'   => __( 'Expression_of_Interest', 'refair-plugin' ),
			'folder_name'        => 'expression_of_interest',
			'template_part_name' => 'order',
			'orientation'        => 'L',
		);

		$inputs_pdf          = new Refairplugin_Files_Generator_Input( $args );
		$order_filename_data = ( new Refairplugin_Pdf_Generator( $inputs_pdf ) )->generate_pdf();

		$files_to_zip [] = array(
			'path_name' => $order_filename_data['full_path'],
			'filename'  => $order_filename_data['filename'],
		);

		$xls_args = array(
			'id'                 => $order->get_id(),
			'title'              => $order->get_id(),
			'public_type_name'   => __( 'Expression_of_Interest', 'refair-plugin' ),
			'template_part_name' => 'order',
			'folder_name'        => 'expression_of_interest',
		);

		$inputs_xls              = new Refairplugin_Files_Generator_Input( $xls_args );
		$xls_order_filename_data = ( new Refairplugin_Xls_Generator( $inputs_xls ) )->generate_xls();

		$files_to_zip [] = array(
			'path_name' => $xls_order_filename_data['full_path'],
			'filename'  => $xls_order_filename_data['filename'],
		);

		/* get materials order */
		$materials = $order->get_items();
		/* get path */

		foreach ( $materials as $material ) {
			$m_id                   = $material->get_product_id();
			$material_filename_data = \Refairplugin\Refairplugin_Utils::refair_build_material_filename_data( $m_id );

			if ( false === $material_filename_data ) {
				error_log( 'Refairplugin_Documents_Manager::refair_build_expression_of_interest_archive: skipped item with invalid product id ' . $m_id . ' for order ' . $order->get_id() );
				if ( $strict ) {
					throw new RuntimeException( __( 'Produit introuvable : ', 'refair-plugin' ) . $m_id );
				}
				continue;
			}

			$files_to_zip [] = array(
				'path_name' => $material_filename_data['full_path'],
				'filename'  => $material_filename_data['filename'],
			);
		}

		$zip_filename_data = $this->refair_build_expression_of_interest_archive_filename_data( $order->get_id() );

		$status = $this->refair_zip_files( $zip_filename_data, $files_to_zip, $strict );

		if ( ! $status ) {
			return $status;
		}
		return $zip_filename_data;
	}

	/**
	 * Start or advance an authenticated export. Only server-side state contains paths.
	 */
	public function refair_export_orders() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'refair-plugin' ) ), 403 );
		}
		check_ajax_referer( 'refair_export_orders', 'nonce' );

		$token = isset( $_POST['token'] ) && is_string( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$lock  = false;
		$zip   = null;
		try {
			if ( '' === $token ) {
				if ( ! class_exists( 'ZipArchive' ) ) {
					throw new RuntimeException( __( 'L’extension PHP ZIP est indisponible.', 'refair-plugin' ) );
				}
				$statuses = array_diff( array_keys( wc_get_order_statuses() ), array( 'wc-checkout-draft', 'wc-auto-draft', 'wc-trash' ) );
				$ids = wc_get_orders(
					array(
						'type'    => 'shop_order',
						'status'  => array_values( $statuses ),
						'limit'   => -1,
						'return'  => 'ids',
						'orderby' => 'ID',
						'order'   => 'ASC',
					)
				);
				if ( empty( $ids ) ) {
					wp_send_json_success( array( 'empty' => true, 'message' => __( 'Aucune manifestation d’intérêt à exporter.', 'refair-plugin' ) ) );
				}
				$token = wp_generate_password( 32, false, false );
				$path  = trailingslashit( sys_get_temp_dir() ) . 'refair-export-' . $token;
				$real_temp = wp_normalize_path( realpath( sys_get_temp_dir() ) );
				$web_roots = array( ABSPATH, WP_CONTENT_DIR );
				if ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
					$web_roots[] = $_SERVER['DOCUMENT_ROOT'];
				}
				foreach ( $web_roots as $web_root ) {
					$resolved = realpath( $web_root );
					if ( false === $resolved ) {
						throw new RuntimeException( __( 'Impossible de vérifier les dossiers Web.', 'refair-plugin' ) );
					}
					$real_root = trailingslashit( wp_normalize_path( $resolved ) );
					if ( 0 === stripos( trailingslashit( $real_temp ), $real_root ) ) {
						throw new RuntimeException( __( 'Le dossier temporaire PHP doit être hors des dossiers Web.', 'refair-plugin' ) );
					}
				}
				if ( ! mkdir( $path, 0700 ) ) {
					throw new RuntimeException( __( 'Impossible de créer le dossier temporaire.', 'refair-plugin' ) );
				}
				$state = array(
					'owner'    => get_current_user_id(),
					'path'     => $path,
					'ids'      => array_values( $ids ),
					'position' => 0,
					'included' => 0,
					'errors'   => array(),
					'ready'    => false,
					'expires'  => time() + DAY_IN_SECONDS,
				);
				if ( ! add_option( 'refair_export_' . $token, $state, '', false ) ) {
					rmdir( $path );
					throw new RuntimeException( __( 'Impossible d’enregistrer l’export.', 'refair-plugin' ) );
				}
				$scheduled = wp_schedule_single_event( $state['expires'], 'refair_cleanup_orders_export', array( $token ), true );
				if ( is_wp_error( $scheduled ) || false === $scheduled ) {
					throw new RuntimeException( __( 'Impossible de planifier le nettoyage de l’export.', 'refair-plugin' ) );
				}
			}
			$state = $this->refair_get_export_state( $token );
			$lock  = $this->refair_lock_export( $state );
			wp_cache_delete( 'refair_export_' . $token, 'options' );
			$state = $this->refair_get_export_state( $token );
			if ( ! $state['ready'] ) {
				$zip = new ZipArchive();
				if ( true !== $zip->open( $state['path'] . '/orders.zip', ZipArchive::CREATE ) ) {
					$zip = null;
					throw new RuntimeException( __( 'Impossible d’ouvrir le ZIP global.', 'refair-plugin' ) );
				}
				$processed = 0;
				$generated = false;
				while ( $state['position'] < count( $state['ids'] ) && $processed < 10 && ! $generated ) {
					$id = $state['ids'][ $state['position'] ];
					try {
						$data = $this->refair_build_expression_of_interest_archive_filename_data( $id );
						if ( ! file_exists( $data['full_path'] ) ) {
							$generated = true;
							$order = wc_get_order( $id );
							if ( ! $order ) {
								throw new RuntimeException( __( 'Commande introuvable.', 'refair-plugin' ) );
							}
							$data = $this->refair_build_expression_of_interest_archive( $order, true );
							if ( false === $data ) {
								throw new RuntimeException( __( 'Échec de génération du ZIP individuel.', 'refair-plugin' ) );
							}
						}
						$individual = new ZipArchive();
						if ( ! is_readable( $data['full_path'] ) || true !== $individual->open( $data['full_path'], ZipArchive::CHECKCONS ) ) {
							throw new RuntimeException( __( 'ZIP individuel illisible ou invalide.', 'refair-plugin' ) );
						}
						$individual->close();
					} catch ( Throwable $error ) {
						$state['errors'][] = 'Commande ' . $id . ' : ' . $error->getMessage();
						error_log( 'REFAIR export: ' . end( $state['errors'] ) );
						++$state['position'];
						++$processed;
						continue;
					}
					if ( ! $zip->addFile( $data['full_path'], $data['filename'] ) ) {
						throw new RuntimeException( __( 'Impossible d’ajouter une archive au ZIP global.', 'refair-plugin' ) );
					}
					if ( ! $zip->setCompressionName( $data['filename'], ZipArchive::CM_STORE ) ) {
						throw new RuntimeException( __( 'Impossible de configurer le ZIP global.', 'refair-plugin' ) );
					}
					++$state['included'];
					++$state['position'];
					++$processed;
				}
				$state['ready'] = $state['position'] === count( $state['ids'] );
				$report = sprintf( "Commandes : %d\nArchives incluses : %d\nEchecs : %d\n\n", count( $state['ids'] ), $state['included'], count( $state['errors'] ) );
				$report .= implode( "\n", $state['errors'] );
				if ( ! $zip->addFromString( 'rapport-export.txt', $report ) ) {
					throw new RuntimeException( __( 'Impossible d’ajouter le rapport au ZIP global.', 'refair-plugin' ) );
				}
				$closing_zip = $zip;
				$zip = null;
				$closed = $closing_zip->close();
				if ( ! $closed ) {
					throw new RuntimeException( __( 'Impossible de finaliser le ZIP global (vérifiez l’espace disque).', 'refair-plugin' ) );
				}
				if ( ! update_option( 'refair_export_' . $token, $state, false ) ) {
					throw new RuntimeException( __( 'Impossible de sauvegarder la progression.', 'refair-plugin' ) );
				}
			}
			flock( $lock, LOCK_UN );
			fclose( $lock );
			$lock = false;
			wp_send_json_success(
				array(
					'token'     => $token,
					'processed' => $state['position'],
					'total'     => count( $state['ids'] ),
					'included'  => $state['included'],
					'failed'    => count( $state['errors'] ),
					'ready'     => $state['ready'],
					'download'  => $state['ready'] ? wp_nonce_url( add_query_arg( array( 'action' => 'refair_download_orders_export', 'token' => $token ), admin_url( 'admin-post.php' ) ), 'refair_download_orders_' . $token ) : '',
				)
			);
		} catch ( Throwable $error ) {
			if ( $zip instanceof ZipArchive ) {
				$zip->close();
			}
			if ( is_resource( $lock ) ) {
				flock( $lock, LOCK_UN );
				fclose( $lock );
				$this->refair_cleanup_orders_export( $token );
			} elseif ( isset( $scheduled ) && ( is_wp_error( $scheduled ) || false === $scheduled ) ) {
				$this->refair_cleanup_orders_export( $token );
			}
			error_log( 'REFAIR export: ' . $error->getMessage() );
			wp_send_json_error( array( 'message' => $error->getMessage() ), 500 );
		}
	}

	/** Validate ownership before opening any export file. */
	protected function refair_get_export_state( $token ) {
		if ( ! preg_match( '/^[a-zA-Z0-9]{32}$/D', $token ) ) {
			throw new RuntimeException( __( 'Identifiant d’export invalide.', 'refair-plugin' ) );
		}
		$state = get_option( 'refair_export_' . $token );
		if ( ! is_array( $state ) || get_current_user_id() !== $state['owner'] || time() >= $state['expires'] ) {
			throw new RuntimeException( __( 'Export indisponible ou expiré.', 'refair-plugin' ) );
		}
		return $state;
	}

	/** A file lock also protects against duplicate browser requests. */
	protected function refair_lock_export( $state ) {
		$lock = fopen( $state['path'] . '/export.lock', 'c' );
		if ( false === $lock ) {
			throw new RuntimeException( __( 'Impossible de verrouiller l’export.', 'refair-plugin' ) );
		}
		if ( ! flock( $lock, LOCK_EX | LOCK_NB ) ) {
			fclose( $lock );
			throw new RuntimeException( __( 'Cet export est déjà en cours de traitement.', 'refair-plugin' ) );
		}
		return $lock;
	}

	/** Stream only completed exports through authenticated administration. */
	public function refair_download_orders_export() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'refair-plugin' ), '', array( 'response' => 403 ) );
		}
		$token = isset( $_GET['token'] ) && is_string( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		check_admin_referer( 'refair_download_orders_' . $token );
		$lock = false;
		try {
			$state = $this->refair_get_export_state( $token );
			$lock = $this->refair_lock_export( $state );
			wp_cache_delete( 'refair_export_' . $token, 'options' );
			$state = $this->refair_get_export_state( $token );
			if ( ! $state['ready'] ) {
				throw new RuntimeException( __( 'L’export n’est pas terminé.', 'refair-plugin' ) );
			}
			$handle = fopen( $state['path'] . '/orders.zip', 'rb' );
			if ( false === $handle ) {
				throw new RuntimeException( __( 'Le ZIP global est indisponible.', 'refair-plugin' ) );
			}
			while ( ob_get_level() > 0 ) {
				if ( ! ob_end_clean() ) {
					fclose( $handle );
					throw new RuntimeException( __( 'Impossible de préparer le téléchargement.', 'refair-plugin' ) );
				}
			}
			nocache_headers();
			header( 'Content-Type: application/zip' );
			header( 'Content-Disposition: attachment; filename="manifestations-interet.zip"' );
			header( 'Content-Length: ' . filesize( $state['path'] . '/orders.zip' ) );
			$result = fpassthru( $handle );
			fclose( $handle );
			flock( $lock, LOCK_UN );
			fclose( $lock );
			if ( false === $result ) {
				error_log( 'REFAIR export: echec de transmission du ZIP global.' );
			}
			// Keep the export until expiry so an interrupted download can be retried.
			exit;
		} catch ( Throwable $error ) {
			if ( is_resource( $lock ) ) {
				flock( $lock, LOCK_UN );
				fclose( $lock );
			}
			error_log( 'REFAIR export download: ' . $error->getMessage() );
			wp_die( esc_html( $error->getMessage() ), '', array( 'response' => 400 ) );
		}
	}

	/** Delete only files created by this export, never individual order archives. */
	public function refair_cleanup_orders_export( $token ) {
		if ( ! is_string( $token ) || ! preg_match( '/^[a-zA-Z0-9]{32}$/D', $token ) ) {
			error_log( 'REFAIR export: identifiant de nettoyage invalide.' );
			return;
		}
		$state = get_option( 'refair_export_' . $token );
		if ( ! is_array( $state ) ) {
			return;
		}
		if ( ! is_dir( $state['path'] ) ) {
			delete_option( 'refair_export_' . $token );
			wp_clear_scheduled_hook( 'refair_cleanup_orders_export', array( $token ) );
			return;
		}
		try {
			$lock = $this->refair_lock_export( $state );
		} catch ( RuntimeException $error ) {
			error_log( 'REFAIR export cleanup: ' . $error->getMessage() );
			wp_schedule_single_event( time() + 300, 'refair_cleanup_orders_export', array( $token ) );
			return;
		}
		if ( file_exists( $state['path'] . '/orders.zip' ) ) {
			wp_delete_file( $state['path'] . '/orders.zip' );
		}
		flock( $lock, LOCK_UN );
		fclose( $lock );
		wp_delete_file( $state['path'] . '/export.lock' );
		if ( ! rmdir( $state['path'] ) ) {
			error_log( 'REFAIR export: nettoyage du dossier temporaire impossible.' );
			wp_schedule_single_event( time() + 300, 'refair_cleanup_orders_export', array( $token ) );
			return;
		}
		delete_option( 'refair_export_' . $token );
		wp_clear_scheduled_hook( 'refair_cleanup_orders_export', array( $token ) );
	}

	/**
	 * Send file to user.
	 *
	 * @param  string $file_path Path to the file.
	 * @param  string $filename Name of the file.
	 * @return boolean false on error otherwise nothing because execution is exited.
	 */
	protected function refair_send_file( $file_path, $filename ) {

		if ( empty( $file_path ) || empty( $filename ) || ! file_exists( $file_path . $filename ) ) {
			wp_die( "Le fichier {$filename} n'a pu être trouvé sur le site. Merci de le regénérer." );
		}

		header( 'Cache-control: private' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: filename=' . $filename );

		$handle = fopen( $file_path . $filename, 'rb' );
		if ( false === $handle ) {
			return false;
		}
		while ( ! feof( $handle ) ) {
			$buffer = @fread( $handle, 1024 * 8 );
			echo $buffer;
			ob_flush();
			flush();
		}
		ob_end_flush();
		fclose( $handle );
		exit();
	}

	/**
	 * Add an order note when custom action is clicked
	 * Add a flag on the order to show it's been run
	 *
	 * @param \WC_Order $order Targeted by the action.
	 */
	public function refair_process_order_meta_box_action( $order ) {

		$status   = true;
		$zip_data = $this->refair_build_expression_of_interest_archive( $order );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$status = $this->refair_send_file( $zip_data['path'], $zip_data['filename'] );
			unlink( $zip_data['path'] . $zip_data['filename'] );
		}
	}

	/**
	 * Add download archive action to order list actions dropdown of orders.
	 *
	 * @param  array    $actions Actions list.
	 * @param  WC_Order $order Expression of interest object.
	 * @return array Actions modified with download action.
	 */
	public function refair_add_download_expression_of_interest_action( $actions, $order ) {

		$o_id                = $order->get_id();
		$actions['download'] = array(
			'url'  => get_rest_url( null, "wc/v3/orders/{$o_id}/download" ),
			'name' => __( 'Download', 'refair-plugin' ),
		);
		return $actions;
	}

	/**
	 * Register route to download order archive (containing order xls and pdf materials sheet).
	 *
	 * @return void
	 */
	public function register_download_archive_order() {
		register_rest_route(
			'wc/v3',
			'/orders/(?P<order_id>\d+)/download',
			array(
				'args' => array(
					'order_id' => array(
						'description' => __( 'Expression of interest ID.', 'refair-plugin' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_order_archive' ),
					'permission_callback' => function () {
						return true;
					},
				),
			)
		);
	}

	/**
	 * Get Order archive with zip format.
	 *
	 * @param  WP_Request $request Rest request from user.
	 * @return void
	 */
	public function get_order_archive( $request ) {

		$order = wc_get_order( (int) $request['order_id'] );

		$status   = true;
		$zip_data = $this->refair_build_expression_of_interest_archive( $order );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$status = $this->refair_send_file( $zip_data['path'], $zip_data['filename'] );
		}
	}

	/**
	 * Register download archive of deposit route.
	 *
	 * @return void
	 */
	public function register_download_deposit_archive() {
		register_rest_route(
			'deposit',
			'/(?P<deposit_id>\d+)/download',
			array(
				'args' => array(
					'deposit_id' => array(
						'description' => __( 'The deposit ID.', 'refair-plugin' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_deposit_archive' ),
					'permission_callback' => function () {
						return true;
					},
				),
			),
		);
	}

	/**
	 * Register route to download orders of a deposit.
	 *
	 * @return void
	 */
	public function register_download_deposit_orders_archive() {
		register_rest_route(
			'deposit',
			'/(?P<deposit_id>\d+)/orders',
			array(
				'args' => array(
					'deposit_id' => array(
						'description' => __( 'The deposit ID.', 'refair-plugin' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'refair_get_orders_deposit_archive' ),
					'permission_callback' => function () {
						return true;
					},
				),
			),
		);
	}

	/**
	 * Get archive of requested deposit.
	 *
	 * @param  WP_Request $request Request containing id of deposit.
	 * @return void
	 */
	public function get_deposit_archive( $request ) {

		$status = true;

		$deposits = get_posts(
			array(
				'p'           => (int) $request['deposit_id'],
				'post_type'   => 'deposit',
				'post_status' => array( 'publish', 'private', 'draft', 'future', 'pending' ),
			)
		);

		if ( is_array( $deposits ) && count( $deposits ) > 0 && property_exists( $deposits[0], 'ID' ) ) {
			$zip_data = $this->refair_build_deposit_archive( $deposits[0] );

			if ( false === $zip_data ) {
				$status = false;
			}

			if ( true === $status ) {
				$status = $this->refair_send_file( $zip_data['path'], $zip_data['filename'] );
			}
		}
	}

		/**
		 * Build archive of deposit.
		 *
		 * @param  WP_Post $deposit deposit post data.
		 * @return mixed zip data on success or false on failure.
		 */
	public static function refair_build_deposit_archive_by_id( $deposit_id ) {

		$deposit_obj = get_post( (int) $deposit_id );
		return self::refair_build_deposit_archive( $deposit_obj );
	}

	/**
	 * Build archive of deposit.
	 *
	 * @param  WP_Post $deposit deposit post data.
	 * @return mixed zip data on success or false on failure.
	 */
	public static function refair_build_deposit_archive( $deposit ) {

		$files_to_zip      = array();
		$zip_filename_data = array(
			'path'      => '',
			'filename'  => '',
			'full_path' => '',
		);

		$args = array(
			'id'                 => $deposit->ID,
			'title'              => sanitize_file_name( get_the_title( $deposit->ID ) ),
			'destination'        => 'F',
			'public_type_name'   => __( 'Deposit', 'refair-plugin' ),
			'folder_name'        => 'deposits',
			'template_part_name' => 'deposit',
		);

		$ref = get_post_meta( $deposit->ID, 'reference', true );

		if ( false !== $ref ) {
			$args['reference'] = $ref;
		}

		$inputs = new \Refairplugin\Refairplugin_Files_Generator_Input( $args );

		$files_to_zip [] = array(
			'path_name' => $inputs->get_file_directory() . $inputs->get_pdf_filename(),
			'filename'  => $inputs->get_pdf_filename(),
		);

		/* get materials order */
		$materials = get_posts(
			array(
				'post_type'   => 'product',
				'meta_key'    => 'deposit',
				'meta_value'  => get_post_meta( $deposit->ID, 'reference', true ),
				'numberposts' => -1,
			)
		);
		/* get path */

		foreach ( $materials as $material ) {
			$m_id                   = $material->ID;
			$material_filename_data = \Refairplugin\Refairplugin_Utils::refair_build_material_filename_data( $m_id );
			$files_to_zip []        = array(
				'path_name' => $material_filename_data['full_path'],
				'filename'  => $material_filename_data['filename'],
			);

		}

		$zip_filename_data = \Refairplugin\Refairplugin_Utils::refair_build_deposit_archive_filename_data( $deposit->ID );

		$status = self::refair_zip_files( $zip_filename_data, $files_to_zip );

		if ( ! $status ) {
			return $status;
		}
		return $zip_filename_data;
	}

	/**
	 * Getand send archive containing orders of a deposit.
	 *
	 * @param  WP_Request $request Request containing deposit id.
	 * @return void
	 */
	public function refair_get_orders_deposit_archive( $request ) {
		$status = true;

		$deposits = get_posts(
			array(
				'p'           => (int) $request['deposit_id'],
				'post_type'   => 'deposit',
				'post_status' => array( 'publish', 'private', 'draft', 'future', 'pending' ),
			)
		);

		if ( is_array( $deposits ) && count( $deposits ) > 0 && property_exists( $deposits[0], 'ID' ) ) {

			$zip_data = $this->build_orders_deposit_archive( $deposits[0] );
			if ( false === $zip_data ) {
				$status = false;

			}
			if ( true === $status ) {
				$status = $this->refair_send_file( $zip_data['path'], $zip_data['filename'] );
			}
		}
	}

	/**
	 * Build zip file containing all orders of a deposit.
	 *
	 * @param  WP_Post $deposit Deposit post object.
	 * @return mixed Zip files data on sucess or false on failure.
	 */
	public function build_orders_deposit_archive( $deposit ) {
		$files_to_zip      = array();
		$zip_filename_data = array(
			'path'      => '',
			'filename'  => '',
			'full_path' => '',
		);

		$xls_args = array(
			'id'                 => $deposit->ID,
			'title'              => sanitize_file_name( get_the_title( $deposit->ID ) ),
			'public_type_name'   => __( 'Deposit', 'refair-plugin' ),
			'folder_name'        => 'deposits_orders',
			'template_part_name' => 'deposit_orders',
		);

		$inputs_xls              = new Refairplugin_Files_Generator_Input( $xls_args );
		$xls_order_filename_data = ( new Refairplugin_Xls_Generator( $inputs_xls ) )->generate_xls();

		$files_to_zip [] = array(
			'path_name' => $xls_order_filename_data['full_path'],
			'filename'  => $xls_order_filename_data['filename'],
		);

		$zip_filename_data = \Refairplugin\Refairplugin_Utils::refair_build_deposit_archive_filename_data( $deposit->ID );

		$status = $this->refair_zip_files( $zip_filename_data, $files_to_zip );

		if ( ! $status ) {
			return $status;
		}
		return $zip_filename_data;
	}


	/**
	 * Regenerate pdf sheet of listed materials.
	 *
	 * @param  array $listed_products List of products to regenerate.
	 * @return int count of files regenerated.
	 */
	protected function refair_regenerate_list_products_pdf( $listed_products ) {

		foreach ( $listed_products as $listed_product ) {
			$this->generate_material_pdf_on_save( $listed_product->ID, $listed_product, false );
		}

		$returned = 0;
		if ( is_array( $listed_products ) ) {
			$returned = count( $listed_products );
		}
		return $returned;
	}

	/**
	 * Regenerate all materials pdf sheet.
	 *
	 * @return int Count of files regenerated.
	 */
	public function refair_regenerate_products_pdf_exec() {

		$listed_products = get_posts(
			array(
				'post_type'   => 'product',
				'post_statut' => 'publish',
				'numberposts' => -1,
			)
		);

		return $this->refair_regenerate_list_products_pdf( $listed_products );
	}

	/**
	 * Handle bulk regenerate pdf action ( edit page list ).
	 *
	 * @param  string $redirect_url Redirection after handling.
	 * @param  string $action Action requested.
	 * @param  array  $post_ids Post listed by Id targeted by the action.
	 * @return string Redirection url.
	 */
	public function handle_product_regenerate_pdf_bulk_action( $redirect_url, $action, $post_ids ) {
		if ( 'regenerate-pdf' === $action ) {

			$listed_products = get_posts(
				array(
					'post_type'   => 'product',
					'post__in'    => $post_ids,
					'numberposts' => -1,
				)
			);
			$this->refair_regenerate_list_products_pdf( $listed_products );

			$redirect_url = add_query_arg( 'changed-to-published', count( $post_ids ), $redirect_url );
		}
		return $redirect_url;
	}

	/**
	 * Regenerate archive of listed deposits.
	 *
	 * @param  array $listed_deposits List of products to regenerate.
	 */
	private function refair_regenerate_list_deposit_archive( $listed_deposits ) {
		foreach ( $listed_deposits as $listed_deposit ) {
			$this->generate_deposit_pdf( $listed_deposit->ID, array( 'Refairplugin_Documents_Manager', 'refair_build_deposit_archive_by_id' ) );

		}
	}

	/**
	 * Handle bulk regenerate archive action ( edit page list ).
	 *
	 * @param  string $redirect_url Redirection after handling.
	 * @param  string $action Action requested.
	 * @param  array  $post_ids Post listed by Id targeted by the action.
	 * @return string Redirection url.
	 */
	public function handle_deposit_regenerate_pdf_bulk_action( $redirect_url, $action, $post_ids ) {
		if ( 'regenerate-archive' === $action ) {

			$listed_archives = get_posts(
				array(
					'post_type'   => 'deposit',
					'post__in'    => $post_ids,
					'numberposts' => -1,
				)
			);
			$this->refair_regenerate_list_deposit_archive( $listed_archives );

			$redirect_url = add_query_arg( 'changed-to-published', count( $post_ids ), $redirect_url );
		}
		return $redirect_url;
	}
	/**
	 * Write log for debug.
	 *
	 * @param  string $log message to log.
	 * @return void
	 */
	public static function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
