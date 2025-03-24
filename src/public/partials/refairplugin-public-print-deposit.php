<?php
/**
 * Template for deposit pdf print
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public/partials
 */

global $post;
$ref          = get_post_meta( $post->ID, 'reference', true );
$thumbnail_id = get_post_thumbnail_id( $post->ID );
if ( 0 === $thumbnail_id || false === $thumbnail_id ) {
	$thumbnail_id = get_theme_mod( 'custom_logo' );
}

$logo_url = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' )[0];

$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, 'container_half_width_sqr' );
if ( false !== $thumbnail_src ) {
	$thumbnail_url = $thumbnail_src[0];
}

$post_terms = get_the_terms( get_the_ID(), 'product_cat' );
$family     = '';
$category   = '';
if ( false !== $post_terms ) {
	foreach ( $post_terms as $post_term ) {
		if ( 0 === $post_term->parent ) {
			$family = $post_term->name;
			continue;
		}
		$category = $post_term->name;
	}
}

$meta = get_post_meta( get_the_ID() );

if ( ! function_exists( 'get_existing_meta' ) ) {
	/**
	 * Get specific meta from all meta array thanks to key.
	 *
	 * @param  array   $meta array of metas.
	 * @param  string  $key  key to find in meta.
	 * @param  boolean $echo if result has to be echoed or returned.
	 * @return mixed
	 */
	function get_existing_meta( $meta, $key, $echo = false ) {
		$value = false;
		if ( array_key_exists( $key, $meta ) && isset( $meta[ $key ][0] ) ) {
			$value = $meta[ $key ][0];
		}
		if ( true === $echo && false !== $value ) {
			echo $value;
			return;
		}
		return $value;
	}
}

if ( ! function_exists( 'echo_product_line' ) ) {
	/**
	 * Echo product line width details.
	 *
	 * @param  object $product product to echo line.
	 * @return void
	 */
	function echo_product_line( $product ) {

		$condition = get_post_meta( $product->get_id(), 'condition', true );
		$unit      = get_post_meta( $product->get_id(), 'unit', true );
		$dim_arr   = array();
		$length    = $product->get_length();
		$width     = $product->get_width();
		$height    = $product->get_height();

		if ( ! empty( $length ) ) {
			$dim_arr[] = sprintf( 'L %scm', $length );
		}

		if ( ! empty( $width ) ) {
			$dim_arr[] = sprintf( 'l %scm', $width );
		}

		if ( ! empty( $height ) ) {
			$dim_arr[] = sprintf( 'h %scm', $height );
		}

		$dim_str = implode( ' / ', $dim_arr );

		$qty       = $product->get_stock_quantity();
		$var_class = '';
		if ( $product->is_type( 'variable' ) ) {
			$vars = $product->get_available_variations();
			$qty  = 0;
			foreach ( $vars as $var ) {
				if ( array_key_exists( 'max_qty', $var ) ) {
					$qty += intval( $var['max_qty'] );
				} else {
					++$qty;
				}
			}
			$var_class = 'class=variable-row';
		}
		if ( $product->is_type( 'variation' ) ) {
			$var_class = 'class=variation-row';
		}

		?>
		<tr <?php echo esc_attr( $var_class ); ?>>
			<td class="ref-cell"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_sku() ); ?></a></td>
			<td><?php echo esc_html( $product->get_title() ); ?></td>
			<td style="text-align:center;"><?php echo esc_html( $qty ); ?></td>
			<td style="text-align:center;"><?php echo esc_html( $unit ); ?></td>
			<td style="text-align:center;"><?php echo esc_html( $condition ); ?></td>
			<td><?php echo esc_html( $dim_str ); ?></td>
		</tr>
		<?php
	}
}

?>
<body>
	<div class="container content-container">
		<table class="header-table">
			<tbody>
				<tr>
					<td class="logo-cell"><img src="<?php echo esc_url( $logo_url ); ?>"></td>					
				</tr>
				<tr>
					<td class="sub-title-cell">Fiche de Site</td>					
				</tr>
			</tbody>
		</table>
		<table>
			<tbody>
				<tr >
					<td width=50% class="img-cell"><img src="<?php echo esc_url( $thumbnail_url ); ?>"></td>
					<td width=50% class="title-cell">
						<h1 class="product-title"><?php the_title(); ?></h1>
						<div class="ref"><?php echo get_existing_meta( $meta, 'reference' ); ?></div>
					</td>
				</tr>
			</tbody>
		</table>
		<table  width=100%>
			<thead>
				<tr>
				<th colspan="4">Description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<?php
					$deposit_content           = get_the_content();
					$formatted_deposit_content = '';
					if ( ! empty( $deposit_content ) ) {
						$formatted_deposit_content = wpautop( $deposit_content );
					}
					?>
					<td width=100% class="desc-cell" colspan="4"><?php echo wp_kses_post( $formatted_deposit_content ); ?></td>
				</tr>
				<tr>
					<th width=16%>Les +</th><td><?php echo nl2br( get_existing_meta( $meta, 'plus_details', false ) ); ?></td><th width=16%>Date de récupération</th><td><?php get_existing_meta( $meta, 'availability_details', true ); ?></td>
				</tr>
			</tbody>
		</table>
		<div class="deposit-inventory">
			<h2>Inventaire des matériaux</h2>
			<?php
			$parent_terms = get_terms(
				array(
					'taxonomy' => 'product_cat',
					'parent'   => 0,
				)
			);

			if ( is_array( $parent_terms )
			&& count( $parent_terms ) > 0
			&& is_a( $parent_terms[ array_key_first( $parent_terms ) ], WP_Term::class )
			) {
				foreach ( $parent_terms as $parent_term ) {
					?>
					<h3><?php echo esc_html( $parent_term->name ); ?></h3>
					<?php
					$child_terms = get_terms(
						array(
							'taxonomy' => 'product_cat',
							'parent'   => $parent_term->term_id,
						)
					);

					if ( is_array( $child_terms ) && count( $child_terms ) > 0 && is_a( $child_terms[ array_key_first( $child_terms ) ], WP_Term::class ) ) {

						foreach ( $child_terms as $child_term ) {

							$args = array(
								'post_type'  => array( 'product', 'product_variation' ),
								'tax_query'  => array(
									'relation' => 'AND',
									array(
										'taxonomy' => 'product_cat',
										'terms'    => $child_term->term_id,
										'field'    => 'term_id',
									),
								),
								'meta_key'   => 'deposit',
								'meta_value' => $ref,
							);

							$materials = new WP_Query( $args );

							if ( $materials->have_posts() ) {
								?>
								<h4><?php echo esc_html( $child_term->name ); ?></h4>
								<table width=100%>
									<thead><tr><th class='materials-header' style="width:175px">Référence</th><th class='materials-header'>Désignation</th><th class='materials-header' style="width:75px">Qté</th><th class='materials-header' style="width:75px">unité</th><th class='materials-header' style="width:100px">État</th><th class='materials-header' style="width:25%">Dimensions</th></tr></thead>
									<tbody>
									<?php

									while ( $materials->have_posts() ) {
										$materials->the_post();
										$product = wc_get_product( $post->ID );

										echo_product_line( $product );

										if ( $product->is_type( 'variable' ) ) {
											$variations = $product->get_available_variations();

											foreach ( $variations as $variation ) {
												$p_var = wc_get_product( $variation['variation_id'] );
												echo_product_line( $p_var );
											}
										}
									}
									wp_reset_postdata();
									?>
									</tbody>
								</table>
								<?php
							}
						}
					}
				}
			}
			?>
		</div>				
	</div>
</body>