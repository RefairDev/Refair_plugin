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

/**
 * Class managing xls files gneration.
 */
class Refairplugin_Xls_Generator {

	/**
	 * The static part of xls filename.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $generator_inputs    Input used to generate the xls
	 */
	private Refairplugin_Files_Generator_Input $generator_inputs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      Refairplugin_Files_Generator_Input $generator_inputs  inputs used to generate the xls.
	 */
	public function __construct( Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->set_inputs( $generator_inputs );

		$this->initialize_generator();

		return $this;
	}

	/**
	 * Initialize xls generator
	 *
	 * @return void
	 */
	private function initialize_generator() {
	}

	/**
	 * Record generator inputs.
	 *
	 * @param  Refairplugin_Files_Generator_Input $generator_inputs Inputs used for document generation.
	 * @return void
	 */
	private function set_inputs( Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->generator_inputs = $generator_inputs;
	}

	/**
	 * Generate xls accoding to previous initializations.
	 *
	 * @return array path and filename infos
	 */
	public function generate_xls() {

		$spreadsheet = new Spreadsheet();

		$this->get_xls_content( $spreadsheet );

		$writer = new Xlsx( $spreadsheet );

		$path = '';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$path = $this->get_file_directory();

		$filename = $this->generator_inputs->get_xls_filename();
		$writer->save( $path . $filename );

		return array(
			'path'      => $path,
			'filename'  => $filename,
			'full_path' => $path . $filename,
		);
	}

	/**
	 * Fill current edited spreadsheet with content of document template generated.
	 *
	 * @param  Spreadsheet $ss Object representing currently edited xls spreadsheet.
	 * @return void Content for the xls.
	 */
	public function get_xls_content( Spreadsheet $ss ) {

		switch ( $this->generator_inputs->get_template_part_name() ) {
			case 'deposit_orders':
				( new Refairplugin_Xls_Template_Deposit_Orders( $ss, $this->generator_inputs ) )->get_xls_content();
				break;
			case 'deposit':
				( new Refairplugin_Xls_Template_Deposit( $ss, $this->generator_inputs ) )->get_xls_content();
				break;
			case 'order':
				( new Refairplugin_Xls_Template_Order( $ss, $this->generator_inputs ) )->get_xls_content();
				break;
		}
	}

	/**
	 * Get file directory for generation pourpose.
	 *
	 * @return string Directory path.
	 */
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
