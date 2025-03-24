<?php
$order = wc_get_order( $post->ID );

$logo_url = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' )[0];

$meta = get_post_meta( get_the_ID() );

if ( ! function_exists( 'get_existing_meta' ) ) {
	function get_existing_meta( $meta, $key, $echo = false ) {
		$value = false;
		if ( array_key_exists( $key, $meta ) && isset( $meta[ $key ][0] ) ) {
			$value = $meta[ $key ][0];
		}
		if ( $echo == true && $value != false ) {
			echo $value;
			return;
		}

		return $value;
	}
}

if ( ! function_exists( 'get_terms_hierarchical' ) ) {
	function get_terms_hierarchical( $args = array() ) {

		if ( ! isset( $args['parent'] ) ) {
			$args['parent'] = 0;
		}

		$terms = get_terms( $args );

		foreach ( $terms as $key => $term ) :

			$args['parent'] = $term->term_id;

			$terms[ $key ]->child_terms = get_terms_hierarchical( $args );

		endforeach;

		return $terms;

	}
}


?>
<body>
	<div class="container content-container">
		<table class="header-table">
			<tbody>
				<tr>
					<td class="logo-cell"><img src="<?php echo $logo_url; ?>"></td>					
				</tr>
				<tr>
					<td class="sub-title-cell"><h1>Manifestation d'intérêt<br><span class="order-ref"><?php echo $order->get_id(); ?></span></h1></td>					
				</tr>
			</tbody>
		</table>
		<table class="correspondant-table" style="width:100%" >
			<tbody>
				<tr >
					<th style="width:33%">
						Date de création
					</th>
					<td>
						<?php
						$date = new DateTime( $order->get_date_created() );
						echo $date->format( 'd-m-Y H:i:s' );
						?>
						<br>
					</td>
				</tr>
				<tr >
					<th style="width:33%">
						Informations de correspondance		
					</th>
					<td>
						<?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?><br>
						<?php
						if ( $order->get_billing_company() != '' ) {
							echo $order->get_billing_company();
							?>
							<br><?php } ?>
						<?php echo $order->get_billing_address_1(); ?><br>
						<?php
						if ( $order->get_billing_address_2() != '' ) {
							echo $order->get_billing_address_2();
							?>
							<br><?php } ?>
						<?php echo $order->get_billing_city() . ' ' . $order->get_billing_postcode(); ?><br>
						<?php echo 'Tel: ' . $order->get_billing_phone(); ?><br>
						<?php echo 'Mail: ' . $order->get_billing_email(); ?>
					</td>
				</tr>
			</tbody>
		</table>
			<?php
			$all_products_categories = get_terms_hierarchical(
				array(
					'taxonomy' => 'product_cat',
					'orderby'  => 'term_order',
				)
			);



			$items             = $order->get_items();
			$ordered_materials = array();
			foreach ( $items as $item ) {
				$item->get_id();
				$product      = wc_get_product( $item->get_product_id() );
				$availability = false;
				if ( false !== $product ) {
					$p_ref   = get_post_meta( $product->get_id(), 'deposit', true );
					$deposit = get_posts(
						array(
							'post_type'  => 'deposit',
							'meta_key'   => 'reference',
							'meta_value' => $p_ref,
						)
					);
					if ( $deposit != false && is_array( $deposit ) && count( $deposit ) > 0 ) {
						$availability = get_post_meta( $deposit[0]->ID, 'availability_details', true );
					}

					if ( ! array_key_exists( $p_ref, $ordered_materials ) ) {
						$ordered_materials[ $p_ref ] = array();
					}

					$family = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'parent' => 0 ) )[0];
					if ( ! array_key_exists( $family->slug, $ordered_materials[ $p_ref ] ) ) {
						$ordered_materials[ $p_ref ][ $family->slug ] = array(
							'family'   => $family,
							'children' => array(),
						);
					}
					$category = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'parent' => $family->term_id ) )[0];
					if ( ! array_key_exists( $category->slug, $ordered_materials[ $p_ref ][ $family->slug ]['children'] ) ) {
						$ordered_materials[ $p_ref ][ $family->slug ]['children'][ $category->slug ] = array(
							'category' => $category,
							'children' => array(),
						);
					}

					$material = array(
						'product'      => $product,
						'cart_item'    => $item,
						'availability' => $availability,
					);

					array_push( $ordered_materials[ $p_ref ][ $family->slug ]['children'][ $category->slug ]['children'], $material );


				}
			}
			$rf_link       = get_the_permalink( $deposit[0] );
			$rf_link_clean = str_replace( 'http://', 'https://', $rf_link );
			$rf_link_clean = str_replace( 'refair', 'refair-bm.fr', $rf_link_clean );
			?>
			<a href="<?php echo $rf_link_clean; ?>"><?php echo $rf_link_clean; ?></a>
<?php
foreach ( $ordered_materials as $deposit_ref => $ordered_families ) {
	$deposit_obj = get_posts(
		array(
			'post_type'  => 'deposit',
			'meta_key'   => 'reference',
			'meta_value' => $deposit_ref,
		)
	);
	?>
		<h2><a href="<?php the_permalink( $deposit_obj[0] ); ?>"><?php echo $deposit_ref; ?></a></h2>
		<table style="width:100%" >
			<thead><tr><th>Ref Materiau</th><th>Famille</th><th>Catégorie</th><th>Designation</th><th>Qté</th><th>Disponibilité</th></tr></thead>
			<tbody>
				<?php
				foreach ( $ordered_families as $ordered_family ) {
					?>
				<tr><td colspan=6 class="fam-title"><?php echo $ordered_family['family']->name; ?></td></tr>
					<?php
					foreach ( $ordered_family['children'] as $ordered_category ) {
						?>
				<tr><td colspan=6 class="cat-title"><?php echo $ordered_category['category']->name; ?></td></tr>
						<?php
						foreach ( $ordered_category['children'] as $material ) {
							?>
			<tr >
							<?php $material_link = esc_url( get_permalink( $material['product']->get_id() ) ); ?>
				<td class="ref-cell"><a href="<?php echo $material_link; ?>"><?php echo $material['product']->get_sku(); ?></a></td>		
				<td class="family-cell"><?php echo $ordered_family['family']->name; ?></a></td>		
				<td class="category-cell"><?php echo $ordered_category['category']->name; ?></a></td>		
				<td class="title-cell"><?php echo $material['cart_item']->get_name(); ?></td>
				<td class="qty-cell"><?php echo $material['cart_item']->get_quantity(); ?></td>
				<td class="availability-cell">
							<?php
							if ( $material['availability'] != false ) {
								echo $material['availability'];}
							?>
				</td>
			</tr>
							<?php
						}
					}
				}
				?>
			</tbody>
		</table>
				<?php
}
?>
					
	</div>
</body>
