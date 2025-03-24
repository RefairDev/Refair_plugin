<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 */
class Refairplugin_Public {

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
	 * Data to generate custom post types.
	 *
	 * @var array
	 */
	protected $custom_post_types_data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name            = $plugin_name;
		$this->version                = $version;
		$this->custom_post_types_data = array();

		$this->create_custom_post_types();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/refairplugin-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/refairplugin-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Launch individual custom post creation from structured array
	 *
	 * @return void
	 */
	public function create_custom_post_types() {
		$cpts = array(
			array(
				'type'     => 'deposit',
				'name'     => 'Site',
				'supports' => array( 'editor', 'title', 'thumbnail', 'custom-fields' ),
				'options'  => array(
					'has_archive'  => true,
					'rewrite_slug' => 'sites',
					'menu_icon'    => 'dashicons-admin-multisite',
					'category'     => array(
						array(
							'label' => 'Commune',
							'slug'  => 'city',
						),
						array(
							'label' => 'Fournisseur',
							'slug'  => 'deposit_type',
						),
					),
				),
			),
			array(
				'type'     => 'provider',
				'name'     => 'Fournisseur',
				'supports' => array( 'editor', 'title', 'thumbnail', 'custom-fields' ),
				'options'  => array(
					'has_archive'  => true,
					'rewrite_slug' => 'fournisseur',
					'menu_icon'    => 'dashicons-groups',
				),
			),
		);

		foreach ( $cpts as $cpt ) {
			$this->create_custom_post_type( $cpt );
		}
	}

	/**
	 * Store custom post type data for further creation.
	 *
	 * @param  array $cpt structured array to store for future custom post type creation.
	 * @return void
	 */
	public function create_custom_post_type( $cpt ) {
		$this->custom_post_types_data[] = array_merge( $cpt );
	}

	/**
	 * Use stored data to create custom post types.
	 *
	 * @return void
	 */
	public function build_custom_post_types() {
		foreach ( $this->custom_post_types_data as $cpt_data ) {
			if ( ! array_key_exists( 'options', $cpt_data ) ) {
				$cpt_data['options'] = array();
			}

			$this->build_custom_post_type( $cpt_data['type'], $cpt_data['name'], $cpt_data['supports'], $cpt_data['options'] );
		}
	}

	/**
	 * Register custom post types and associated taxonomies.
	 *
	 * @param  string $type Slug of the post type.
	 * @param  string $name Name of the post type.
	 * @param  array  $supports Array of capabilities supported by post type.
	 * @param  array  $options Options of post type creation.
	 * @return void
	 */
	protected function build_custom_post_type( $type, $name, $supports, $options ) {
		$options['category'] = ( array_key_exists( 'category', $options ) ) ? $options['category'] : false;

		$options['post_tag'] = ( array_key_exists( 'post_tag', $options ) ) ? $options['post_tag'] : false;

		$taxonomies        = array();
		$option_categories = array();

		$has_archive  = ( array_key_exists( 'has_archive', $options ) ) ? $options['has_archive'] : false;
		$rewrite_slug = ( array_key_exists( 'rewrite_slug', $options ) ) ? $options['rewrite_slug'] : false;

		if ( false !== $options['category'] ) {

			if ( is_string( $options['category'] ) ) {
				$option_categories = array(
					array(
						'label' => $options['category'],
						'slug'  => sanitize_key( $options['category'] ),
					),
				);
			}
			if ( is_array( $options['category'] ) ) {
				$option_categories = $options['category'];
			}

			if ( ! empty( $option_categories ) ) {

				foreach ( $option_categories as $taxonomy_inputs ) {

					$taxonomy_label = $taxonomy_inputs['label'];
					$labels         = array(
						'name'                       => $taxonomy_label . 's',
						'singular_name'              => $taxonomy_label,
						'search_items'               => __( 'Search ', 'refair-plugin' ) . $taxonomy_label,
						'popular_items'              => __( 'Popular ', 'refair-plugin' ) . $taxonomy_label,
						'all_items'                  => __( 'All ', 'refair-plugin' ) . $taxonomy_label,
						'parent_item'                => null,
						'parent_item_colon'          => null,
						'edit_item'                  => __( 'Edit ', 'refair-plugin' ) . $taxonomy_label,
						'update_item'                => __( 'Update ', 'refair-plugin' ) . $taxonomy_label,
						'add_new_item'               => __( 'Add New ', 'refair-plugin' ) . $taxonomy_label,
						'new_item_name'              => sprintf( __( 'New %s Name', 'refair-plugin' ), $taxonomy_label ),
						'separate_items_with_commas' => __( 'Separate writers with commas', 'refair-plugin' ),
						'add_or_remove_items'        => __( 'Add or remove', 'refair-plugin' ) . $taxonomy_label,
						'choose_from_most_used'      => __( 'Choose from the most used ', 'refair-plugin' ) . $taxonomy_label,
						'not_found'                  => sprintf( __( 'No %s found.', 'refair-plugin' ), $taxonomy_label ),
						'menu_name'                  => $taxonomy_label . 's',
					);

					$args = array(
						'label'        => $taxonomy_label . 's',
						'labels'       => $labels,
						'hierarchical' => true,
						'show_in_rest' => true,
					);

					register_taxonomy( $taxonomy_inputs['slug'], $type, $args );

				}
			}

			if ( true === $options['category'] ) {
				$option_categories = array( 'category' );
			}

			foreach ( $option_categories as $taxonomy_name ) {
				if ( 'category' === $taxonomy_name ) {
					register_taxonomy_for_object_type( sanitize_key( $taxonomy_name ), $type ); // Register Taxonomies for Category.
				}
				array_push( $taxonomies, sanitize_key( $taxonomy_name ) );
			}
		}

		if ( $options['post_tag'] ) {
			register_taxonomy_for_object_type( 'post_tag', $type );
			array_push( $taxonomies, 'post_tag' );
		}

		$cpt_args = array(
			'labels'       => array(
				'name'               => $name . 's', // Rename these to suit.
				'singular_name'      => $name,
				'add_new'            => __( 'Add', 'refair-plugin' ),
				'add_new_item'       => __( 'Add new', 'refair-plugin' ) . ' ' . $name,
				'edit'               => __( 'Edit', 'refair-plugin' ),
				'edit_item'          => __( 'Edit a', 'refair-plugin' ) . ' ' . $name,
				'new_item'           => __( 'New', 'refair-plugin' ) . ' ' . $name,
				'view'               => __( 'View', 'refair-plugin' ) . ' ' . $name,
				'view_item'          => __( 'View the', 'refair-plugin' ) . ' ' . $name,
				'search_items'       => __( 'Search for', 'refair-plugin' ) . ' ' . $name,
				/* translators: %s is for post type name */
				'not_found'          => sprintf( __( 'No %s found', 'refair-plugin' ), $name ),
				/* translators: %s is for post type name */
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'refair-plugin' ), $name ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => false, // Allows your posts to behave like Hierarchy Pages.
			'has_archive'  => $has_archive,
			'supports'     => $supports, // Go to Dashboard Custom HTML5 Blank post for supports.
			'can_export'   => true, // Allows export in Tools > Export.
			'taxonomies'   => $taxonomies, // Add Category and Post Tags support.
			'rewrite'      => array( 'slug' => $rewrite_slug ),
		);

		if ( array_key_exists( 'menu_icon', $options ) ) {
			$cpt_args['menu_icon'] = $options['menu_icon'];
		}

		register_post_type(
			$type, // Register Custom Post Type.
			$cpt_args
		);
	}

	/**
	 * Add parameters for REST request such a ordering by dismantling date and location.
	 *
	 * @param  array $params actual authorized request parameters.
	 * @return array New authorized request parameters.
	 */
	public function add_params_in_rest_request( $params ) {

		$params['orderby']['enum'][] = 'dismantle_date';
		$params['orderby']['enum'][] = 'location';
		return $params;
	}

	/**
	 * Handle internal request modification if dismantle date and location is set in REST request as ordering
	 *
	 * @param  array           $args Array of input arguments for internal Request.
	 * @param  WP_REST_Request $request Rest request received.
	 * @return array Modified array of input arguments for internal Request.
	 */
	public function order_rest_request_by_meta( $args, $request ) {

		$meta_keys = array( 'dismantle_date', 'location' );

		$order_by = $request->get_param( 'orderby' );
		if ( isset( $order_by ) && in_array( $order_by, $meta_keys, true ) ) {
			$args['meta_key'] = $order_by;
			$args['orderby']  = 'meta_value';
		}
		return $args;
	}

	/**
	 * Add post-meta-fields in Rest request response filled with return of get_post_meta_for_api function.
	 *
	 * @return void
	 */
	public function create_api_posts_meta_field() {

		foreach ( $this->custom_post_types_data as $post_type_data ) {
			register_rest_field(
				$post_type_data['type'],
				'post-meta-fields',
				array(
					'get_callback' => array( $this, 'get_post_meta_for_api' ),
					'schema'       => null,
				)
			);
		}
	}

	/**
	 * Get all object meta value in a key/value pair array.
	 *
	 * @param  array $rq_resp_object Object for which all meta values are retrieved.
	 * @return array
	 */
	public function get_post_meta_for_api( $rq_resp_object ): array {
		$post_id = $rq_resp_object['id'];

		$raw_metas = get_post_meta( $post_id );
		$metas     = array();
		foreach ( $raw_metas as $key => $raw_meta ) {
			if ( is_serialized( $raw_meta[0] ) ) {
				$metas[ $key ] = maybe_unserialize( $raw_meta[0] );
			} else {
				$metas[ $key ] = $raw_meta[0];
			}
		}
		return $metas;
	}

	/**
	 * Add Term meta field in deposit request response.
	 *
	 * @return void
	 */
	public function create_api_deposit_type_terms_metas_field() {

		register_rest_field(
			'deposit',
			'deposit_type_meta',
			array(
				'get_callback' => array( $this, 'get_deposit_type_terms_metas_for_api' ),
				'schema'       => null,
			)
		);
	}

	/**
	 * Get type of deposit metas.
	 *
	 * @param  array $rq_resp_object Data array of the post.
	 * @return array All Deposit type Terms meta of the input post.
	 */
	public function get_deposit_type_terms_metas_for_api( $rq_resp_object ) {

		$all_terms_metas = array();
		if ( is_array( $rq_resp_object['deposit_type'] ) && count( $rq_resp_object['deposit_type'] ) > 0 ) {
			$metas = get_term_meta( $rq_resp_object['deposit_type'][0] );
			$all_terms_metas[ $rq_resp_object['deposit_type'][0] ] = $metas;
		}
		return $all_terms_metas;
	}

	/**
	 * Register Rest response field for deposit materials count.
	 *
	 * @return void
	 */
	public function create_api_deposits_materials_field() {

		register_rest_field(
			'deposit',
			'materials',
			array(
				'get_callback' => array( $this, 'get_deposit_materials_data_for_api' ),
				'schema'       => null,
			)
		);
	}


	/**
	 * Get deposit materials count.
	 *
	 * @param  array $rq_resp_object deposit post data array.
	 * @return int metarial count for th deposit.
	 */
	public function get_deposit_materials_data_for_api( $rq_resp_object ) {

		$args       = array(
			'post_type'      => array( 'product' ),
			'post_per_pages' => -1,
			'meta_key'       => 'deposit',
			'meta_value'     => $rq_resp_object['post-meta-fields']['reference'],
			'fields '        => 'ids',
		);
		$query      = new WP_Query( $args );
		$last_count = $query->found_posts;
		wp_reset_postdata();
		return $last_count;
	}

	/**
	 * Register locations REST route.
	 *
	 * @return void
	 */
	public function register_locations_route() {
		register_rest_route(
			'locations',
			'get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_locations' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	/**
	 * Get all locations according to terms city taxonomy.
	 *
	 * @return array Array of WP_Terms from city taxonomy.
	 */
	public function get_locations() {
		$locations = array();

		$locations = get_terms(
			array(
				'taxonomy' => 'city',
			)
		);

		return $locations;
	}

	/**
	 * Register REST route to get materials filters values list.
	 *
	 * @return void
	 */
	public function register_materials_filters_route() {
		register_rest_route(
			'materials',
			'filters',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_materials_filters' ),
				'args'                => array(),
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	/**
	 * Get materials filters values list ordered by filters slugs.
	 *
	 * @param  WP_REST_Request $request Current Rest request.
	 * @return array List of filters values ordered by filters slugs.
	 */
	public function get_materials_filters( $request ) {

		/* famillies */
		$famillies_raw = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => 0,
				'order'    => 'term_order',
			)
		);

		$famillies = array_values(
			array_map(
				function ( $term ) {
					return array(
						'id'   => $term->term_taxonomy_id,
						'name' => $term->name,
					);
				},
				$famillies_raw
			)
		);

		$mat_categories = array();
		foreach ( $famillies as $familly ) {

			$fam_categories_raw = get_terms(
				array(
					'taxonomy' => 'product_cat',
					'parent'   => $familly['id'],
					'order'    => 'term_order',
				)
			);

			$fam_categories = array_map(
				function ( $term ) {
					return array(
						'id'     => $term->term_taxonomy_id,
						'name'   => $term->name,
						'parent' => $term->parent,
					);
				},
				$fam_categories_raw
			);

			$familly['children'] = $fam_categories;
			$mat_categories      = array_merge( $mat_categories, $fam_categories );
		}

		/* Deposits */
		$deposits_raw = get_posts(
			array(
				'post_type'   => 'deposit',
				'numberposts' => -1,
				'fields'      => array( 'ID', 'post_title', 'post_meta' ),
			)
		);

		$deposits = array_map(
			function ( $elt ) {
				$dep_for_filter = array();
				$dep_taxos      = array_values( get_taxonomies( array( 'object_type' => array( 'deposit' ) ) ) );
				foreach ( $dep_taxos as $dep_tax ) {
					$terms                                   = wp_get_post_terms( $elt->ID, $dep_tax, array( 'fields' => 'ids' ) );
					$dep_for_filter['relations'][ $dep_tax ] = $terms;
				}
				$dep_for_filter['relations']['deposit'] = array( $elt->ID );
				$dep_for_filter['id']                   = $elt->ID;
				$dep_for_filter['name']                 = $elt->post_title;

				return $dep_for_filter;
			},
			$deposits_raw
		);

		/* Cities */

		$cities_raw = get_terms( array( 'taxonomy' => 'city' ) );

		$cities = array_values(
			array_map(
				function ( $term ) {
					return array(
						'id'   => $term->term_taxonomy_id,
						'name' => $term->name,
					);
				},
				$cities_raw
			)
		);

		/* Providers*/

		$providers_raw = get_terms( array( 'taxonomy' => 'deposit_type' ) );

		$providers = array_values(
			array_map(
				function ( $term ) {
					return array(
						'id'   => $term->term_taxonomy_id,
						'name' => $term->name,
					);
				},
				$providers_raw
			)
		);

		$filters = array(
			'families'     => $famillies,
			'categories'   => $mat_categories,
			'deposits'     => $deposits,
			'cities'       => $cities,
			'deposit_type' => $providers,
		);

		return $filters;
	}

	/**
	 * Register REST route for Materials.
	 *
	 * @return void
	 */
	public function register_materials_route() {
		register_rest_route(
			'materials',
			'get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_materials' ),
				'args'                => array(
					array( 'per_page' => array( 'default' => 9 ) ),
					array( 'offset' => array( 'default' => 1 ) ),
					array( 'product_cat' => array( 'default' => 0 ) ),
					array( 'date_from' => array( 'default' => false ) ),
					array( 'date_to' => array( 'default' => false ) ),
					array( 'only_in_stock' => array( 'default' => false ) ),
					array( 'deposit' => array( 'default' => 0 ) ),
				),
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	/**
	 * Get list of materials accorgin to request input arguments.
	 *
	 * @param  WP_Request $request Current REST request.
	 * @return array Array of response with materials ( materials list ) and offset (offset in materials from previous materials list response ) fields.
	 */
	public function get_materials( $request ) {
		global $post;

		$per_pages     = intval( $request->get_param( 'per_page' ) );
		$offset        = intval( $request->get_param( 'offset' ) );
		$product_cat   = $request->get_param( 'product_cat' );
		$only_in_stock = $request->get_param( 'only_in_stock' );
		$mat_search    = $request->get_param( 'search' );

		$order      = $request->get_param( 'order' );
		$orderby    = $request->get_param( 'orderby' );
		$order_type = $request->get_param( 'ordertype' );

		$deposit   = $request->get_param( 'product_deposit' );
		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );
		$provider  = $request->get_param( 'product_deposit_type' );
		$location  = $request->get_param( 'product_location' );

		$deposits_rq_args = array(
			'deposit'      => $deposit,
			'date_from'    => $date_from,
			'date_to'      => $date_to,
			'location'     => $location,
			'deposit_type' => $provider,
		);

		$deposits_rq_args = array_filter(
			$deposits_rq_args,
			function ( $elt ) {
				return ( ( null !== $elt ) && ( '' !== $elt ) ) ? true : false; }
		);

		$deposits_ids  = '';
		$deposits_refs = array();
		if ( 0 < count( $deposits_rq_args ) ) {
			$deposits_ids  = $this->get_deposits_from_rq( $deposits_rq_args );
			$deposits_refs = array_map(
				function ( $dep_id ) {
					return get_post_meta( $dep_id, 'reference', true );
				},
				explode( ',', $deposits_ids )
			);
		}

		$args = array(
			'post_type' => array( 'product' ),
		);

		if ( isset( $mat_search ) && ! empty( $mat_search ) ) {
			$args['s']          = $mat_search;
			$args['relevanssi'] = true;
		}
		if ( isset( $per_pages ) ) {
			$args['posts_per_page'] = $per_pages;
		}
		if ( isset( $offset ) ) {
			$args['offset'] = intval( $offset );
		}
		if ( ! empty( $deposits_ids ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'deposit',
					'value'   => $deposits_refs,
					'compare' => 'IN',
				),
			);
		}

		if ( isset( $order_type ) ) {

			if ( is_string( $order ) ) {
				$order = strtoupper( $order );
				if ( ( 'ASC' !== $order ) && ( 'DESC' !== $order ) ) {
					$order = 'ASC';
				}
			}

			switch ( $order_type ) {
				case 'prop':
					if ( isset( $orderby ) ) {
						$args['orderby'] = $orderby;
						$args['order']   = $order;
					}
					break;
				case 'meta':
					$args['orderby']  = array(
						'meta_value' => $order,
						'title'      => 'ASC',
					);
					$args['meta_key'] = $orderby;
					break;
				case 'metameta':
					$args['orderby']   = array(
						'meta_value' => $order,
						'title'      => 'ASC',
					);
					$args['meta_meta'] = $orderby;
					break;
			}
		}

		if ( isset( $product_cat ) && ! empty( $product_cat ) && count( explode( ',', $product_cat ) ) > 0 ) {
			$args['tax_query'] = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'product_cat',
					'terms'    => explode( ',', $product_cat ),
				),
			);
		}

		$response   = array( 'materials' => array() );
		$count_mat  = 0;
		$last_count = $per_pages;
		$nb_mat     = 0;
		while ( $nb_mat < $per_pages && ( $last_count >= $per_pages ) ) {
			$args['offset'] = $offset;
			$query          = new WP_Query( $args );
			$last_count     = $query->post_count;
			if ( $query->have_posts() ) {
				while ( $query->have_posts() && $count_mat < 9 ) {
					$query->the_post();
					$in_interval = $this->check_time_interval( $post, $date_from, $date_to );
					$in_stock    = true;
					if ( true === filter_var( $only_in_stock, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) {
						$in_stock = wc_get_product( $post->ID )->is_in_stock();
						if ( wc_get_product( $post->ID )->get_stock_quantity() > 0 ) {
							$in_stock = true;
						}
					}
					if ( true === $in_interval && true === $in_stock ) {
						$post->woocommerce       = $this->get_product_data( $post );
						$post->taxonomy          = $this->get_product_categories( $post );
						$post->deposit           = $this->get_product_deposit( $post );
						$response['materials'][] = $post;
					}
					++$offset;
					$count_mat = count( $response['materials'] );
				}
			}
			wp_reset_postdata();
			$nb_mat = count( $response['materials'] );
		}

		$response['offset'] = $offset;

		return $response;
	}

	/**
	 * Get list of deposit according to input parameters date_from, date_to, location, deposit_type and specific deposit ID.
	 *
	 * @param  array $deposits_args Input deposit query parameters.
	 * @return string List of deposits WP_Post query response - string seperated with comma.
	 */
	protected function get_deposits_from_rq( $deposits_args ) {

		$args_deposit = array(
			'post_type' => array( 'deposit' ),
			'fields'    => 'ids',
		);

		$meta_query = array();
		if ( array_key_exists( 'date_from', $deposits_args ) ) {
			array_push(
				$meta_query,
				array(
					'key'     => 'dismantle_date',
					'value'   => $deposits_args['date_from'],
					'compare' => '>=',
					'type'    => 'DATE',
				)
			);
		}

		if ( array_key_exists( 'date_to', $deposits_args ) ) {
			array_push(
				$meta_query,
				array(
					'key'     => 'dismantle_date',
					'value'   => $deposits_args['date_to'],
					'compare' => '<=',
					'type'    => 'DATE',
				)
			);
		}

		$args_deposit['meta_query'] = $meta_query;

		$tax_query = array( 'relation' => 'AND' );

		if ( array_key_exists( 'location', $deposits_args ) ) {
			array_push(
				$tax_query,
				array(
					'taxonomy' => 'city',
					'terms'    => explode( ',', $deposits_args['location'] ),
				),
			);
		}

		if ( array_key_exists( 'deposit_type', $deposits_args ) ) {
			array_push(
				$tax_query,
				array(
					'taxonomy' => 'deposit_type',
					'terms'    => explode( ',', $deposits_args['deposit_type'] ),
				),
			);
		}

		$args_deposit['tax_query'] = $tax_query;

		if ( array_key_exists( 'deposit', $deposits_args ) ) {
			$args_deposit['include'] = array( $deposits_args['deposit'] );
		}

		return implode( ',', get_posts( $args_deposit ) );
	}

	/**
	 * Add deposit dismantle date to SQL Query.
	 *
	 * @param  string   $posts_fields Input post fields for sql query.
	 * @param  WP_Query $wp_query current post query.
	 * @return string modified post fields for sql query.
	 */
	public function posts_fields_deposit( $posts_fields, $wp_query ) {

		if ( ( array_key_exists( 'meta_meta', $wp_query->query_vars ) && ! empty( 'dismantle_date' === $wp_query->query_vars['meta_meta'] ) ) && ( ( is_array( $wp_query->query_vars['post_type'] ) && in_array( 'product', $wp_query->query_vars['post_type'], true ) ) || ( 'product' === $wp_query->query_vars['post_type'] ) ) ) {
			$posts_fields .= ', dismantle_meta.meta_value as dismantle_meta_date';
		}
		return $posts_fields;
	}

	/**
	 * Modify join SQL query part according to input parameter such as dismantle date for products.
	 *
	 * @param  string   $join Join part of the SQL query.
	 * @param  WP_Query $wp_query input WordPress Query.
	 * @return string join sql query part modified according to wp_query values for dismantle date and post_type.
	 */
	public function posts_join_deposit( $join, $wp_query ) {
		global $wpdb;

		if ( ( array_key_exists( 'meta_meta', $wp_query->query_vars ) && ! empty( 'dismantle_date' === $wp_query->query_vars['meta_meta'] ) ) && ( ( is_array( $wp_query->query_vars['post_type'] ) && in_array( 'product', $wp_query->query_vars['post_type'], true ) ) || ( 'product' === $wp_query->query_vars['post_type'] ) ) ) {
			$join .= "INNER JOIN ( 
				SELECT * 
				FROM $wpdb->postmeta 
				WHERE meta_key = 'deposit' ) AS desposit_meta ON desposit_meta.post_id = $wpdb->posts.ID 
			INNER JOIN (
				SELECT *
				FROM $wpdb->postmeta
				WHERE meta_key =  'reference' ) AS reference_meta ON reference_meta.meta_value = desposit_meta.meta_value	
			INNER JOIN (
				SELECT *
				FROM $wpdb->postmeta
				WHERE meta_key = 'dismantle_date' ) AS dismantle_meta ON dismantle_meta.post_id = reference_meta.post_id
			";
		}

		return $join;
	}

	/**
	 * Modify where SQL query part according to input parameter such as dismantle date for products.
	 *
	 * @param  string   $where Where part of the SQL query.
	 * @param  WP_Query $wp_query input WordPress Query.
	 * @return string where sql query part modified according to wp_query values for dismantle date and post_type.
	 */
	public function posts_where_deposit( $where, $wp_query ) {
		global $wpdb;

		if ( ( array_key_exists( 'meta_meta', $wp_query->query_vars ) && ! empty( 'dismantle_date' === $wp_query->query_vars['meta_meta'] ) ) && ( ( is_array( $wp_query->query_vars['post_type'] ) && in_array( 'product', $wp_query->query_vars['post_type'], true ) ) || ( 'product' === $wp_query->query_vars['post_type'] ) ) ) {
			$where_on_line = json_decode( str_replace( '\n', '', json_encode( $where ) ) );
			$where         = str_replace(
				"AND (   refair_postmeta.meta_key = 'dismantle_date') ",
				'',
				$where_on_line
			);
		}

		return $where;
	}

	/**
	 * Modify post SQL query part to add dismantle date.
	 *
	 * @param  string   $order_by Input orderby part of SQL query.
	 * @param  WP_Query $wp_query WordPress Query.
	 * @return string Modified orderby part of SQL query.
	 */
	public function posts_orderby_deposit( $order_by, $wp_query ) {
		global $wpdb;

		if ( ( array_key_exists( 'meta_meta', $wp_query->query_vars ) && ! empty( 'dismantle_date' === $wp_query->query_vars['meta_meta'] ) ) && is_array( $wp_query->query_vars['post_type'] ) && in_array( 'product', $wp_query->query_vars['post_type'], true ) ) {
			$meta_order = $wp_query->query_vars['orderby']['meta_value'];
			$order_by   = "dismantle_meta_date {$meta_order}, " . $order_by;
		}

		return $order_by;
	}

	/**
	 * Check $post dismantle_date meta is in range between $date_from and $date_to.
	 *
	 * @param  WP_Post $post $post dismantle_date meta to check.
	 * @param  Date    $date_from older date of the duration interval.
	 * @param  Date    $date_to newer date of the duration interval.
	 * @return boolean true is in date interval | false not in date interval
	 */
	protected function check_time_interval( $post, $date_from, $date_to ) {
		$in_interval = true;
		if ( ( false !== $date_from && ! empty( $date_from ) ) || ( false !== $date_to && ! empty( $date_from ) ) ) {
			$in_interval = false;
			$inv_ref     = get_post_meta( $post->ID, 'deposit', true );
			$inv_posts   = get_posts(
				array(
					'post_type'  => 'deposit',
					'meta_key'   => 'reference',
					'meta_value' => $inv_ref,
				)
			);

			if ( isset( $inv_posts ) && is_array( $inv_posts ) && count( $inv_posts ) > 0 ) {
				$desposit       = $inv_posts[0];
				$dismantle_date = get_post_meta( $desposit->ID, 'dismantle_date', true );
				if ( false !== $dismantle_date ) {
					$in_interval = false;
					if ( ( false === $date_from || empty( $date_from ) || ( ! empty( $date_from ) && $dismantle_date >= $date_from ) )
					&& ( false === $date_to || empty( $date_to ) || ( ! empty( $date_to ) && $dismantle_date <= $date_to ) ) ) {
						$in_interval = true;
					}
				}
			}
		}
		return $in_interval;
	}

	/**
	 * Get all product data.
	 *
	 * @param  WP_Post $rq_resp_object current request.
	 * @return array All product metas.
	 */
	public function get_product_data( $rq_resp_object ) {
		$post_id    = $rq_resp_object->ID;
		$product    = wc_get_product( $post_id );
		$conditions = array();
		$dimensions = array();
		$qty        = 0;
		$unit       = 'u';

		if ( $product->get_type() === 'variable' ) {
			$units = array();
			foreach ( $product->get_available_variations() as $variation_array ) {
				$var_cond = get_post_meta( $variation_array['variation_id'], 'condition', true );
				if ( ! in_array( $var_cond, $conditions ) ) {
					$conditions[] = $var_cond;
				}
				if ( count( $conditions ) > 1 ) {
					$conditions = array( __( 'Various', 'refair-plugin' ) );
				}
				$dimensions = array( __( 'Sizes', 'refair-plugin' ), __( 'Various', 'refair-plugin' ) );

				$qty += intval( $variation_array['max_qty'] );

				$units[] = get_post_meta( $variation_array['variation_id'], 'unit', true );
			}

			$unit = array_reduce(
				$units,
				function ( $acc, $elt ) {

					if ( ( null === $acc ) ) {
						$acc = $elt;
					}

					if ( $acc !== $elt || false === $elt || '' === $elt ) {
						$acc = 'u';
					}
					return $acc;
				},
				null
			);

		} else {
			$conditions[] = get_post_meta( $product->get_id(), 'condition', true );
			if ( ! empty( $product->get_length() ) ) {
				$dimensions[] = array( 'L', $product->get_length(), 'cm' );
			}
			if ( ! empty( $product->get_width() ) ) {
				$dimensions[] = array( 'l', $product->get_width(), 'cm' );
			}
			if ( ! empty( $product->get_height() ) ) {
				$dimensions[] = array( 'h', $product->get_height(), 'cm' );
			}
			if ( count( $dimensions ) < 1 ) {
				$dimensions[] = '-';
			}

			$qty = $product->get_stock_quantity();

			$unit = get_post_meta( $product->get_id(), 'unit', true );

			if ( false === $unit || '' === $unit ) {
				$unit = 'u';
			}
		}

		$srcs = wp_get_attachment_image_src( $product->get_image_id(), 'large_thumbnail' );
		if ( false !== $srcs ) {
			$src = $srcs[0];
		} else {
			$src = '#';
		}

		$metas = array(
			'link'             => $product->get_permalink(),
			'name'             => $product->get_name(),
			'sku'              => $product->get_sku(),
			'stock_qty'        => $qty,
			'unit'             => $unit,
			'stock_status'     => $product->get_stock_status(),
			'variations'       => $product->get_children(),
			'cats'             => wc_get_product_category_list( $product->get_id() ),
			'featured_img'     => $src,
			'gallery_ids'      => $product->get_gallery_image_ids(),
			'add_to_cart_link' => $product->add_to_cart_url(),
			'conditions'       => $conditions,
			'dimensions'       => $dimensions,
		);

		return $metas;
	}

	/**
	 * Get product categories. If category have no parent it's classified as Family.
	 *
	 * @param  WP_REST_Request $rq_resp_object REst Request from user.
	 * @return array Array of terms with two main keys family and category.
	 */
	public function get_product_categories( $rq_resp_object ) {
		$terms = array(
			'family'   => null,
			'category' => null,
		);
		if ( is_a( $rq_resp_object, WP_Post::class ) ) {
			$post_id   = $rq_resp_object->ID;
			$raw_terms = get_the_terms( $post_id, 'product_cat' );
			if ( false !== $raw_terms && is_array( $raw_terms ) ) {
				foreach ( $raw_terms as $term ) {
					if ( 0 === $term->parent ) {
						$terms['family'] = $term;
					} else {
						$terms['category'] = $term;
					}
				}
			}
		}
		return $terms;
	}

	/**
	 * Get deposit of a product.
	 *
	 * @param  WP_Post $rq_resp_object Input product.
	 * @return array Deposit data linked to input product.
	 */
	public function get_product_deposit( $rq_resp_object ) {
		$deposit = array(
			'name'         => __( 'Deposit', 'refair-plugin' ),
			'id'           => 0,
			'link'         => '#',
			'availability' => 'Terminé',
		);
		$post_id = $rq_resp_object->ID;

		$inv_ref   = get_post_meta( $post_id, 'deposit', true );
		$inv_posts = get_posts(
			array(
				'post_type'  => 'deposit',
				'meta_key'   => 'reference',
				'meta_value' => $inv_ref,
			)
		);
		if ( false !== $inv_posts && 0 < count( $inv_posts ) ) {

			$inv_post        = $inv_posts[0];
			$deposit['name'] = $inv_post->post_title;
			$deposit['id']   = $inv_post->ID;
			$deposit['link'] = get_permalink( $inv_post->ID );
			$inv_loc         = get_post_meta( $inv_post->ID, 'location', true );
			if ( false !== $inv_loc ) {
				$re      = '/, [0-9]{5} (.*), /m';
				$matches = array();
				preg_match_all( $re, $inv_loc['location'], $matches, PREG_SET_ORDER, 0 );
				$deposit['location'] = $matches[0][1];
			}
			$inv_av = get_post_meta( $inv_post->ID, 'dismantle_date', true );
			if ( false !== $inv_av && 0 !== $inv_av && '' !== $inv_av ) {
				$deposit['availability'] = $inv_av;
			}
		}
		return $deposit;
	}

	/**
	 * Add custom fields in deposit requests.
	 *
	 * @return void
	 */
	public function add_custom_inventories_fields() {
		register_rest_field(
			'deposit',
			'availability',
			array(
				'get_callback' => array(
					$this,
					'get_deposit_availability',
				),
				'schema'       => null,
			)
		);
		register_rest_field(
			'deposit',
			'location',
			array(
				'get_callback' => array(
					$this,
					'get_deposit_location',
				),
				'schema'       => null,
			)
		);
		register_rest_field(
			'deposit',
			'iris',
			array(
				'get_callback' => array(
					$this,
					'get_deposit_iris',
				),
				'schema'       => null,
			)
		);
	}

	/**
	 * Get deposit dismantle date thanks to deposit ID.
	 *
	 * @param  array $deposit_data Deposit data.
	 * @return string Dismantle date value.
	 */
	public function get_deposit_availability( $deposit_data ) {
		return get_post_meta( $deposit_data['id'], 'dismantle_date', true );
	}

	/**
	 * Get deposit location thanks to deposit ID.
	 *
	 * @param  array $deposit_data Deposit data.
	 * @return string Location value.
	 */
	public function get_deposit_location( $deposit_data ) {
		$inv_loc = get_post_meta( $deposit_data['id'], 'location', true );
		if ( false === $inv_loc || empty( $inv_loc ) ) {
			return false;
		}
		$re      = '/, [0-9]{5} (.*), /m';
		$matches = array();
		preg_match_all( $re, $inv_loc['location'], $matches, PREG_SET_ORDER, 0 );
		return $matches[0][1];
	}

	/**
	 * Get deposit IRIS thanks to deposit ID.
	 *
	 * @param  array $deposit_data Deposit data.
	 * @return string IRIS meta value.
	 */
	public function get_deposit_iris( $deposit_data ) {
		return get_post_meta( $deposit_data['id'], 'iris', true );
	}

	/**
	 * Get deposit dismantle date thanks to deposit ID.
	 *
	 * @param  array $deposit_data Deposit data.
	 * @return string Dismantle date value.
	 */
	public function get_deposit_dismantle_date( $deposit_data ) {
		return get_post_meta( $deposit_data['id'], 'dismantle_date', true );
	}


	/* Partials Template Managing */

	/**
	 * Get path to template part.
	 *
	 * @param  [type]  $template_names templates names reuired.
	 * @param  boolean $load is tempalte have to be loaded.
	 * @param  boolean $rq_once is required once or just this time.
	 * @return string Path to template part.
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
	 * Forget to reduice statsu on on-hold status and processing status.
	 *
	 * @param  boolean  $reduce_stock is stock has to be reduced.
	 * @param  WC_Order $order Current order data object.
	 * @return boolean New value if is stock has to be reduced.
	 */
	public function refair_do_not_reduce_onhold_stock( $reduce_stock, $order ) {
		if ( $order->has_status( 'on-hold' ) || $order->has_status( 'processing' ) ) {
			$reduce_stock = false;
		}
		return $reduce_stock;
	}

	/**
	 * Manage stock reduction according to order status transition.
	 *
	 * @param  int      $order_id ID of the order.
	 * @param  string   $old_status Old status.
	 * @param  string   $new_status New status.
	 * @param  WC_order $order Order data Object.
	 * @return void
	 */
	public function refair_order_stock_reduction_based_on_status( $order_id, $old_status, $new_status, $order ) {
		// Only for 'processing' and 'completed' order statuses change.
		if ( 'completed' === $new_status ) {
			$stock_reduced = get_post_meta( $order_id, '_order_stock_reduced', true );
			if ( empty( $stock_reduced ) && 'bacs' === $order->get_payment_method() ) {
				wc_reduce_stock_levels( $order_id );
			}
		}
	}

	/**
	 * Manage Add to cart with Ajax request.
	 *
	 * @return void
	 */
	public function woocommerce_ajax_add_to_cart() {

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$variation_id      = absint( $_POST['variation_id'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) && 'publish' === $product_status ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}

			WC_AJAX::get_refreshed_fragments();
		} else {

			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			);

			echo wp_send_json( $data );
		}

		wp_die();
	}

	/**
	 * Loop on all pending orders to update links with deposits
	 *
	 * @return void
	 */
	public function refair_update_all_orders_links_to_deposits() {

		$args   = array(
			'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
			'limit'  => -1,
		);
		$orders = wc_get_orders( $args );

		error_log( 'Update link to deposit for all orders' );
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			$this->refair_update_order_links_to_deposits( $order_id, $order );
		}
	}

	/**
	 * Update link between $order and corresponding deposits targeted by order items
	 *
	 * @param  int      $order_id Order designed with its ID.
	 * @param  WC_Order $order Order data objecct.
	 * @return void
	 */
	public function refair_update_order_links_to_deposits( $order_id, $order ) {

		$materials   = $order->get_items();
		$deposit_ids = array();

		error_log( 'Update link to deposit for order: ' . $order_id );
		/* Get all deposits concerned by order */
		foreach ( $materials as $material ) {
			$dep_orders = array();
			$dep_ref    = get_post_meta( $material->get_product_id(), 'deposit', true );

			$dep_obj = $this->refair_get_deposit_by_ref( $dep_ref );

			if ( ( false !== $dep_obj ) && ( ! in_array( $dep_obj->ID, $deposit_ids, true ) ) ) {
				array_push( $deposit_ids, $dep_obj->ID );
			}
		}
		error_log( 'For order ' . $order_id . ' deposits found : ' . implode( ',', $deposit_ids ) );
		update_post_meta( $order_id, 'refair_deposits', $deposit_ids );

		/* for each deposit add order_id to its orders list meta */
		foreach ( $deposit_ids as $deposit_id ) {
			$dep_orders = get_post_meta( $deposit_id, 'refair_orders', true );

			if ( empty( $dep_orders ) ) {
				$dep_orders = array();
			}

			if ( ! in_array( $order_id, $dep_orders, true ) ) {
				array_push( $dep_orders, $order_id );
				update_post_meta( $deposit_id, 'refair_orders', $dep_orders );
			}
		}
	}

	/**
	 * Remove link between $order and corresponding deposits targeted by order items
	 *
	 * @param  int      $order_id Id of the current expression of interest.
	 * @param  WC_Order $order Object of the current expression of interest.
	 * @return void
	 */
	public function refair_remove_order_links_to_deposits( $order_id, $order ) {

		$materials   = $order->get_items();
		$deposit_ids = array();

		/* Get all deposits concerned by order */
		foreach ( $materials as $material ) {
			$dep_orders = array();
			$dep_ref    = get_post_meta( $material->get_product_id(), 'deposit', true );

			$dep_obj = $this->refair_get_deposit_by_ref( $dep_ref );

			if ( ( false !== $dep_obj ) && ( ! in_array( $dep_obj->ID, $deposit_ids, true ) ) ) {
				array_push( $deposit_ids, $dep_obj->ID );
			}
		}

		/* for each deposit remove order_id to its orders list meta */
		foreach ( $deposit_ids as $deposit_id ) {
			$dep_orders = get_post_meta( $deposit_id, 'refair_orders', true );

			$dep_orders = array_diff( $dep_orders, array( $order_id ) );

			update_post_meta( $dep_obj->ID, 'refair_orders', $dep_orders );
		}
	}

	/**
	 * Get deposit WP_post object according to metakey deposit reference
	 *
	 * @param  string $reference deposit reference.
	 * @return mixed WP_Post | false return Deposit WP_Post or false.
	 */
	public function refair_get_deposit_by_ref( $reference ) {

		$found_posts = false;
		if ( ! empty( $reference ) ) {
			$found_posts_raw = get_posts(
				array(
					'post_type'   => 'deposit',
					'post_status' => array( 'draft', 'publish' ),
					'meta_key'    => 'reference',
					'meta_value'  => $reference,
				)
			);

			if ( is_array( $found_posts_raw ) && ( 0 < count( $found_posts_raw ) ) ) {
				$found_posts = $found_posts_raw[0];
			}
		}

		return $found_posts;
	}
}
