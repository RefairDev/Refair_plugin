<?php
/**
 * Instance used as input for file generation.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 */

namespace Refairplugin;

/**
 * Instance Used as input for file generation.
 *
 * This class defines all elements used to generate files (pdf and spredsheet).
 *
 * @since      1.0.0
 * @package    Refairplugin
 * @subpackage Refairplugin/includes
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */
class Refairplugin_Files_Generator_Input {

	/**
	 * Post id associated with file gneration.
	 *
	 * @var int
	 */
	private int $post_id;

	/**
	 * Type of the post which file is generated.
	 *
	 * @var string
	 */
	private string $post_type;

	/**
	 * Title of the file generated.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * Public name of the File generated.
	 *
	 * @var string
	 */
	private string $public_type_name;

	/**
	 * Destination of the content generation.
	 *
	 * @var string
	 */
	private string $destination;

	/**
	 * Filename
	 *
	 * @var string
	 */
	private string $filename;

	/**
	 * Tempalte part used to generate the file.
	 *
	 * @var string
	 */
	private string $template_part_name;

	/**
	 * Name of the directory where file is stored.
	 *
	 * @var string
	 */
	private string $directory_name;

	/**
	 * Name of the folder where file is stored.
	 *
	 * @var string
	 */
	private string $folder_name;

	/**
	 * Static part of the filename.
	 *
	 * @var string
	 */
	private string $static_filename_part;

	/**
	 * Path to the stylesheet.
	 *
	 * @var string
	 */
	private string $stylesheet_file_path;

	/**
	 * Reference for the Post.
	 *
	 * @var string
	 */
	private string $reference = '';

	/**
	 * Edit Date of the Post.
	 *
	 * @var string
	 */
	private string $edit_date = '';

	/**
	 * Orientation of the generated file content (landscape or portrait)
	 *
	 * @var string
	 */
	private string $orientation = 'P';

	/**
	 * Class contructor set internal variables.
	 *
	 * @param  array $args Input arguments.
	 */
	public function __construct( $args = array() ) {

		$this->static_filename_part = 'REFAIR';

		if ( array_key_exists( 'id', $args ) ) {

			$post = get_post( $args['id'] );

			if ( null !== $post ) {
				$this->post_id   = $args['id'];
				$this->title     = get_the_title( $this->post_id );
				$this->post_type = get_post_type( $this->post_id );
			}
		}
		if ( array_key_exists( 'title', $args ) ) {
			$this->title = $args['title']; }
		if ( array_key_exists( 'destination', $args ) ) {
			$this->destination = $args['destination']; }
		if ( array_key_exists( 'public_type_name', $args ) ) {
			$this->public_type_name = $args['public_type_name']; }
		if ( array_key_exists( 'folder_name', $args ) ) {
			$this->folder_name = $args['folder_name']; }
		if ( array_key_exists( 'template_part_name', $args ) ) {
			$this->template_part_name = $args['template_part_name']; }
		if ( array_key_exists( 'reference', $args ) ) {
			$this->reference = $args['reference']; }
		if ( array_key_exists( 'edit_date', $args ) ) {
			$this->edit_date = $args['edit_date']; }
		if ( array_key_exists( 'orientation', $args ) ) {
			$this->orientation = $args['orientation']; }

		$this->stylesheet_file_path = plugin_dir_path( __FILE__ ) . '../print/css/print.css';

		$this->build_filename();
		$this->build_directory_name();
	}

	/**
	 * Accessor to post ID.
	 *
	 * @return int post id.
	 */
	public function get_id() {
		return $this->post_id;
	}

	/**
	 * Accessor to post type.
	 *
	 * @return string Post type.
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Build filename for the générated xls according to input parameters public_type_name/static_filename_part/reference/title.
	 *
	 * @return void
	 */
	private function build_filename() {

		$fn_array = array(
			sanitize_file_name( $this->public_type_name ),
			$this->static_filename_part,
		);

		if ( ! empty( $this->reference ) ) {
			array_push( $fn_array, sanitize_file_name( $this->reference ) );
		}

		if ( ! empty( $this->title ) ) {
			array_push( $fn_array, sanitize_file_name( strval( $this->title ) ) );
		}

		$this->filename = sanitize_file_name( implode( '_', $fn_array ) );
	}

	/**
	 * Build directory path according to DIRECTORY_SEPARATOR.
	 *
	 * @return void
	 */
	private function build_directory_name() {

		if ( DIRECTORY_SEPARATOR === '/' ) {
			$from = '\\';
		}
		if ( DIRECTORY_SEPARATOR === '\\' ) {
			$from = '/';
		}
		$to = DIRECTORY_SEPARATOR;

		$this->directory_name = str_replace( $from, $to, wp_upload_dir()['basedir'] . "/{$this->folder_name}/" . strval( $this->post_id ) . '/' );
	}

	/**
	 * Accessor to directory name.
	 *
	 * @return string Directory name.
	 */
	public function get_file_directory() {
		return $this->directory_name;
	}

	/**
	 * Accessor to template name.
	 *
	 * @return string Template name.
	 */
	public function get_template_part_name() {
		return $this->template_part_name;
	}

	/**
	 * Accessor to title.
	 *
	 * @return string Title.
	 */
	public function get_the_title() {
		return $this->title;
	}

	/**
	 * Accessor to public type name.
	 *
	 * @return string Public type name.
	 */
	public function get_public_type_name() {
		return $this->public_type_name;
	}

	/**
	 * Accessor to basename/filename.
	 *
	 * @return string Basename/filename.
	 */
	public function get_basename() {
		return $this->filename;
	}

	/**
	 * Get filename as a spreadsheet.
	 *
	 * @return string File name as a spreadsheet.
	 */
	public function get_xls_filename() {
		return $this->filename . '.xls';
	}
	/**
	 * Get filename as a pdf.
	 *
	 * @return string Filename as a pdf.
	 */
	public function get_pdf_filename() {
		return $this->filename . '.pdf';
	}

	/**
	 * Accessor to static filename part.
	 *
	 * @return string Static filename part.
	 */
	public function get_static_filename_part() {
		return $this->static_filename_part;
	}

	/**
	 * Accessor to stylesheet file path.
	 *
	 * @return string Stylesheet file path.
	 */
	public function get_stylesheet_file_path() {
		return $this->stylesheet_file_path;
	}

	/**
	 * Accessor to file destination.
	 *
	 * @return string File destination.
	 */
	public function get_file_destination() {
		return $this->destination;
	}

	/**
	 * Accessor to reference.
	 *
	 * @return string Reference.
	 */
	public function get_reference() {
		return $this->reference;
	}

	/**
	 * Accessor to edit date.
	 *
	 * @return string Edit date.
	 */
	public function get_edit_date() {
		return $this->edit_date;
	}

	/**
	 * Accessor to page orientation.
	 *
	 * @return string Page orientation.
	 */
	public function get_orientation() {
		return $this->orientation;
	}
}
