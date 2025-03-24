<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin;

use Refairplugin\Refairplugin_Files_Generator_Input;

/**
 * Class used by other classes for generic actions.
 */
class Refairplugin_Utils {

	/**
	 * Requires recursively files.
	 *
	 * @param  string $dir Diretory to require.
	 * @param  array  $exclude Exclude files.
	 * @return array Files that have been required.
	 */
	public static function require( $dir, $exclude = array() ) {
			$files_returned = array();
			$files          = array_diff( scandir( $dir, 1 ), array( '.', '..', 'index.php' ) );

		foreach ( $files as $file ) {
			if ( ! in_array( $file, $exclude, true ) && ! in_array( basename( $file ), $exclude, true ) ) {
				if ( ! is_dir( $dir . '/' . $file ) ) {
					$returned         = require_once $dir . '/' . $file;
					$files_returned[] = $dir . '/' . $file;
				} else {
					self::require( $dir . '/' . $file, $exclude = array() );
				}
			}
		}
			return $files_returned;
	}

	/**
	 * Convert string to UTF-8.
	 *
	 * @param  string $str_to_convert String to convert.
	 * @return string String converted to UTF-8.
	 */
	public static function convert_string( $str_to_convert ) {
		static $use_mb = null;

		if ( is_null( $use_mb ) ) {
			$use_mb = function_exists( 'mb_convert_encoding' );
		}

		if ( $use_mb ) {
			$encoding = mb_detect_encoding( $str_to_convert, mb_detect_order(), true );
			if ( $encoding ) {
				return mb_convert_encoding( $str_to_convert, 'UTF-8', $encoding );
			} else {
				return mb_convert_encoding( $str_to_convert, 'UTF-8', 'UTF-8' );
			}
		} else {
			return $str_to_convert;
		}
	}

	/**
	 * Write file with parameter content.
	 *
	 * @param  string $file Filepath.
	 * @param  string $content Content to write.
	 * @return void
	 */
	public static function fwrite( $file, $content ) {
		$fd = fopen( $file, 'w' );

		$content_length = strlen( $content );

		if ( false === $fd ) {
			throw new Exception( 'File not writable.', 1 );
		}

		$written = fwrite( $fd, $content );

		fclose( $fd );

		if ( false === $written ) {
			throw new Exception( 'Unable to write to file.', 1 );
		}
	}

	/**
	 * Get classes in a file.
	 *
	 * @param  string $filepath Filepath.
	 * @return array Classes found in file in parameters.
	 */
	public static function file_get_php_classes( $filepath ) {
		$php_code = file_get_contents( $filepath );
		$classes  = self::get_php_classes( $php_code );
		return $classes;
	}

	/**
	 * Get classes from php code.
	 *
	 * @param  string $php_code php code read from a file.
	 * @return array Classes found in php code.
	 */
	public static function get_php_classes( $php_code ) {
		$classes = array();
		$tokens  = token_get_all( $php_code );
		$count   = count( $tokens );
		for ( $i = 2; $i < $count; $i++ ) {
			if ( T_CLASS === $tokens[ $i - 2 ][0]
				&& T_WHITESPACE === $tokens[ $i - 1 ][0]
				&& T_STRING === $tokens[ $i ][0] ) {

				$class_name = $tokens[ $i ][1];
				$classes[]  = $class_name;
			}
		}
		return $classes;
	}

	/**
	 * Create filename and paths for expression of interest zip archive generation.
	 *
	 * @param  int $m_id Id of the expression of interest whom archive is to zip.
	 * @return array Array of paths and filename informations.
	 */
	public static function refair_build_material_filename_data( $m_id ) {

		$material_path = str_replace( '\\', '/', wp_upload_dir()['basedir'] . '/materials/' . strval( $m_id ) . '/' );

		$args = array(
			'id'                 => $m_id,
			'destination'        => 'F',
			'public_type_name'   => __( 'Material', 'refair-plugin' ),
			'folder_name'        => 'materials',
			'template_part_name' => 'material',
			'reference'          => wc_get_product( $m_id )->get_sku(),
		);

		$inputs                  = new Refairplugin_Files_Generator_Input( $args );
		$material_sheet_filename = $inputs->get_pdf_filename();

		return array(
			'path'      => $material_path,
			'filename'  => $material_sheet_filename,
			'full_path' => $material_path . $material_sheet_filename,
		);
	}

	/**
	 * Build array with filename data.
	 *
	 * @param  int $id Deposit id.
	 * @return array Array with filename data.
	 */
	public static function refair_build_deposit_archive_filename_data( $id ) {

		$folder = '/deposits_archives/';

		/* make EoI directory */
		$path = str_replace( '\\', '/', wp_upload_dir()['basedir'] . $folder );
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
			chmod( $path, 0774 );
		}
		$root_url = wp_upload_dir()['baseurl'] . $folder;

		/* build filename */

		$args = array(
			'id'                 => $id,
			'title'              => sanitize_file_name( get_the_title( $id ) ),
			'destination'        => 'F',
			'public_type_name'   => __( 'Deposit', 'refair-plugin' ),
			'folder_name'        => 'desposits',
			'template_part_name' => 'deposit',
		);

		$ref = get_post_meta( $id, 'reference', true );

		if ( false !== $ref ) {
			$args['reference'] = $ref;
		}

		$inputs       = new Refairplugin_Files_Generator_Input( $args );
		$zip_filename = $inputs->get_basename() . '.zip';

		return array(
			'path'      => $path,
			'filename'  => $zip_filename,
			'full_path' => $path . $zip_filename,
			'full_url'  => $root_url . $zip_filename,
		);
	}

	/**
	 * Check if material file exists.
	 *
	 * @param  int $id material post ID.
	 * @return boolean true is existing | false not existing.
	 */
	public static function is_material_file_exists( $id ) {

		$is_existing = false;
		$status      = true;

		$m_obj = wc_get_product( $id );

		/* the material don't exist so file don't exist, no further search. */
		if ( false === $m_obj || null === $m_obj ) {
			return $is_existing;
		}

		$zip_data = self::refair_build_material_filename_data( $id );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$is_existing = file_exists( $zip_data['path'] . $zip_data['filename'] );
		}

		return $is_existing;
	}

	/**
	 * Check if deposit file exists.
	 *
	 * @param  int $id deposit post ID.
	 * @return boolean true is existing | false not existing.
	 */
	public static function is_deposit_archive_file_exists( $id ) {
		$is_existing = false;
		$status      = true;
		$zip_data    = self::refair_build_deposit_archive_filename_data( $id );

		if ( false === $zip_data ) {
			$status = false;
		}

		if ( true === $status ) {
			$is_existing = file_exists( $zip_data['path'] . $zip_data['filename'] );
		}

		return $is_existing;
	}

	/**
	 * Get deposit WP_post object according to metakey deposit reference
	 *
	 * @param  string $reference deposit reference.
	 * @return mixed WP_Post | false return Deposit WP_Post or false.
	 */
	public static function refair_get_deposit_by_ref( $reference ) {

		$found_posts = false;
		if ( ! empty( $reference ) ) {
			$found_posts_raw = get_posts(
				array(
					'post_type'  => 'deposit',
					'meta_key'   => 'reference',
					'meta_value' => $reference,
				)
			);

			if ( is_array( $found_posts_raw ) ) {
				$found_posts = $found_posts_raw[0];
			}
		}

		return $found_posts;
	}
}
