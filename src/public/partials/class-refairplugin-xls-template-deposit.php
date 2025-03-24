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


class Refairplugin_Xls_Template_Deposit {

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
	 * Get content for the xls file.
	 *
	 * @return void
	 */
	public function get_xls_content() {

		global $post;
		$query = new WP_Query(
			array(
				'p'         => intval( $this->generator_inputs->get_id() ),
				'post_type' => $this->generator_inputs->get_post_type(),
			)
		);
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$active_worksheet = $this->spreadsheet->getActiveSheet();
				$this->generate_xls_Eoi_header( $active_worksheet, $post );
				$this->generate_xls_Eoi_Body( $active_worksheet, $post );
			}
		}

		wp_reset_postdata();
	}

	protected function generate_xls_Eoi_header( $aws, $deposit ) {

		$aws->mergeCells( 'B2:I2' );
		$aws->setCellValue( 'B2', "Manifestations d'intérêt\npour le Site d'inventaire\n" . $deposit->ID );
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

		$aws->getStyle( 'B2:I2' )->applyFromArray( $style_array );
		$cell_value   = $aws->getCell( 'B2' )->getValue();
		$substr_count = 0;
		if ( null !== $cell_value ) {
			$substr_count = substr_count( $cell_value, "\n" );
		}
		$aws->getRowDimension( '2' )->setRowHeight( 39.54 * ( $substr_count + 1 ) );
	}


	protected function generate_xls_Eoi_Body( $aws, $deposit ) {

		$aws->setCellValue( 'B7', 'Ref M.I.' );
		$aws->setCellValue( 'C7', 'Ref Materiau' );
		$aws->setCellValue( 'D7', 'Famille' );
		$aws->setCellValue( 'E7', 'Catégorie' );
		$aws->setCellValue( 'F7', 'Designation' );
		$aws->setCellValue( 'G7', 'Qté' );
		$aws->setCellValue( 'H7', 'Unité' );
		$aws->setCellValue( 'I7', 'Disponibilité' );

		$aws->getColumnDimension( 'B' )->setAutoSize( true );
		$aws->getColumnDimension( 'C' )->setAutoSize( true );
		$aws->getColumnDimension( 'D' )->setAutoSize( true );
		$aws->getColumnDimension( 'E' )->setAutoSize( true );
		$aws->getColumnDimension( 'F' )->setAutoSize( true );
		$aws->getColumnDimension( 'G' )->setAutoSize( true );
		$aws->getColumnDimension( 'H' )->setAutoSize( true );
		$aws->getColumnDimension( 'I' )->setAutoSize( true );

		$current_line       = 8;
		$items              = $this->get_and_filter_items( $deposit );
		$sorted_order_items = $this->sort_order_items( $items );
		foreach ( $sorted_order_items as $ordered_families ) {
			$deposit_start_line = $current_line;
			foreach ( $ordered_families as $ordered_family ) {

				$aws->setCellValue( 'B' . strval( $current_line ), $ordered_family['family']->name );
				$aws->getStyle( 'B' . strval( $current_line ) )->getFont()->setSize( 18 );
				$aws->mergeCells( 'B' . strval( $current_line ) . ':I' . strval( $current_line ) );
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

						$qty_unit = 'N\D';

						try {
							$qty_unit = get_post_meta( $material['product']->get_id(), 'unit', true );
						} catch ( Exception $e ) {
							$qty_unit = 'N\D';
						}

						$aws->setCellValue( 'B' . strval( $current_line ), $material['order'] );
						$aws->setCellValue( 'C' . strval( $current_line ), $material['product']->get_sku() );
						$aws->getCell( 'C' . strval( $current_line ) )->getHyperlink()->setUrl( $material['product']->get_permalink() );
						$aws->setCellValue( 'D' . strval( $current_line ), $ordered_family['family']->name );
						$aws->setCellValue( 'E' . strval( $current_line ), $ordered_category['category']->name );
						$aws->setCellValue( 'F' . strval( $current_line ), $material['cart_item']->get_name() );
						$aws->setCellValue( 'G' . strval( $current_line ), $material['cart_item']->get_quantity() );
						$aws->setCellValue( 'H' . strval( $current_line ), $qty_unit );
						if ( $material['availability'] != false ) {
							$aws->setCellValue( 'I' . strval( $current_line ), $material['availability'] );
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
			$aws->getStyle( 'B' . $deposit_start_line . ':I' . $current_line )->applyFromArray( $style_array );
			++$current_line;
		}
	}

	protected function get_and_filter_items( $deposit ) {

		$items = array();

		$deposit_orders = get_post_meta( $deposit->ID, 'refair_orders', true );

		$deposit_ref = get_post_meta( $deposit->ID, 'reference', true );

		foreach ( $deposit_orders as $deposit_order ) {

			$order_materials = wc_get_order( $deposit_order )->get_items();

			$filtered_order_materials = array_values(
				array_filter(
					$order_materials,
					function ( $material ) use ( $deposit_ref ) {
						if ( $deposit_ref === get_post_meta( $material->get_product_id(), 'deposit', true ) ) {
							return true;
						}
						return false;
					}
				)
			);

			$items = array_merge(
				$items,
				array_map(
					function ( $material ) use ( $deposit_order ) {

						return array(
							'order'    => $deposit_order,
							'material' => $material,
						);
					},
					$filtered_order_materials
				)
			);
		}

		return $items;
	}

	private function sort_order_items( $order_items ) {
		$ordered_materials = array();
		foreach ( $order_items as $item ) {
			$item['material']->get_id();
			$product      = wc_get_product( $item['material']->get_product_id() );
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
					'order'        => $item['order'],
					'product'      => $product,
					'cart_item'    => $item['material'],
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
