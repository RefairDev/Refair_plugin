<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 */

use Refairplugin\Refairplugin_Term_Meta_Edit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */
class Refairplugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Refairplugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used as public name identifying this plugin.
	 */
	protected $plugin_name;

	/**
	 * The fancy name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_id    The string used to uniquely identify this plugin.
	 */
	protected $plugin_id;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'REFAIRPLUGIN_VERSION' ) ) {
			$this->version = REFAIRPLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_id = 'refairplugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_common_hooks();
		$this->plugin_name = 'REFAIR Plugin';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Refairplugin_Loader. Orchestrates the hooks of the plugin.
	 * - Refairplugin_I18n. Defines internationalization functionality.
	 * - Refairplugin_Admin. Defines all hooks for the admin area.
	 * - Refairplugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once __DIR__ . '/../vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-refairplugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-refairplugin-i18n.php';

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-refairplugin-term-meta-edit.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-view-utils.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-meta-parameters.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-meta.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-meta-view.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-settings.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-setting-view.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-metas-factory.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-ui-customization.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-refairplugin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-public.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-documents-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-pdf-generator-worker.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-pdf-generator.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-xls-generator-worker.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-refairplugin-xls-generator.php';

		require_once plugin_dir_path( __DIR__ ) . 'public/partials/class-refairplugin-xls-template-order.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/class-refairplugin-xls-template-deposit.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/class-refairplugin-xls-template-deposit-orders.php';

		/**
		 * The class responsible for defining woocommerce products
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-refairplugin-files-generator-input.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-refairplugin-utils.php';

		$this->loader = new Refairplugin_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Refairplugin_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Refairplugin_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin    = new Refairplugin_Admin( $this->get_plugin_id(), $this->get_version() );
		$plugin_settings = new Refairplugin_Settings( $this->get_plugin_id(), $this->get_version() );
		$plugin_admin_ui = new Refairplugin_UI_Customization( $this->get_plugin_id(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_settings_page' );
		$this->loader->add_action( 'woocommerce_product_options_advanced', $plugin_admin, 'add_additionnals_fields' );
		$this->loader->add_action( 'woocommerce_product_options_sku', $plugin_admin, 'add_deposit_fields' );
		$this->loader->add_action( 'woocommerce_product_options_stock_fields', $plugin_admin, 'add_stock_unit_field' );
		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'add_variation_fields', 10, 3 );
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'add_pemd_product_tab', 10, 1 );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'set_pemd_product_tab_data' );
		$this->loader->add_action( 'save_post_product', $plugin_admin, 'save_additionnals_fields', 10, 3 );
		$this->loader->add_filter( 'manage_product_posts_columns', $plugin_admin_ui, 'set_columns_head', 11 );
		$this->loader->add_filter( 'manage_deposit_posts_columns', $plugin_admin_ui, 'set_deposit_columns_head', 11 );
		$this->loader->add_action( 'manage_product_posts_custom_column', $plugin_admin_ui, 'set_columns_product_deposit_ref', 10, 2 );
		$this->loader->add_action( 'manage_product_posts_custom_column', $plugin_admin_ui, 'set_columns_product_sheet', 10, 3 );
		$this->loader->add_filter( 'manage_edit-product_sortable_columns', $plugin_admin_ui, 'set_columns_head', 11 );
		$this->loader->add_action( 'manage_deposit_posts_custom_column', $plugin_admin_ui, 'set_columns_deposit_archive', 11, 3 );
		$this->loader->add_action( 'manage_deposit_posts_custom_column', $plugin_admin_ui, 'set_columns_deposit_reference', 12, 3 );
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin_ui, 'restrict_by_deposits' );
		$this->loader->add_filter( 'parse_query', $plugin_admin_ui, 'convert_restrict', 1000 );
		$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin_ui, 'add_products_actions' );
		$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin_ui, 'add_orders_actions' );
		$this->loader->add_action( 'admin_footer-edit-tags.php', $plugin_admin_ui, 'add_city_taxonomy_actions' );
		$this->loader->add_action( 'wp_ajax_erase_products', $plugin_admin, 'refair_erase_products_exec' );
		$this->loader->add_action( 'wp_ajax_set_all_products_stock_management', $plugin_admin, 'refair_set_all_products_stock_management_exec' );
		$this->loader->add_action( 'wp_ajax_regen_geometry_meta', $plugin_admin, 'refair_regen_geometry_meta_exec' );
		$this->loader->add_action( 'save_post_deposit', $plugin_admin, 'propagate_dismantle_date_on_save', 99, 3 );
		$this->loader->add_filter( 'manage_shop_order_posts_columns', $plugin_admin_ui, 'set_shop_order_columns_head', 20 );
		$this->loader->add_filter( 'manage_edit-shop_order_sortable_columns', $plugin_admin_ui, 'set_shop_order_columns_head', 20 );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin_ui, 'set_columns_shop_order_additionnal_note', 20, 2 );
		$this->loader->add_filter( 'bulk_actions-edit-product', $plugin_admin_ui, 'add_product_regenerate_pdf_bulk_action' );
		$this->loader->add_filter( 'bulk_actions-edit-deposit', $plugin_admin_ui, 'add_deposit_regenerate_archive_bulk_action' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public           = new Refairplugin_Public( $this->get_plugin_id(), $this->get_version() );
		$plugin_document_manager = new Refairplugin_Documents_Manager();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'build_custom_post_types' );
		// $this->loader->add_filter( 'product_type_selector'                                       , $plugin_public, 'add_wc_product_type' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_materials_route' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_materials_filters_route' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_locations_route' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'add_custom_inventories_fields' );
		// $this->loader->add_action('init' , $plugin_public, 'generate_material_pdf_from_url',10 );
		$this->loader->add_action( 'save_post_product', $plugin_document_manager, 'generate_material_pdf_on_save', 10, 3 );
		$this->loader->add_action( 'save_post_deposit', $plugin_document_manager, 'generate_deposit_pdf_on_save', 30, 3 );
		$this->loader->add_action( 'delete_post', $plugin_document_manager, 'remove_pdf_on_delete', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_actions', $plugin_document_manager, 'refair_add_order_meta_box_action' );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'refair_update_order_links_to_deposits', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $plugin_public, 'refair_remove_order_links_to_deposits', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_status_refunded', $plugin_public, 'refair_remove_order_links_to_deposits', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_action_wc_get_materials_sheets_order_action', $plugin_document_manager, 'refair_process_order_meta_box_action' );
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $plugin_document_manager, 'refair_add_download_expression_of_interest_action', 10, 2 );
		$this->loader->add_action( 'rest_api_init', $plugin_document_manager, 'register_download_archive_order' );
		$this->loader->add_action( 'rest_api_init', $plugin_document_manager, 'register_download_sheet_material' );
		$this->loader->add_action( 'rest_api_init', $plugin_document_manager, 'register_download_deposit_archive' );
		$this->loader->add_action( 'rest_api_init', $plugin_document_manager, 'register_download_deposit_orders_archive' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'create_api_posts_meta_field' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'create_api_deposits_materials_field' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'create_api_deposit_type_terms_metas_field' );

		$this->loader->add_filter( 'rest_deposit_collection_params', $plugin_public, 'add_params_in_rest_request', 10, 1 );
		$this->loader->add_filter( 'rest_deposit_query', $plugin_public, 'order_rest_request_by_meta', 10, 2 );

		$this->loader->add_filter( 'woocommerce_can_reduce_order_stock', $plugin_public, 'refair_do_not_reduce_onhold_stock', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_public, 'refair_order_stock_reduction_based_on_status', 20, 4 );
		$this->loader->add_action( 'wp_ajax_woocommerce_ajax_add_to_cart', $plugin_public, 'woocommerce_ajax_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', $plugin_public, 'woocommerce_ajax_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_regenerate_products_pdf', $plugin_document_manager, 'refair_regenerate_products_pdf_exec' );
		$this->loader->add_action( 'wp_ajax_regenerate_products_pdf', $plugin_document_manager, 'refair_regenerate_products_pdf_exec' );
		$this->loader->add_action( 'wp_ajax_update_deposit_orders_links', $plugin_public, 'refair_update_all_orders_links_to_deposits' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-product', $plugin_document_manager, 'handle_product_regenerate_pdf_bulk_action', 10, 3 );
		$this->loader->add_filter( 'handle_bulk_actions-edit-deposit', $plugin_document_manager, 'handle_deposit_regenerate_pdf_bulk_action', 10, 3 );
		$this->loader->add_action( 'post_type_link', $plugin_public, 'set_custom_product_permalink', 10, 3 );
		$this->loader->add_action( 'init', $plugin_public, 'set_product_permastructure', 13 );
		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'rewrite_product_query_for_sku' );
		$this->loader->add_filter( 'request', $plugin_public, 'rebuild_request_form_sku' );
		$this->loader->add_filter( 'get_sample_permalink_html', $plugin_public, 'get_sample_permalink_html_with_sku', 10, 5 );
		// $this->loader->add_action( 'template_redirect', $plugin_public, 'sku_url_redirect' );

		// $this->loader->add_action( 'posts_join', $plugin_public, 'posts_join_deposit', 10, 2 );
		// $this->loader->add_action( 'posts_fields', $plugin_public, 'posts_fields_deposit', 10, 2 );
		// $this->loader->add_action( 'posts_where', $plugin_public, 'posts_where_deposit', 10, 2 );
		// $this->loader->add_action( 'posts_orderby', $plugin_public, 'posts_orderby_deposit', 10, 2 );
	}


	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	private function define_common_hooks() {

		$term_color_meta = new Refairplugin_Term_Meta_Edit(
			'deposit',
			'deposit_type',
			array(
				'name'    => 'color',
				'title'   => 'Couleur',
				'type'    => 'radio',
				'options' => array(
					'choices' => array(
						array(
							'label' => 'Vert',
							'value' => 'green',
						),
						array(
							'label' => 'Rouge',
							'value' => 'red',
						),
						array(
							'label' => 'Orange',
							'value' => 'orange',
						),
						array(
							'label' => 'Bleu',
							'value' => 'blue',
						),
					),
				),
			),
		);

		$term_insee_code_meta = new Refairplugin_Term_Meta_Edit(
			'deposit',
			'city',
			array(
				'name'  => 'insee_code',
				'title' => 'Code INSEE',
				'type'  => 'text',

			),
		);

		$term_geometry_meta = new Refairplugin_Term_Meta_Edit(
			'deposit',
			'city',
			array(
				'name'    => 'geometry',
				'title'   => 'Contour géographique',
				'type'    => 'text',
				'options' => array(
					'show_in_columns' => false,
					'description'     => "Contour géographique de la commune au format GeoJSON. Utilisé pour afficher le contour dune commune sur une carte. Laisser vide, générer automatiquement ou cliquer 'Regénérer contours'.",
				),
			),
		);

		$term_centroid_meta = new Refairplugin_Term_Meta_Edit(
			'deposit',
			'city',
			array(
				'name'    => 'centroid',
				'title'   => 'Centre géographique',
				'type'    => 'text',
				'options' => array(
					'show_in_columns' => false,
					'description'     => "Centre géographique de la commune au format [lat,lng]. Utilisé pour afficher le centre d'une commune sur une carte. Laisser vide, générer automatiquement ou cliquer 'Regénérer contours'.",
				),
			),
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_id() {
		return $this->plugin_id;
	}

	/**
	 * The public name identifying it within the context of WordPress.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Refairplugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
