<?php
/**
 * The pdf generator functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Refairplugin\Refairplugin_Files_Generator_Input;

/**
 *  Pdf Generator class
 */
class Refairplugin_Pdf_Generator {

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
	 * The static part of pdf filename.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $generator_inputs    Input used to generate the pdf
	 */
	private Refairplugin_Files_Generator_Input $generator_inputs;


	/**
	 * Local instance of mpdf.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $mpdf    The current version of this plugin.
	 */
	private $mpdf;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  Refairplugin_Files_Generator_Input $generator_inputs Parameters to generate file.
	 */
	public function __construct( Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->set_inputs( $generator_inputs );

		$this->initialize_generator();

		return $this;
	}

	/**
	 * Initialize pdf generator with fonts, display mode, debug mode
	 *
	 * @return void
	 */
	private function initialize_generator() {
		$default_config    = ( new \Mpdf\Config\ConfigVariables() )->getDefaults();
		$default_font_dirs = $default_config['fontDir'];

		$default_font_config = ( new \Mpdf\Config\FontVariables() )->getDefaults();
		$font_data           = $default_font_config['fontdata'];

		$font_dirs = array_merge(
			$default_font_dirs,
			array(
				plugin_dir_path( __FILE__ ) . 'fonts',
			)
		);

		$mpdf_args = array(
			'fontDir'       => $font_dirs,
			'fontdata'      => $font_data + array(
				'circular_std'      => array(
					'R' => 'Circular Std Book.ttf',
					'I' => 'CircularStd-BookItalic.ttf',
				),
				'circular_std_bold' => array(
					'R' => 'CircularStd-Bold.ttf',
					'I' => 'CircularStd-BoldItalic.ttf',
				),
			),
			'default_font'  => 'circular_std',
			'margin_left'   => 7,
			'margin_right'  => 7,
			'margin_top'    => 8,
			'margin_bottom' => 8,
			'margin_header' => 9,
			'margin_footer' => 9,
			'tempDir'       => WP_CONTENT_DIR . '/mpdf_tmp',
		);

		if ( ! empty( $this->generator_inputs->get_orientation() ) ) {
			$mpdf_args['orientation'] = $this->generator_inputs->get_orientation();
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$mpdf_args['debug']                  = true;
			$mpdf_args['allow_output_buffering'] = false;
		}

		$this->mpdf = new \Mpdf\Mpdf( $mpdf_args );

		$this->mpdf->useSubstitutions = true; // optional - just as an example.
		$this->mpdf->SetDisplayMode( 'fullpage' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$logger = new Logger( 'mpdf_logger' );
			$logger->pushHandler( new StreamHandler( wp_normalize_path( WP_CONTENT_DIR . '/mpdf.log' ), Logger::DEBUG ) );
			$this->mpdf->setLogger( $logger );
		}
	}

	/**
	 * Add footer content to pdf
	 *
	 * @return void
	 */
	public function initialize_pdf_footer() {
		$custom_logo_id  = get_theme_mod( 'custom_logo' );
		$image_src       = wp_get_attachment_image_src( $custom_logo_id, 'medium' );
		$image_ressource = '';
		if ( false !== $image_src ) {
			$image_ressource = $image_src[0];

			$parsed_image_url = wp_parse_url( $image_src[0] );
			if ( is_array( $parsed_image_url ) && array_key_exists( 'path', $parsed_image_url ) && ( ! empty( $parsed_image_url['path'] ) ) ) {
				$image_file = ABSPATH . ltrim( $parsed_image_url['path'], '/' );
				if ( file_exists( $image_file ) ) {
					$image_ressource = $image_file;
				}
			}
		}

		$title = $this->generator_inputs->get_the_title();
		$ref   = $this->generator_inputs->get_reference();
		$date  = $this->generator_inputs->get_edit_date();

		$right_part = "<td width='40%' style='text-align: right;'>{PAGENO}/{nbpg}</td>";
		if ( ! empty( $date ) ) {
			$right_part = "<td width='20%' style='text-align: center;'>{$date}</td><td width='20%' style='text-align: right;'>{PAGENO}/{nbpg}</td>";
		}

		$this->mpdf->DefHTMLFooterByName(
			'defaultFooter',
			"<table class='footer-table' width='100%' height='15px'>
				<tr>
					<td class='left-cell' width='40%'>{$ref} {$title}</td>
					<td width='20%' align='center' style='background-color:#c1d7cd'><img height='15px' src='{$image_ressource}'/></td>
					{$right_part}
				</tr>
			</table>"
		);
	}

	/**
	 * Set inputs to the the pdf generator.
	 *
	 * @param  Refairplugin_Files_Generator_Input $generator_inputs Inputs used to generate pdf.
	 * @return void
	 */
	private function set_inputs( Refairplugin_Files_Generator_Input $generator_inputs ) {

		$this->generator_inputs = $generator_inputs;
	}

		/*  PDF generation from HTML */
	/**
	 * Generate pdf according to previous initializations.
	 *
	 * @return array path and filename infos
	 */
	public function generate_pdf() {

		$path     = '';
		$filename = $this->generator_inputs->get_pdf_filename();

		try {
			$stylesheet = $this->get_stylesheet_content();
			$this->mpdf->WriteHTML( $stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS );
			$this->initialize_pdf_footer();
			$content = $this->get_pdf_content();
			$this->mpdf->WriteHTML( $content, \Mpdf\HTMLParserMode::HTML_BODY );

			$path        = '';
			$destination = $this->generator_inputs->get_file_destination();
			if ( 'F' === $destination ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				$path = $this->get_file_directory();
			}

			error_log( 'build (' . $destination . '): ' . $path . $filename );
			$this->mpdf->Output( $path . $filename, $destination );
		} catch ( \Mpdf\MpdfException $e ) {
			error_log( 'MPDF generation error: ' . $e->getMessage() );
		}
		return array(
			'path'      => $path,
			'filename'  => $filename,
			'full_path' => $path . $filename,
		);
	}

	/**
	 * Get content of the pdf according to inputs.
	 *
	 * @return string Content for the pdf.
	 */
	public function get_pdf_content() {
		$query  = new WP_Query(
			array(
				'p'           => intval( $this->generator_inputs->get_id() ),
				'post_type'   => $this->generator_inputs->get_post_type(),
				'post_status' => array( 'any', 'wc-processing', 'wc-completed' ),
			)
		);
		$output = '';
		if ( $query->have_posts() ) {
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->refair_get_template_part( 'partials/refairplugin-public-print', $this->generator_inputs->get_template_part_name() );
			}
			$output = ob_get_clean();
		}

		wp_reset_postdata();

		return $output;
	}

	/**
	 * Get file directory.
	 *
	 * @return string File directory.
	 */
	private function get_file_directory_name() {

		return $this->generator_inputs->file_directory;
	}


	/**
	 * Get file directory
	 *
	 * @return string Path to the directory.
	 */
	private function get_file_directory() {

		$path = $this->generator_inputs->get_file_directory();

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
			chmod( $path, 0774 );
		} else {
			$files = list_files( $path );
			foreach ( $files as $file ) {
				wp_delete_file( $file );
			}
		}
		return $path;
	}

	/**
	 * Get stylesheet content for génération.
	 *
	 * @return string stylesheet content to use for generation.
	 */
	private function get_stylesheet_content() {
		$stylesheet_filename = $this->generator_inputs->get_stylesheet_file_path();

		return file_get_contents( $stylesheet_filename );
	}


	/**
	 * Get part of template php file should be named [$slug]-[$name].php.
	 *
	 * @param  string $slug Slug of the template part.
	 * @param  string $name Name of the tempalte part.
	 * @return void
	 */
	public function refair_get_template_part( $slug, $name = null ) {

		do_action( "refair_get_template_part_{$slug}", $slug, $name );

		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		$this->refair_get_template_path( $templates, true, false );
	}

	/**
	 * Get templates part on the provided templates names.
	 *
	 * @param  array   $template_names List of templates parts.
	 * @param  boolean $load Is template part has to be loaded.
	 * @param  boolean $rq_once Is template part has to be load once or only this time.
	 * @return string Located template part.
	 */
	public function refair_get_template_path( $template_names, $load = false, $rq_once = true ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}

			/* Search file within the PLUGIN_DIR_PATH only */
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
}
