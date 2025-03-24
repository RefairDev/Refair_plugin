<?php

/* template_function */
if ( ! function_exists( 'get_existing_meta' ) ) {
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

$logo_url = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' )[0];

$logo_ressource  = $logo_url;
$parsed_logo_url = parse_url( $logo_url );
if ( is_array( $parsed_logo_url ) && array_key_exists( 'path', $parsed_logo_url ) && ( ! empty( $parsed_logo_url['path'] ) ) ) {
	$logo_file = ABSPATH . ltrim( $parsed_logo_url['path'], '/' );
	if ( file_exists( $logo_file ) ) {
		$logo_ressource = $logo_file;
	}
}



$product = wc_get_product( $post->ID );

$product_type       = null;
	$length         = null;
	$width          = null;
	$height         = null;
	$qty            = null;
	$attachment_ids = array();

if ( $product != false ) {
	$product_type   = $product->get_type();
	$length         = $product->get_length();
	$width          = $product->get_width();
	$height         = $product->get_width();
	$qty            = $product->get_stock_quantity();
	$attachment_ids = $product->get_gallery_image_ids();
}

/* get all informations product*/
$thumbnail_id = get_post_thumbnail_id( $post->ID );

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

$meta               = get_post_meta( get_the_ID() );
$material_nature    = get_existing_meta( $meta, 'material', false );
$material_condition = get_existing_meta( $meta, 'condition', false );

$unit = get_existing_meta( $meta, 'unit', false );

if ( 'u' === $unit ) {
	$unit = '';
}

/*
availability management */
/* get deposit of the product */
$deposit_ref    = get_existing_meta( $meta, 'deposit', false );
$dismantle_date = 'Non renseignée';
if ( false !== $deposit_ref ) {
	$args    = array(
		'post_type'   => 'deposit',
		'post_statut' => 'publish',
		'meta_key'    => 'reference',
		'meta_value'  => $deposit_ref,
	);
	$deposit = get_posts( $args );

	/* get availability of the deposit */
	if ( 0 < count( $deposit ) ) {
		$dismantle_date = get_post_meta( $deposit[0]->ID, 'availability_details', true );
	}
}


/* get parent product if variation and information not filled */

if ( 'variation' === $product_type ) {
	$parent_id      = $product->get_parent_id();
	$parent_product = wc_get_product( $parent_id );

	/* parent fakllback for thumbnail */
	if ( 0 === $thumbnail_id || false === $thumbnail_id ) {
		$thumbnail_id = get_post_thumbnail_id( $parent_id );
	}

	/* parent fakllback for family / category */
	if ( '' === $family || '' === $category ) {
		$post_terms = get_the_terms( $parent_id, 'product_cat' );

		if ( false !== $post_terms ) {
			foreach ( $post_terms as $post_term ) {
				if ( 0 === $post_term->parent && '' === $family ) {
					$family = $post_term->name;
					continue;
				}
				if ( $post_term->parent != 0 && $category == '' ) {
					$category = $post_term->name;
				}
			}
		}
	}

	/* parent fallback metas*/
	$parent_meta = get_post_meta( $parent_id );
	if ( ! $material_nature || empty( $material_nature ) ) {
		$material_nature = get_existing_meta( $parent_meta, 'material', false );
		if ( ! $material_nature || empty( $material_nature ) ) {
			$material_nature = '-';
		}
	}

	if ( ! $material_condition || empty( $material_condition ) ) {
		$material_condition = get_existing_meta( $parent_meta, 'condition', false );
		if ( ! $material_condition || empty( $material_condition ) ) {
			$material_condition = '-';
		}
	}
}

/* fallback informations */

if ( 0 === $thumbnail_id || false === $thumbnail_id ) {
	$thumbnail_id = get_theme_mod( 'custom_logo' );
}


$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, 'container_half_width_sqr' );
if ( false !== $thumbnail_src ) {
	$thumbnail_ressource = $thumbnail_src[0];

	$parsed_thumbnail_url = parse_url( $thumbnail_src[0] );
	if ( is_array( $parsed_thumbnail_url ) && array_key_exists( 'path', $parsed_thumbnail_url ) && ( ! empty( $parsed_thumbnail_url['path'] ) ) ) {
		$thumbnail_file = ABSPATH . ltrim( $parsed_thumbnail_url['path'], '/' );
		if ( file_exists( $thumbnail_file ) ) {
			$thumbnail_ressource = $thumbnail_file;
		}
	}
	// $thumbnail_url = $thumbnail_src[0];
}


?>
<body>
	<div class="container content-container">
	<table class="header-table">
			<tbody>
				<tr>
					<td class="logo-cell"><img height="30px" src="<?php echo $logo_ressource; ?>" ></td>					
				</tr>
				<tr>
					<td class="sub-title-cell">Fiche Matériau</td>					
				</tr>
			</tbody>
		</table>
		<table>
			<tbody>
				<tr >
					<td width=40% class="img-cell" rowspan=4 ><img src="<?php echo $thumbnail_ressource; ?>"></td>
					<td width=60% class="title-cell" colspan=4 style="height:1rem;" > 
						<div class="family"><?php echo $family; ?></div>
					</td>
				</tr>
				<tr >
					<td width=60% class="title-cell" colspan=4 style="height:1rem;">
						<div class="category"><?php echo $category; ?></div>
					</td>
				</tr>
				<tr >
					<td width=60% class="title-cell" colspan=4>
						<h1 class="product-title"><?php the_title(); ?></h1>
					</td>
				</tr>
				<tr >
					<th width=15% class="qty-header-cell"  style="height:1.5rem;">Quantité</th>
					<td width=15% class="qty-cell" style="height:1.5rem;"><?php echo $qty . $unit; ?></td>
					<th width=15% class="avail-header-cell" style="height:1.5rem;">Date de disponibilité</th>
					<td width=15% class="avail-cell" style="height:1.5rem;"><?php echo $dismantle_date; ?></td>					
				</tr>							
			</tbody>
		</table>
		<table  width=100%>
			<thead>
				<tr>
				<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width=100% class="desc-cell"><?php the_content(); ?></td>
				</tr>
			</tbody>
		</table>
		<div class="row">
			<div class="col-12">
				<table width=100%>
					<tbody>
						<tr><th class="detail-cell">Matériau</th><td class="detail-cell"><?php echo $material_nature; ?></td></tr>
						<tr>
							<th class="detail-cell">Dimensions</th>
							<td class="detail-cell">
								<ul>
									<?php
									if ( ! empty( $length ) && '0' !== $length ) {
										?>
										<li><span>Longueur: </span><?php echo $length; ?>cm</li> <?php } ?>
									<?php
									if ( ! empty( $width ) && '0' !== $width ) {
										?>
										<li><span>Largueur: </span><?php echo $width; ?>cm</li> <?php } ?>
									<?php
									if ( ! empty( $height ) && '0' !== $height ) {
										?>
										<li><span>Hauteur: </span><?php echo $height; ?>cm</li> <?php } ?>
								</ul>
							</td>
						</tr>
						<tr>
							<th class="detail-cell">État</th>							
							<td class="detail-cell"><?php echo $material_condition; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>		
		<?php
		$attachment_ids = $product->get_gallery_image_ids();
		if ( count( $attachment_ids ) > 0 ) {
			?>
		<div class="row">
			<div class="col-12">
				<table width=100%>
					<thead>
						<tr>
							<th colspan=6>Images supplémentaires</th>
						</tr>
					</thead>
					<tbody>
						<tr style="width:100%">
						<?php
						$attachment_ids = $product->get_gallery_image_ids();

						$percent = 100 / count( $attachment_ids );
						foreach ( $attachment_ids as $attachment_id ) {

							$attach_src = wp_get_attachment_image_src( $attachment_id, 'shop_catalog' );
							if ( false !== $attach_src ) {
								$attach_ressource = $attach_src[0];

								$parsed_attach_url = parse_url( $attach_src[0] );
								if ( is_array( $parsed_attach_url ) && array_key_exists( 'path', $parsed_attach_url ) && ( ! empty( $parsed_attach_url['path'] ) ) ) {
									$attach_file = ABSPATH . ltrim( $parsed_attach_url['path'], '/' );
									if ( file_exists( $attach_file ) ) {
										$attach_ressource = $attach_file;
									}
								}
							}
							?>
							<td style="width:<?php echo $percent; ?>%;text-align: center;" ><img style="height:10rem;" src="<?php echo $attach_ressource; ?>" /></td>
							<?php
						}
						?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
			<?php
		}
		?>
	</div>
</body>
