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

use Refairplugin\Refairplugin_Meta_Parameters;
use Refairplugin\Refairplugin_Utils;
use Refairplugin\Metas;
use Refairplugin\Refairplugin_Meta_View;
use Refairplugin\Meta;
use Refairplugin\Refairplugin_Term_Meta_Edit;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */
class Refairplugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Factory for meta applied to deposit post type
	 *
	 * @var Refairplugin_Metas_Factory
	 */
	private $meta_factory_deposit;

	/**
	 * Factory for meta applied to shop_order post type
	 *
	 * @var Refairplugin_Metas_Factory
	 */
	private $meta_factory_shop_order;

	/**
	 * Factory for meta applied to provider post type
	 *
	 * @var Refairplugin_Metas_Factory
	 */
	private $meta_factory_provider;

	/**
	 * All meta factories.
	 *
	 * @var array
	 */
	private $metas_factories;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->meta_factory_deposit = $this->create_meta_factory( 'deposit' );

		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'text', 'reference', 'Référence de site', array() ) );
		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'locmap', 'location', 'Localisation', array() ) );
		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'text', 'iris', 'Code IRIS', array() ) );
		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'date', 'dismantle_date', 'Date de démolition', array() ) );
		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'long', 'availability_details', 'Détails de disponibilité', array() ) );
		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'long', 'plus_details', 'Détails des plus', array() ) );
		$this->meta_factory_deposit->create(
			new Refairplugin_Meta_Parameters(
				'extensible',
				'galery',
				'Galerie',
				array(
					'meta' => new Refairplugin_Meta_Parameters(
						'image',
						'image',
						'Image de galerie',
						array()
					),
				),
			)
		);

		$this->meta_factory_deposit->create( new Refairplugin_Meta_Parameters( 'text', 'ressources_url', 'URL de ressources' ) );

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

		$this->meta_factory_shop_order = $this->create_meta_factory( 'shop_order' );

		$this->meta_factory_shop_order->create( new Refairplugin_Meta_Parameters( 'editor', 'additionnal_note', 'Note supplémentaire', array() ) );

		$this->meta_factory_provider = $this->create_meta_factory( 'provider' );

		$this->meta_factory_provider->create(
			new Refairplugin_Meta_Parameters(
				'term',
				'deposit_type_term',
				'Catégorie de fournisseur associée',
				array(
					'multiple' => false,
					'taxonomy' => 'deposit_type',
				)
			)
		);

		$this->meta_factory_provider->create(
			new Refairplugin_Meta_Parameters(
				'number',
				'provider_order',
				'Ordre ( 0 = premier )'
			)
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Refairplugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Refairplugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/refairplugin-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'leaflet', plugin_dir_url( __FILE__ ) . 'js/leaflet/leaflet.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2/css/select2.min.css', array(), $this->version, 'all' );
		if ( defined( WP_DEBUG ) && true === WP_DEBUG ) {
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2/css/select2.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param  string $hook Slug of the triggered hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Refairplugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Refairplugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'leaflet', plugin_dir_url( __FILE__ ) . 'js/leaflet/leaflet.js', array( 'jquery', 'jquery-ui-dialog' ), $this->version, false );
		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2/js/select2.min.js', array( 'jquery' ), $this->version, 'all' );
		if ( defined( WP_DEBUG ) && true === WP_DEBUG ) {
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2/js/select2.js', array( 'jquery' ), $this->version, 'all' );
		}
	}

	/**
	 * Create meta factory for post types
	 *
	 * @param  string $post_type Post type concerned by the meta factory.
	 * @param  array  $options Options used to create meta factory.
	 * @return Refairplugin_Metas_Factory Initialized factory.
	 */
	private function create_meta_factory( $post_type, $options = array() ) {
		$factory                                       = new \Refairplugin\Refairplugin_Metas_Factory( $post_type, $options );
		$this->metas_factories[ $factory->get_slug() ] = $factory;
		return $factory;
	}

	/**
	 * Add fields to product concerning linked deposit.
	 *
	 * @return void
	 */
	public function add_deposit_fields() {
		global $post;
		woocommerce_wp_text_input(
			array(
				'id'          => '_deposit',
				'value'       => get_post_meta( $post->ID, 'deposit', true ),
				'label'       => '<abbr title="' . esc_attr__( 'Deposit listing this material', 'refair-plugin' ) . '">' . esc_html__( 'Deposit', 'refair-plugin' ) . '</abbr>',
				'desc_tip'    => true,
				'description' => __( 'In order to list the materials these are linked to a deposit', 'refair-plugin' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'availability_date',
				'label'       => __( 'Availability date', 'refair-plugin' ),
				'placeholder' => __( 'Availability date', 'refair-plugin' ),
				'description' => __( 'Date on which the materials can be collected (by default the date of the associated deposit)', 'refair-plugin' ),
				'value'       => get_post_meta( $post->ID, 'availability_date', true ),
				'type'        => 'date',
				'desc_tip'    => true,
			)
		);
	}

	/**
	 * Add fields for stock unit and initial stock count.
	 *
	 * @return void
	 */
	public function add_stock_unit_field() {
		global $post;

		woocommerce_wp_text_input(
			array(
				'id'          => 'unit',
				'value'       => get_post_meta( $post->ID, 'unit', true ),
				'label'       => '<abbr title="' . esc_attr__( 'Stock unit', 'refair-plugin' ) . '">' . esc_html__( 'Unit', 'refair-plugin' ) . '</abbr>',
				'desc_tip'    => true,
				'description' => __( 'Unit quantifying the stock', 'refair-plugin' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_initial_stock',
				'value'       => get_post_meta( $post->ID, '_initial_stock', true ),
				'label'       => '<abbr title="' . esc_attr__( 'Initial quantity', 'refair-plugin' ) . '">' . esc_html__( 'Initial quantity', 'refair-plugin' ) . '</abbr>',
				'desc_tip'    => true,
				'description' => __( 'Stock quantity when material is added', 'refair-plugin' ),
			)
		);
	}

	/**
	 * Add group of fields with location, condition and remarques fields.
	 *
	 * @return void
	 */
	public function add_additionnals_fields() {
		global $post;
		echo '<div class="options_group">';

		echo '<h4 style="padding-left=12px" >' . __( 'Additional fields', 'refair-plugin' ) . '</h4>';

		woocommerce_wp_text_input(
			array(
				'id'          => 'location',
				'label'       => __( 'Location', 'refair-plugin' ),
				'placeholder' => __( 'Material location', 'refair-plugin' ),
				'description' => __( 'Location where the materials can be found', 'refair-plugin' ),
				'value'       => get_post_meta( $post->ID, 'location', true ),
				'desc_tip'    => true,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'condition',
				'label'       => __( 'Condition', 'refair-plugin' ),
				'placeholder' => __( 'Material condition', 'refair-plugin' ),
				'description' => __( 'Material condition', 'refair-plugin' ),
				'value'       => get_post_meta( $post->ID, 'condition', true ),
				'desc_tip'    => true,
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'id'          => 'remarques',
				'label'       => __( 'Additionnal comments', 'refair-plugin' ),
				'class'       => 'widefat',
				'placeholder' => __( 'Additional internal notes', 'refair-plugin' ),
				'description' => __( '<br>Additional information displayed only on the Back-office', 'refair-plugin' ),

			)
		);

		echo '</div>';
	}
	/**
	 * Add fields for variations
	 *
	 * @param int     $loop           Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Post data.
	 * @return void
	 */
	public function add_variation_fields( $loop, $variation_data, $variation ) {
		global $post;
		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'            => 'designation',
				'label'         => __( 'Designation', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'Material designation', 'refair-plugin' ),
				'description'   => __( 'Material designation', 'refair-plugin' ),
				'value'         => get_post_meta( $variation->ID, 'designation', true ),
				'desc_tip'      => true,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'            => 'availability_date',
				'label'         => __( 'Availability date', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'Availability date', 'refair-plugin' ),
				'description'   => __( 'Date on which the materials can be collected (by default the date of the associated deposit)', 'refair-plugin' ),
				'value'         => get_post_meta( $variation->ID, 'availability_date', true ),
				'type'          => 'date',
				'desc_tip'      => true,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'            => 'location',
				'label'         => __( 'Location', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'Material location', 'refair-plugin' ),
				'description'   => __( "Location where the materials can be found", 'refair-plugin' ),
				'value'         => get_post_meta( $variation->ID, 'location', true ),
				'desc_tip'      => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'            => 'condition',
				'label'         => __( 'Condition', 'refair-plugin' ),
				'wrapper_class' => 'form-row  form-row-first',
				'placeholder'   => __( 'Material condition', 'refair-plugin' ),
				'description'   => __( 'Material condition', 'refair-plugin' ),
				'value'         => get_post_meta( $variation->ID, 'condition', true ),
				'desc_tip'      => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'            => 'material',
				'label'         => __( 'Material', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-last',
				'placeholder'   => __( 'Material of the material', 'refair-plugin' ),
				'description'   => __( 'Material of the material', 'refair-plugin' ),
				'value'         => get_post_meta( $variation->ID, 'material', true ),
				'desc_tip'      => true,
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'id'                => 'remarques',
				'label'             => __( 'Additionnal comments', 'refair-plugin' ),
				'class'             => 'widefat',
				'wrapper_class'     => 'form-row form-row-full',
				'placeholder'       => __( 'Additional internal notes', 'refair-plugin' ),

				'description'       => __( '<br>Additional information displayed only on the Back-office', 'refair-plugin' ),
				'custom_attributes' => array(
					'data-test'       => 50,
					'data-other-test' => 'Lorem ipsum',
				),
			)
		);

		echo '</div>';
	}


	/**
	 * Modify save product to save additional fields.
	 *
	 * @param  int     $product_id ID of product currently saved.
	 * @param  WP_Post $post Post data of the post currently saved.
	 * @param  boolean $update is an update (true/false).
	 * @return void
	 */
	public function save_additionnals_fields( $product_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'product' === $post->post_type ) {
			if ( isset( $_POST['location'] ) ) {
				$location = $_POST['location'];
				update_post_meta( $product_id, 'location', $location );
			}

			if ( isset( $_POST['remarques'] ) ) {
				$remarques = $_POST['remarques'];
				update_post_meta( $product_id, 'remarques', $remarques );
			}

			if ( isset( $_POST['unit'] ) ) {
				$unit = $_POST['unit'];
				update_post_meta( $product_id, 'unit', $unit );
			}

			if ( isset( $_POST['_initial_stock'] ) ) {
				$_initial_stock = $_POST['_initial_stock'];
				update_post_meta( $product_id, '_initial_stock', $_initial_stock );
			}

			if ( isset( $_POST['_deposit'] ) ) {
				$deposit = $_POST['_deposit'];
				update_post_meta( $product_id, 'deposit', $deposit );
			}

			if ( isset( $POST['condition'] ) ) {
				$condition = $_POST['condition'];
				update_post_meta( $product_id, 'condition', $condition );
			}
		}
	}

	/**
	 * Add Tab to product data.
	 *
	 * @param  array $default_tabs tabs displayed on product edit panel.
	 * @return array default_ttab modified with PEMD tab.
	 */
	public function add_pemd_product_tab( $default_tabs ) {
		$default_tabs['custom_tab'] = array(
			'label'    => __( 'PEMD', 'refair-plugin' ),
			'target'   => 'pemd_product_tab_data',
			'priority' => 60,
			'class'    => array(),
		);
		return $default_tabs;
	}

	/**
	 * Add fields for PEMD data.
	 *
	 * @return void
	 */
	public function set_pemd_product_tab_data() {
		global $post;

		echo '<div id="pemd_product_tab_data" class="panel woocommerce_options_panel">';

		woocommerce_wp_text_input(
			array(
				'id'            => 'code',
				'label'         => __( 'Code', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'Code', 'refair-plugin' ),
				'description'   => __( 'Code', 'refair-plugin' ),
				'value'         => get_post_meta( $post->ID, 'code', true ),
				'desc_tip'      => false,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'            => 'macrocat',
				'label'         => __( 'Macrocat', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'Macrocat', 'refair-plugin' ),
				'description'   => __( 'Macrocat', 'refair-plugin' ),
				'value'         => get_post_meta( $post->ID, 'macrocat', true ),
				'desc_tip'      => false,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'            => 'categorie',
				'label'         => __( 'Category', 'refair-plugin' ),
				'wrapper_class' => 'form-row  form-row-full',
				'placeholder'   => __( 'Category', 'refair-plugin' ),
				'description'   => __( 'Material category', 'refair-plugin' ),
				'value'         => get_post_meta( $post->ID, 'categorie', true ),
				'desc_tip'      => false,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'            => 'pem',
				'label'         => __( 'PEM', 'refair-plugin' ),
				'wrapper_class' => 'form-row form-row-full',
				'placeholder'   => __( 'PEM', 'refair-plugin' ),
				'description'   => __( 'PEM', 'refair-plugin' ),
				'value'         => get_post_meta( $post->ID, 'pem', true ),
				'desc_tip'      => false,
			)
		);

		echo '</div>';
	}

	/**
	 * Manage erase products request
	 *
	 * @return void
	 */
	public function refair_erase_products_exec() {
		$args    = array(
			'post_type'   => 'product',
			'numberposts' => -1,
		);
		$deposit = $_POST['deposit'];
		if ( '0' !== $deposit ) {
			$args['meta_key']   = 'deposit';
			$args['meta_value'] = $deposit;
		}

		$allposts = get_posts( $args );
		foreach ( $allposts as $eachpost ) {
			wp_delete_post( $eachpost->ID, true );
		}
	}

	/**
	 * Set all products _stock_manage meta to true
	 *
	 * @return void
	 */
	public function refair_set_all_products_stock_management_exec() {

		$args     = array(
			'limit' => -1,
		);
		$products = wc_get_products( $args );
		foreach ( $products as $product ) {
			if ( false === $product->get_manage_stock() ) {
				$product->set_manage_stock( true );
				$initial_stock = get_post_meta( '_initial_stock', $product->get_the_ID(), true );
				if ( '' !== $initial_stock ) {
					set_stock_quantity( $initial_stock );
				}
				$product->save();

			}
		}
	}


	/**
	 * Propagate dismantle date meta of the deposit to linked products
	 *
	 * @param  int     $post_id id of the currently saved post.
	 * @param  WP_Post $post object of the currently saved post.
	 * @param  boolean $update if saving is an update or not.
	 * @return void
	 */
	public function propagate_dismantle_date_on_save( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'publish' === $post->post_status ) {
			$deposit_ref    = get_post_meta( $post_id, 'reference', true );
			$deposit_d_date = get_post_meta( $post_id, 'dismantle_date', true );

			if ( ( false !== $deposit_ref ) && ( '' !== $deposit_ref ) && ( false !== $deposit_d_date ) && ( '' !== $deposit_d_date ) ) {

				$args         = array(
					'post_type'   => 'product',
					'meta_key'    => 'deposit',
					'meta_value'  => $deposit_ref,
					'numberposts' => -1,
				);
				$posts_linked = get_posts( $args );

				foreach ( $posts_linked as $post_l ) {
					update_post_meta( $post_l->ID, 'availability_date', $deposit_d_date );
					do_action( 'generate_material_pdf', $post_l->ID, $post_l, true );
				}
				do_action( 'start_generation_pdf', $post_id );
			}
		}
	}
}
