<?php
/**
 * The admin-specific ui customization of the plugin.
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 *
 * @link  pixelscodex.com
 * @since 1.0.0
 */

use Refairplugin\Refairplugin_Utils;

/**
 * Class used to modify admin interfaces
 */
class Refairplugin_UI_Customization {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var string $_plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var string $_version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Add columns to $post_columns array
	 *
	 * @param array $post_columns list of columns.
	 *
	 * @return array
	 */
	public function set_columns_head( $post_columns ) {

		$returned_columns = $post_columns;
		$returned_columns = $this->array_push_after( $returned_columns, array( 'deposit_ref' => 'Inventaire' ), 'name' );
		$returned_columns = $this->array_push_after( $returned_columns, array( 'product_sheet' => 'Fiche produit' ), 'date' );
		unset( $returned_columns['product_tag'] );
		unset( $returned_columns['price'] );

		return $returned_columns;
	}

	/**
	 * Add columns to $post_columns array.
	 *
	 * @param  array $post_columns array of columns names.
	 * @return array
	 */
	public function set_deposit_columns_head( $post_columns ) {
		$returned_columns = $post_columns;
		$returned_columns = $this->array_push_after(
			$returned_columns,
			array( 'deposit_reference' => 'Référence de site' ),
			'title'
		);
		$returned_columns = $this->array_push_after(
			$returned_columns,
			array( 'deposit_archive' => 'Archive de site' ),
			'deposit_reference'
		);
		$returned_columns = $this->array_push_after(
			$returned_columns,
			array( 'deposit_orders' => "Manifestations d'intérêts" ),
			'deposit_reference'
		);
		return $returned_columns;
	}

	/**
	 * Set header of columns for orders (expression of interest).
	 *
	 * @param  array $post_columns List of post columns.
	 * @return array Modified List of post columns.
	 */
	public function set_shop_order_columns_head( $post_columns ) {
		$returned_columns = $post_columns;
		$returned_columns = $this->array_push_after(
			$returned_columns,
			array( 'order_additionnal_note' => 'Note supplémentaire' ),
			'order_status'
		);
		$returned_columns = $this->array_push_after(
			$returned_columns,
			array( 'deposits' => 'Sites associés' ),
			'order_status'
		);

		return $returned_columns;
	}

	/**
	 * Add deposit_ref column content.
	 *
	 * @param  string $column_name name of column.
	 * @param  int    $post_id id of the post current line.
	 * @return void
	 */
	public function set_columns_product_deposit_ref( $column_name, $post_id ) {

		$new_columns = array( 'deposit_ref' );

		if ( in_array( $column_name, $new_columns, true ) ) {

			$input_data = '';
			$html       = '<p>-</p>';
			switch ( $column_name ) {
				case 'deposit_ref':
					$input_data = get_post_meta( $post_id, 'deposit', true );
					if ( false !== $input_data ) {
						$html = "<p>{$input_data}</p>";
					}
					break;
				default:
					$html = '<p>-</p>';
					break;
			}

			echo wp_kses(
				$html,
				array(
					'p' => array(),
				)
			);

		}
	}

	/**
	 * Set content of the product sheet column
	 *
	 * @param  string $column_name name of column.
	 * @param  int    $post_id id of the post current line.
	 * @return void
	 */
	public function set_columns_product_sheet( $column_name, $post_id ) {
		$new_columns = array( 'product_sheet' );
		if ( in_array( $column_name, $new_columns, true ) ) {

			$html = '<p>-</p>';
			switch ( $column_name ) {
				case 'product_sheet':
					$is_available = Refairplugin_Utils::is_material_file_exists( $post_id ) ? '' : 'disabled';
					$url          = get_rest_url( null, "material/{$post_id}/download" );
					$html         = "<a class = 'button action' href='{$url}' {$is_available} data-id='{$post_id}'>" . __( 'Download Sheet', 'refair-plugin' ) . '</a>';
					break;
				default:
					$html = '<p>-</p>';
					break;
			}
			echo wp_kses(
				$html,
				array(
					'p' => array(),
					'a' => array(
						'class'    => array(),
						'href'     => array(),
						'data-id'  => array(),
						'disabled' => array(),
					),
				)
			);
		}
	}

	/**
	 * Filter lines according to the deposit column.
	 *
	 * @param  string $post_type Type of WordPress post.
	 * @return void
	 */
	public function restrict_by_deposits( $post_type ) {
		if ( 'product' === $post_type ) {
			$this->restrict_products_by_deposits();
			return; // filter your post.
		}

		if ( 'shop_order' === $post_type ) {
			$this->restrict_order_by_deposits();
			return; // filter your post.
		}
	}

	/**
	 * Function to filter product with deposit ID.
	 *
	 * @return void
	 */
	public function restrict_products_by_deposits() {
		$selected     = '';
		$request_attr = 'deposit_ref';
		if ( isset( $_REQUEST[ $request_attr ] ) ) {
			$selected = $_REQUEST[ $request_attr ];
		}
		// Get unique values of the meta field to filer by.
		$meta_key = 'deposit';
		global $wpdb;
		$deposits = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '%s'
				AND p.post_status IN ('publish', 'draft')
				ORDER BY pm.meta_value",
				$meta_key
			)
		);
		// build a custom dropdown list of values to filter by.
		$select = ( '' === $selected ) ? ' selected="selected"' : '';
		echo '<select id="my-deposit" name="deposit_ref">';
		echo wp_kses(
			"<option value='' {$select}>" . __( 'Show all deposits', 'refair-plugin' ) . ' </option>',
			array(
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
			)
		);
		foreach ( $deposits as $deposit ) {
			$select = ( $deposit === $selected ) ? ' selected="selected"' : '';
			echo wp_kses(
				"<option value='{$deposit}' {$select} >{$deposit}</option>",
				array(
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);
		}
		echo '</select>';
	}

	/**
	 * Filter Order ( Expression of interest ) 
	 *
	 * @return void
	 */
	protected function restrict_order_by_deposits() {
		$selected     = '';
		$request_attr = 'deposit_ref';
		if ( isset( $_REQUEST[ $request_attr ] ) ) {
			$selected = $_REQUEST[ $request_attr ];
		}
		// Get unique values of the meta field to filer by.
		$meta_key = 'deposit';
		global $wpdb;
		$deposits = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '%s'
				AND p.post_status IN ('publish', 'draft')
				ORDER BY pm.meta_value",
				$meta_key
			)
		);
		// build a custom dropdown list of values to filter by.
		$select = ( '' === $selected ) ? ' selected="selected"' : '';
		echo '<select id="my-deposit" name="deposit_ref">';
		echo wp_kses(
			"<option value='' {$select}>" . __( 'Show all deposits', 'refair-plugin' ) . ' </option>',
			array(
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
			)
		);
		foreach ( $deposits as $deposit ) {
			$select = ( $deposit === $selected ) ? ' selected="selected"' : '';
			echo wp_kses(
				"<option value='{$deposit}' {$select} >{$deposit}</option>",
				array(
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);
		}
		echo '</select>';
	}

	/**
	 * Apply filters on requests.
	 *
	 * @param  WP_Query $query WordPress query.
	 * @return void
	 */
	public function convert_restrict( $query ) {

		$type = 'post';
		if ( isset( $_GET['post_type'] ) ) {
			$type = $_GET['post_type'];
		}
		if ( 'product' === $type ) {
			$this->convert_product_restrict( $query );
		}
		if ( 'shop_order' === $type ) {
			$this->convert_shop_order_restrict( $query );
		}
	}

	/**
	 * Apply filtering on deposit reference.
	 *
	 * @param  WP_Query $query current WordPress Query.
	 * @return void
	 */
	public function convert_product_restrict( $query ) {
		global $pagenow;
		if ( is_admin()
			&& 'edit.php' === $pagenow
			&& isset( $_GET['deposit_ref'] )
			&& '' !== $_GET['deposit_ref']
			&& $query->is_main_query()
		) {
			$query->query_vars['meta_key']     = 'deposit';
			$query->query_vars['meta_value']   = $_GET['deposit_ref'];
			$query->query_vars['meta_compare'] = '=';
		}
	}

	/**
	 * Applyfiltering on order according to deposit reference.
	 *
	 * @param  WP_Query $query Current WordPress Query.
	 * @return void
	 */
	public function convert_shop_order_restrict( $query ) {
		global $pagenow;
		if ( is_admin()
			&& 'edit.php' === $pagenow
			&& isset( $_GET['deposit_ref'] )
			&& '' !== $_GET['deposit_ref']
			&& $query->is_main_query()
		) {

			$dep                               = Refairplugin_Utils::refair_get_deposit_by_ref( $_GET['deposit_ref'] );
			$query->query_vars['meta_key']     = 'refair_deposits';
			$query->query_vars['meta_value']   = $dep->ID;
			$query->query_vars['meta_compare'] = 'LIKE';
		}
	}

	/**
	 * Set deposit column content.
	 *
	 * @param  string $column_name Column name.
	 * @param  int    $post_id current post Row id.
	 * @return void
	 */
	public function set_columns_deposit_archive( $column_name, $post_id ) {
		$new_columns = array( 'deposit_archive', 'deposit_orders' );
		if ( in_array( $column_name, $new_columns, true ) ) {

			$html = '<p>-</p>';
			switch ( $column_name ) {
				case 'deposit_archive':
					$btn_text = Refairplugin_Utils::is_deposit_archive_file_exists( $post_id ) ? __( 'Download archive', 'refair-plugin' ) : __( 'Generate archive', 'refair-plugin' );

					$url  = get_rest_url( null, "deposit/{$post_id}/download" );
					$html = "<a class='button action' href='{$url}'>{$btn_text}</a>";
					break;
				case 'deposit_orders':
					$btn_text = __( 'Get orders', 'refair-plugin' );

					$url  = get_rest_url( null, "deposit/{$post_id}/orders" );
					$html = "<a class='button action' href='{$url}'>{$btn_text}</a>";
					break;
				default:
					$html = '<p>-</p>';
			}
			echo wp_kses(
				$html,
				array(
					'p' => array(),
					'a' => array(
						'class' => array(),
						'href'  => array(),
					),
				)
			);
		}
	}

	/**
	 * Set content to deposit reference column
	 *
	 * @param  string $column_name Name of the current column.
	 * @param  int    $post_id Id of the current post.
	 * @return void
	 */
	public function set_columns_deposit_reference( $column_name, $post_id ) {
		$new_columns = array( 'deposit_reference' );
		if ( in_array( $column_name, $new_columns, true ) ) {

			$html = '<p>-</p>';
			switch ( $column_name ) {
				case 'deposit_reference':
					$d_ref = get_post_meta( $post_id, 'reference', true );
					$html  = "<p>{$d_ref}</p>";
					break;
				default:
					$html = '<p>-</p>';
			}
			echo wp_kses(
				$html,
				array(
					'p' => array(),
				)
			);
		}
	}

	/**
	 * Add action in the product column
	 *
	 * @param  array $data Data provided to prepare the action.
	 *
	 * @return boolean return false on failure.
	 */
	public function add_products_actions( $data ) {
		if ( ! isset( $_GET['post_type'] ) || 'product' !== $_GET['post_type'] ) {
			return false;
		}
		$ajax_url = admin_url( 'admin-ajax.php' );

		$meta_key = 'deposit';
		global $wpdb;
		$deposits      = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '%s'
				AND p.post_status IN ('publish', 'draft')
				ORDER BY pm.meta_value",
				$meta_key
			)
		);
		$deposits_json = json_encode( $deposits );

		?>
		<script type="text/javascript">
			document.addEventListener("DOMContentLoaded",function(){
				let ankle = document.querySelector(".wrap"); 
				let before = document.querySelector(".wp-header-end");

				let eraseProductsBlock = document.createElement("div"); 
				eraseProductsBlock.classList.add("erase-products-block");				

				let deposistsJson = '<?php echo $deposits_json; ?>';
				let depositsSelect = document.createElement("select"); 
				depositsSelect.setAttribute("id", "deposit-product");
				depositsSelect.classList.add("deposit-product");
				let deposits = JSON.parse(deposistsJson);
				let optionsDeposits = "<option value='0'>Tous</option>";
				if (Array.isArray(deposits)){
					optionsDeposits += deposits.reduce(function(acc,elt){
						return acc+"<option value='"+elt+"'>"+elt+"</option>"
					},
					"");
				}
				depositsSelect.innerHTML = optionsDeposits;
				eraseProductsBlock.append(depositsSelect);

				let eraseButton = document.createElement("a"); 
				eraseButton.classList.add("erase-products"); 
				eraseButton.innerText = "Supprimer";
				eraseProductsBlock.append(eraseButton);

				ankle.insertBefore(eraseProductsBlock, before); 

				let eraseProducts = document.querySelector(".erase-products");
				eraseProducts.addEventListener(
					'click',
					function requestEraseProducts(params) {
						const data = new FormData();
						let desposit = document.querySelector(".deposit-product");
						eraseProducts = document.querySelector(".erase-products");

						if ( ! eraseProducts.classList.contains('disabled')){

							data.append( 'action', 'erase_products' );
							data.append( 'deposit', desposit.value );

							fetch("<?php echo esc_url( $ajax_url ); ?>", {
								method: "POST",
								credentials: 'same-origin',
								body: data
							})
							.then((response) => response.json())
							.then((data) => {
								setTimeout(() => {
									window.location.reload();
								}, 1000);
							})
							.catch(function(error) {
							});
						}
					}
				);

				let regeneratePdfButton = document.createElement("a"); 
				regeneratePdfButton.classList.add("regen-products-pdf"); 
				regeneratePdfButton.innerText = __("Regenerate PDF", 'refair-plugin');
				ankle.insertBefore(regeneratePdfButton, before); 
				let regeneratePdfSpinner = document.createElement("img"); 
				regeneratePdfSpinner.setAttribute("src","<?php echo esc_url( includes_url() . '/images/spinner.gif' ); ?>")
				regeneratePdfSpinner.classList.add("regen-products-pdf-spinner","hidden"); 
				ankle.insertBefore(regeneratePdfSpinner, before); 

				let regeneratePdf = document.querySelector(".regen-products-pdf");
				regeneratePdf.addEventListener(
					'click',
					function requestRegenerateProductPdf(params) {
						const data = new FormData();

						data.append( 'action', 'regenerate_products_pdf' );

						let regeneratePdfSpinner = document.querySelector(".regen-products-pdf-spinner");
						if( regeneratePdfSpinner ){
							regeneratePdfSpinner.classList.remove("hidden");
						}
						fetch("<?php echo esc_url( $ajax_url ); ?>", {
							method: "POST",
							credentials: 'same-origin',
							body: data
						})
						.then((response) => response.json())
						.then((data) => {
							alert(__("PDF regeneration complete", 'refair-plugin') );
							if( regeneratePdfSpinner ){
							regeneratePdfSpinner.classList.add("hidden");
						}
						})
						.catch(function(error) {
						});
					}
				);

				let setManageStockButton = document.createElement("a"); 
				setManageStockButton.classList.add("set-stock-manage"); 
				setManageStockButton.innerText = "Activer la gestion de stock";
				ankle.insertBefore(setManageStockButton, before); 
				let setManageStockSpinner = document.createElement("img"); 
				setManageStockSpinner.setAttribute("src","<?php echo esc_url( includes_url() . '/images/spinner.gif' ); ?>")
				setManageStockSpinner.classList.add("set-stock-manage-spinner","hidden"); 
				ankle.insertBefore(regeneratePdfSpinner, before); 

				let setManageStock = document.querySelector(".set-stock-manage");
				setManageStock.addEventListener(
					'click',
					function requestSetManageStock(params) {
						const data = new FormData();

						data.append( 'action', 'set_all_products_stock_management' );

						let setManageStockSpinner = document.querySelector(".set-stock-manage-spinner");
						if( setManageStockSpinner ){
							setManageStockSpinner.classList.remove("hidden");
						}
						fetch("<?php echo esc_url( $ajax_url ); ?>", {
							method: "POST",
							credentials: 'same-origin',
							body: data
						})
						.then((response) => response.json())
						.then((data) => {
							alert( __('Stock management activated for all materials', 'refair-plugin') );
							if( setManageStockSpinner ){
								setManageStockSpinner.classList.add("hidden");
						}
						})
						.catch(function(error) {
						});
					}
				);

			});
		</script>
		<style>

			.erase-products-block{
				border: solid 1px #0071a1;
				padding: 2px 7px 2px 2px;
				display:inline-block;
				top: -5px;
				position: relative;
			}

			.erase-products, .deposit-product, .regen-products-pdf, .set-stock-manage  {
				padding: 4px 8px;
				position: relative;				
				text-decoration: none;
				border: 1px solid #0071a1;
				border-radius: 2px;
				text-shadow: none;
				font-weight: 600;
				font-size: 13px;
				line-height: normal;
				color: #0071a1;
				background: #f3f5f6;
				cursor: pointer;
			}

			.erase-products{
				top: 2px;
			}
			.regen-products-pdf, .set-stock-manage  {
				top: -3px;
			}
			.erase-products, .deposit-product, .regen-products-pdf, .regen-products-pdf-spinner, .set-stock-manage, .set-stock-manage-spinner {
				margin-left:5px;
			}
			.erase-products:hover, .regen-products-pdf:hover {
				background: #f1f1f1;
				border-color: #016087;
				color: #015080;
			}

			.regen-products-pdf-spinner.hidden{
				opacity:0;
			}

		</style>
		<?php
	}


		/**
		 * Add deposit_ref column content.
		 *
		 * @param  string $column_name name of column.
		 * @param  int    $post_id id of the post current line.
		 * @return void
		 */
	public function set_columns_shop_order_additionnal_note( $column_name, $post_id ) {

		$new_columns = array( 'order_additionnal_note', 'deposits' );

		if ( in_array( $column_name, $new_columns, true ) ) {

			$input_data = '';
			$html       = '<p>-</p>';
			switch ( $column_name ) {
				case 'order_additionnal_note':
					$input_data = get_post_meta( $post_id, 'additionnal_note', true );
					if ( false !== $input_data ) {
						$html = "<p>{$input_data}</p>";
					}
					break;
				case 'deposits':
					$order        = wc_get_order( $post_id );
					$orders_items = $order->get_items();
					$deposits     = array();
					foreach ( $orders_items as $order_item ) {
						if ( is_a( $order_item, 'WC_Order_Item_Product', true ) ) {
							$item_deposit = get_post_meta( $order_item->get_product_id(), 'deposit', true );
							if ( false !== $item_deposit && ! in_array( $item_deposit, $deposits, true ) ) {
								array_push( $deposits, $item_deposit );
							}
						}
					}
					if ( ! empty( $deposits ) ) {
						$deposits_str = implode( ', ', $deposits );
						$html         = "<p>{$deposits_str}</p>";
					}
					break;
				default:
					$html = '<p>-</p>';
					break;
			}

			echo wp_kses(
				$html,
				array(
					'p'      => array(),
					'strong' => array(),
					'b'      => array(),
					'em'     => array(),
				)
			);

		}
	}

	/**
	 * Add action in the orders column
	 *
	 * @return boolean False on failure or nothing on success.
	 */
	public function add_orders_actions() {
		if ( ! isset( $_GET['post_type'] ) || 'shop_order' !== $_GET['post_type'] ) {
			return false;
		}
		$ajax_url = admin_url( 'admin-ajax.php' );

		$meta_key = 'deposit';
		global $wpdb;
		$deposits      = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '%s'
				AND p.post_status IN ('publish', 'draft')
				ORDER BY pm.meta_value",
				$meta_key
			)
		);
		$deposits_json = json_encode( $deposits );

		?>
		<script id="refair-update-orders-deposit-links" type="text/javascript">
			document.addEventListener("DOMContentLoaded",function(){
				let ankle = document.querySelector(".wrap"); 
				let before = document.querySelector(".wp-header-end");

				let updateLinksButton = document.createElement("a"); 
				updateLinksButton.classList.add("update-links-orders"); 
				updateLinksButton.innerText = "Mettre à jour les sites";
				updateLinksButton.setAttribute('title', "Mettre à jour les liens entre Manifestation d'interet et Sites");
				ankle.insertBefore(updateLinksButton, before); 
				let updateLinksSpinner = document.createElement("img");
				updateLinksSpinner.setAttribute("src","<?php echo esc_url( includes_url() . '/images/spinner.gif' ); ?>")
				updateLinksSpinner.classList.add("update-links-orders-spinner","hidden"); 
				ankle.insertBefore(updateLinksSpinner, before); 

				let updateLinks = document.querySelector(".update-links-orders");
				updateLinks.addEventListener(
					'click',
					function requestRegenerateProductPdf(params) {
						const data = new FormData();

						data.append( 'action', 'update_deposit_orders_links' );

						let updateLinksSpinner = document.querySelector(".update-links-orders-spinner");
						if( updateLinksSpinner ){
							updateLinksSpinner.classList.remove("hidden");
						}
						fetch("<?php echo esc_url( $ajax_url ); ?>", {
							method: "POST",
							credentials: 'same-origin',
							body: data
						})
						.then((response) => response.json())
						.then((data) => {
							alert( __('Update done', 'refair-plugin'));
							if( updateLinksSpinner ){
							updateLinksSpinner.classList.add("hidden");
						}
						})
						.catch(function(error) {
						});
					}
				);

			});
		</script>
		<style>

			.update-links-orders  {
				padding: 4px 8px;
				position: relative;				
				text-decoration: none;
				border: 1px solid #0071a1;
				border-radius: 2px;
				text-shadow: none;
				font-weight: 600;
				font-size: 13px;
				line-height: normal;
				color: #0071a1;
				background: #f3f5f6;
				cursor: pointer;
			}

			.update-links-orders  {
				top: -3px;
			}
			.update-links-orders, .update-links-orders-spinner {
				margin-left:5px;
			}
			.update-links-orders:hover {
				background: #f1f1f1;
				border-color: #016087;
				color: #015080;
			}

			.update-links-orders-spinner.hidden{
				opacity:0;
			}

		</style>
		<?php
	}

	/**
	 * Add product bulk action entry to regenerate pdf
	 *
	 * @param  array $bulk_actions Array of bulk actions availables.
	 * @return array Modified bulk actions.
	 */
	public function add_product_regenerate_pdf_bulk_action( $bulk_actions ) {
		$bulk_actions['regenerate-pdf'] = __( 'Regenerate PDF', 'refair-plugin' );
		return $bulk_actions;
	}

	/**
	 * Push $in element after a $pos element in $src array.
	 *
	 * @param array  $src source array.
	 * @param string $in  element to insert in array.
	 * @param string $pos element after that to insert.
	 *
	 * @return array
	 */
	private function array_push_after( $src, $in, $pos ) {
		if ( is_int( $pos ) ) {
			$r = array_merge( array_slice( $src, 0, $pos + 1 ), $in, array_slice( $src, $pos + 1 ) );
		} else {
			foreach ( $src as $k => $v ) {
				$r[ $k ] = $v;
				if ( $k === $pos ) {
					$r = array_merge( $r, $in );
				}
			}
		}return $r;
	}
}
