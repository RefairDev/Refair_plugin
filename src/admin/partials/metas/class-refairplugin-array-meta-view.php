<?php
/**
 * The admin-partials view of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin/meta_views
 */

namespace Refairplugin\Metas\Views;

use Refairplugin\Refairplugin_Meta_View;
/**
 * Class managing array meta view display and saving.
 */
class Refairplugin_Array_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Number of column of the array.
	 *
	 * @var integer
	 */
	protected $col_n = 0;

	/**
	 * Number of row of the array.
	 *
	 * @var integer
	 */
	protected $row_n = 0;

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'array';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
	}

	/**
	 * Get html content of the meta box.
	 *
	 * @param  string $view_content Previous content of the view of post meta boxes.
	 * @param  array  $data Data to use to generate metabox content.
	 * @param  mixed  $value Value to set to metabox inputs.
	 * @return string Content of view added with the current metabox.
	 */
	public function get_view( $view_content, $data, $value = null ) {

		if ( ! is_array( $value ) ) {
			/* Full fill of the meta with dummy*/
			$dummy_col = array_fill( 0, $this->col_n, '' );
			$value     = array_fill( 0, $this->row_n, $dummy_col );
		} else {
			/* complete rows number*/
			if ( $this->row_n >= count( $value ) ) {
				array_merge( $value, array_fill( 0, $this->row_n - count( $value ), array_fill( 0, $this->col_n - 1, '' ) ) );
			}
			/* complete col number*/
			for ( $meta_row_idx = 0; $meta_row_idx < $this->row_n; $meta_row_idx++ ) {
				if ( $this->col_n >= count( $value[ $meta_row_idx ] ) ) {
					array_merge( $value[ $meta_row_idx ], array_fill( 0, $this->col_n - count( $value[ $meta_row_idx ] ), '' ) );
				}
			}
		}
		ob_start();
		?>

		<table>
			<thead>
				<tr>
					<?php
					for ( $i = 0; $i < $this->col_n; $i++ ) :
						echo wp_kses_post( '<th>Col #' . ( $i + 1 ) . '</th>' );
						endfor;
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				for ( $i = 0; $i < $this->row_n; $i++ ) :
					?>
							<tr>
							<?php
							for ( $j = 0; $j < $this->col_n; $j++ ) :
								?>
										<td>
											<input 
											type="text" 
											name="<?php echo esc_attr( "{$data['name']}[{$i}][{$j}]" ); ?>" 
											id="<?php echo esc_attr( "{$data['id']}[{$i}][{$j}]" ); ?>" 
											class="meta-video regular-text" 
											value="<?php echo wp_kses_post( $value[ $i ][ $j ] ); ?>"/>
										</td>
									<?php
								endfor;
							?>
							</tr>
						<?php
					endfor;
				?>
			</tbody>
		</table>

		<?php
		$current_view = ob_get_clean();
		return $view_content . $current_view;
	}
}
