<?php

/**
 * The xls generator functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Refairplugin\Refairplugin_Files_Generator_Input;


class Refairplugin_Xls_Template_Order {

	/**
	 * The static part of xls filename.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $generator_inputs    Input used to generate the xls
	 */
	private Refairplugin_Files_Generator_Input $generator_inputs;

	/**
	 * The static part of xls filename.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $spreadsheet    xls object
	 */
	private Spreadsheet $spreadsheet;


	/**
	 * Colors used on REFAIR website
	 *
	 * @var array
	 */
	private $refairplugin_colors = array(
		'refair_green_100' => 'cff7e3',
		'refair_green_200' => '87a697',
		'refair_green_300' => 'A0CDB7',
		'refair_green_400' => '367857',
		'refair_green_600' => '30644A',
		'refair_green_700' => '24513b',
		'refair_green_900' => '0a1811',
	);

	/**
	 * Class constructor
	 *
	 * @param  Spreadsheet                        $ss
	 * @param  Refairplugin_Files_Generator_Input $generator_inputs
	 */
	public function __construct( Spreadsheet $ss, Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->set_inputs( $generator_inputs );

		$this->set_spreadsheet( $ss );

		return $this;
	}

	/**
	 * Record generator inputs.
	 *
	 * @param  Refairplugin_Files_Generator_Input $generator_inputs
	 * @return void
	 */
	private function set_inputs( Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->generator_inputs = $generator_inputs;
	}

	/**
	 * Record spreadsheet reference to use.
	 *
	 * @param   Spreadsheet $spreadsheet
	 * @return void
	 */
	private function set_spreadsheet( Spreadsheet $spreadsheet ) {

		$this->spreadsheet = $spreadsheet;
	}

	/**
	 * Get content of the xls file.
	 *
	 * @return void
	 */
	public function get_xls_content() {

		global $post;
		$query = new WP_Query(
			array(
				'p'           => intval( $this->generator_inputs->get_id() ),
				'post_type'   => $this->generator_inputs->get_post_type(),
				'post_status' => array( 'any', 'wc-processing', 'wc-completed' ),
			)
		);
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$wc_order = wc_get_order( $post->ID );

				$active_worksheet = $this->spreadsheet->getActiveSheet();
				$this->generate_xls_Eoi_header( $active_worksheet, $wc_order );
				$this->generate_xls_Eoi_Body( $active_worksheet, $wc_order );
			}
		}

		wp_reset_postdata();
	}

	protected function generate_xls_Eoi_header( $aws, $wc_order ) {

		$aws->mergeCells( 'B2:Q2' );
		$aws->setCellValue( 'B2', "Manifestation d'intérêt\n" . $wc_order->get_id() );
		$style_array = array(
			'font'      => array(
				'bold' => true,
				'size' => 30,
			),
			'alignment' => array(
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
			),
			'borders'   => array(
				'top'    => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				),
				'bottom' => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				),
				'right'  => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				),
				'left'   => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				),
			),
		);

		$aws->getStyle( 'B2:Q2' )->applyFromArray( $style_array );
		$cell_value   = $aws->getCell( 'B2' )->getValue();
		$substr_count = 0;
		if ( null !== $cell_value ) {
			$substr_count = substr_count( $cell_value, "\n" );
		}
		$aws->getRowDimension( '2' )->setRowHeight( 39.54 * ( $substr_count + 1 ) );

		$aws->mergeCells( 'B4:D4' );
		$aws->setCellValue( 'B4', 'Date de création' );
		$aws->getStyle( 'B4' )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );

		$aws->mergeCells( 'E4:Q4' );
		$date = new DateTime( $wc_order->get_date_created() );
		$aws->setCellValue( 'E4', $date->format( 'd-m-Y H:i:s' ) );

		$aws->mergeCells( 'B5:D5' );
		$aws->setCellValue( 'B5', "Informations de\ncorrespondance" );
		$aws->getStyle( 'B5' )->getAlignment()->setWrapText( true );
		$aws->getStyle( 'B5' )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
		$cell_value   = $aws->getCell( 'E5' )->getValue();
		$substr_count = 0;
		if ( null !== $cell_value ) {
			$substr_count = substr_count( $cell_value, "\n" );
		}
		$aws->getRowDimension( '5' )->setRowHeight( 14.5 * ( $substr_count + 1 ) );

		$aws->mergeCells( 'E5:Q5' );

		$aws->setCellValue( 'E5', $this->build_correspondance_info( $wc_order ) );
	}


	protected function build_correspondance_info( $wc_order ) {

		$arr_correspondance_info = array();
		array_push( $arr_correspondance_info, sprintf( '%s %s', $wc_order->get_billing_first_name(), $wc_order->get_billing_last_name() ) );
		if ( '' !== $wc_order->get_billing_company() ) {
			array_push( $arr_correspondance_info, $wc_order->get_billing_company() );
		}
		array_push( $arr_correspondance_info, $wc_order->get_billing_address_1() );

		if ( '' !== $wc_order->get_billing_address_2() ) {
			array_push( $arr_correspondance_info, $wc_order->get_billing_address_2() );
		}
		array_push( $arr_correspondance_info, sprintf( '%s - %s', $wc_order->get_billing_city(), $wc_order->get_billing_postcode() ) );
		array_push( $arr_correspondance_info, 'Tel: ' . $wc_order->get_billing_phone() );
		array_push( $arr_correspondance_info, 'Mail: ' . $wc_order->get_billing_email() );
		return implode( "\n", $arr_correspondance_info );
	}


	protected function generate_xls_Eoi_Body( $aws, $wc_order ) {

		$aws->setCellValue( 'B7', 'Ref Materiau' );
		$aws->setCellValue( 'C7', 'Famille' );
		$aws->setCellValue( 'D7', 'Catégorie' );
		$aws->setCellValue( 'E7', 'Designation' );
		$aws->setCellValue( 'F7', 'Qté' );
		$aws->setCellValue( 'G7', 'sur Qté initiale' );
		$aws->setCellValue( 'H7', 'Disponibilité' );

		$aws->getColumnDimension( 'B' )->setAutoSize( true );
		$aws->getColumnDimension( 'C' )->setAutoSize( true );
		$aws->getColumnDimension( 'D' )->setAutoSize( true );
		$aws->getColumnDimension( 'E' )->setAutoSize( true );
		$aws->getColumnDimension( 'F' )->setAutoSize( true );
		$aws->getColumnDimension( 'G' )->setAutoSize( true );
		$aws->getColumnDimension( 'H' )->setAutoSize( true );

		$current_line = 8;
		$items        = $wc_order->get_items();

		$sorted_order_items = $this->sort_order_items( $items );
		foreach ( $sorted_order_items as $deposit_ref => $ordered_families ) {
			$aws->setCellValue( 'B' . strval( $current_line ), $deposit_ref );
			$aws->getStyle( 'B' . strval( $current_line ) )->getFont()->setSize( 22 );
			$aws->getStyle( 'B' . strval( $current_line ) )->getFont()->setBold( true );
			$aws->mergeCells( 'B' . strval( $current_line ) . ':' . 'H' . strval( $current_line ) );
			$aws->getRowDimension( strval( $current_line ) )->setRowHeight( 22 * 1.5, 'pt' );
			$style_array = array(
				'font'      => array(
					'color' => array(
						'rgb' => 'FFFFFF',
					),
				),
				'borders'   => array(
					'outline' => array( 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK ),
				),
				'fill'      => array(
					'color'    => array(
						'rgb' => $this->refairplugin_colors['refair_green_400'],
					),
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				),
				'alignment' => array(
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
					'indent'     => 2,
				),

			);
			$aws->getStyle( 'B' . $current_line . ':H' . $current_line )->applyFromArray( $style_array );
			$deposit_start_line = $current_line;
			++$current_line;
			foreach ( $ordered_families as $ordered_family ) {

				$aws->setCellValue( 'B' . strval( $current_line ), $ordered_family['family']->name );
				$aws->getStyle( 'B' . strval( $current_line ) )->getFont()->setSize( 18 );
				$aws->mergeCells( 'B' . strval( $current_line ) . ':H' . strval( $current_line ) );
				$aws->getRowDimension( strval( $current_line ) )->setRowHeight( 18 * 1.3, 'pt' );
				$style_array = array(
					'borders'   => array(
						'outline' => array( 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM ),
					),
					'fill'      => array(
						'color'    => array(
							'rgb' => $this->refairplugin_colors['refair_green_200'],
						),
						'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					),
					'alignment' => array(
						'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
						'indent'     => 4,
					),
				);
				$aws->getStyle( 'B' . $current_line . ':H' . $current_line )->applyFromArray( $style_array );
				++$current_line;

				foreach ( $ordered_family['children'] as $ordered_category ) {

					$aws->setCellValue( 'B' . strval( $current_line ), $ordered_category['category']->name );
					foreach ( $ordered_category['children'] as $material ) {

						$initial_qty = get_post_meta( $material['product']->get_id(), '_initial_stock', true );
						$unit        = get_post_meta( $material['product']->get_id(), 'unit', true );
						if ( empty( $initial_qty ) || 0 === intval( $initial_qty ) ) {
							$initial_qty = '-';
						}
						$initial_qty = '/ ' . $initial_qty . $unit;

						$aws->setCellValue( 'B' . strval( $current_line ), $material['product']->get_sku() );
						$aws->getCell( 'B' . strval( $current_line ) )->getHyperlink()->setUrl( $material['product']->get_permalink() );
						$aws->setCellValue( 'C' . strval( $current_line ), $ordered_family['family']->name );
						$aws->setCellValue( 'D' . strval( $current_line ), $ordered_category['category']->name );
						$aws->setCellValue( 'E' . strval( $current_line ), $material['cart_item']->get_name() );
						$aws->setCellValue( 'F' . strval( $current_line ), $material['cart_item']->get_quantity() );
						$aws->setCellValue( 'G' . strval( $current_line ), $initial_qty );
						if ( $material['availability'] != false ) {
							$aws->setCellValue( 'H' . strval( $current_line ), $material['availability'] );
						}
						++$current_line;
					}
				}
				++$current_line;
			}
			$style_array = array(
				'borders' => array(
					'outline' => array( 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK ),
				),
			);
			$aws->getStyle( 'B' . $deposit_start_line . ':H' . $current_line )->applyFromArray( $style_array );
			++$current_line;
			++$current_line;
		}
	}

	private function sort_order_items( $order_items ) {
		$ordered_materials = array();
		foreach ( $order_items as $item ) {
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
		return $ordered_materials;
	}

	private function get_file_directory() {

		$path = $this->generator_inputs->get_file_directory();

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
			chmod( $path, 0774 );
		} else {
			$files     = list_files( $path );
			$xls_files = array_filter(
				$files,
				function ( $file ) {
					$ext = pathinfo( $file, PATHINFO_EXTENSION );
					if ( 'xls' !== $ext ) {
						return false;
					}

					return true;
				}
			);
			foreach ( $xls_files as $file ) {
				wp_delete_file( $file );
			}
		}
		return $path;
	}
}
