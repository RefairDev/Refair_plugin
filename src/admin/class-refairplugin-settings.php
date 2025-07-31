<?php
/**
 * The admin-specific settings of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

use Refairplugin\Refairplugin_Utils;
use Refairplugin\SettingView;

/**
 * Class settting up all settings of the plugin.
 */
class Refairplugin_Settings {



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


	private static $setting_classes = array();


	private static $settings_views_namespace = '\\Refairplugin\\Settings\\Views\\';

	/**
	 * Plugin settings page slug
	 *
	 * @var string
	 */
	private $refairplugin_settings_page_slug = 'refairplugin-extras-settings';



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->construct_settings_views();
	}

	/**
	 * Add settings page to global admin menu
	 */
	public function add_settings_page() {
		add_options_page(
			$this->plugin_name . ' ' . __( 'Settings', 'refair-plugin' ),
			$this->plugin_name,
			'manage_options',
			$this->refairplugin_settings_page_slug,
			array( $this, 'build_settings_page' )
		);
	}

	/**
	 * Gather all instance of settings views builders.
	 *
	 * @return void
	 */
	protected function construct_settings_views() {
		$settings_views_dir = plugin_dir_path( __DIR__ ) . 'admin/partials/settings';

		$files = Refairplugin_Utils::require( $settings_views_dir );

		foreach ( $files as $file ) {
			$classes = Refairplugin_Utils::file_get_php_classes( $file );
			foreach ( $classes as $class ) {
				try {
					$full_class_name = self::$settings_views_namespace . $class;
					if ( property_exists( $full_class_name, 'type' ) ) {
						self::$setting_classes[ $full_class_name::$type ] = $full_class_name::get_instance();
					}
				} catch ( \Exception $exception ) {
					trigger_error( 'Setting control has no type:' . $full_class_name );
				}
			}
		}
	}

	/**
	 * Get data to build settings page.
	 *
	 * @return array All data to build settings.
	 */
	protected function get_ui_data() {

		return array(
			array(
				'slug'     => 'mainsettings',
				'name'     => __( 'Global settings', 'refair-plugin' ),
				'is_tab'   => true,
				'save'     => true,
				'settings' => array(
					array(
						'name'        => 'google_api_key',
						'label'       => __( 'Google API key', 'refair-plugin' ),
						'type'        => 'text',
						'description' => '',
						'options'     => array(),
					),
				),
			),
			array(
				'slug'     => 'refair-plugin-actions',
				'name'     => __( 'Actions', 'refair-plugin' ),
				'is_tab'   => false,
				'save'     => false,
				'settings' => array(
					array(
						'name'        => 'insee_code',
						'label'       => __( 'Code INSEE', 'refair-plugin' ),
						'type'        => 'action',
						'description' => '',
						'options'     => array(
							'button_text'  => __( 'Get all INSEE code', 'refair-plugin' ),
							'button_class' => 'button button-secondary',
							'url'          => admin_url( 'admin-ajax.php?action=refairplugin_get_all_insee_codes' ),
						),
					),
				),
			),

		);
	}

	/**
	 * Build settings page
	 */
	public function build_settings_page() {

		if ( ! empty( $_REQUEST ) ) {
			if ( isset( $_REQUEST['submit'] ) ) {
				check_admin_referer( plugin_basename( REFAIRPLUGIN_ROOT_FILE ), 'refairplugin_options' );
				$this->update_refairplugin_options( $_REQUEST );

			}
		}

		echo "<div class='postbox-container' style='display:block;width:100%;'>";
		echo "<form method='post'>";

		wp_nonce_field( plugin_basename( REFAIRPLUGIN_ROOT_FILE ), 'refairplugin_options' );

		$display_save_button = true;

		$active_tab = 'mainsettings';
		if ( isset( $_REQUEST['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$active_tab = $_REQUEST['tab']; // phpcs:ignore WordPress.Security.NonceVerification
		}

		printf( "<input type='hidden' name='tab' value='%s' />", esc_attr( $active_tab ) );

		$this_page = '?page=' . $this->refairplugin_settings_page_slug;

		/**
		 * Allows adding new tabs to the refairplugin menu.
		 *
		 * @param array $tabs An array of arrays defining the tabs.
		 *
		 * @return array Filtered tab array.
		 */
		$tabs = apply_filters( 'refairplugin_tabs', $this->get_ui_data() );
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			array_walk(
				$tabs,
				function ( $tab ) use ( $this_page, $active_tab ) {
					if ( $tab['is_tab'] ) {
						?>
						<a href="<?php echo esc_attr( $this_page ); ?>&amp;tab=<?php echo esc_attr( $tab['slug'] ); ?>"
						class="nav-tab <?php echo esc_attr( $tab['slug'] === $active_tab ? 'nav-tab-active' : '' ); ?>">
						<?php echo esc_html( $tab['name'] ); ?></a>
						<?php
					}
				}
			);
		?>
		</h2>
	
		<?php
		$current_tab = $tabs[ array_search( $active_tab, wp_list_pluck( $tabs, 'slug' ), true ) ];
		if ( ! $current_tab['save'] ) {
			$display_save_button = false;
		}

		$this->build_tab_content( $current_tab );
		// if ( $current_tab['require'] ) {
		// require_once $current_tab['require'];
		// }
		// call_user_func( $current_tab['callback'] );

		if ( $display_save_button ) :
			?>
	
		<input type='submit' name='submit' value='<?php esc_attr_e( 'Save the options', 'refair-plugin' ); ?>' class='button button-primary' />
	
		<?php endif; ?>
	
		</form>

		<?php
		array_walk(
			$tabs,
			function ( $tab ) {
				if ( false === $tab['is_tab'] ) {

					?>
					<h2 id="<?php echo $tab['slug']; ?>"><?php echo $tab['name']; ?></h2>
					<div id="<?php echo $tab['slug']; ?>-settings">
						<?php
						array_walk(
							$tab['settings'],
							function ( $setting ) {
								$setting_view = self::$setting_classes[ $setting['type'] ];
								$data_view    = $setting_view->get_data_view( '', $setting );
								$heading_view = $setting_view->get_heading_view( '', $setting );
								ob_start();
								?>
								<table>
									<tr>
										<td>
											<?php echo '<h3>' . esc_html( $heading_view ) . '</h3>'; ?>
										</td>
										<td>
											<?php echo $data_view; ?>
										</td>
									</tr>
								</table>
								<?php
								$setting_view = ob_get_clean();
								echo $setting_view;
							}
						);
					?>
						</div>
						<?php
				}
			}
		);
		?>
		<?php
	}

	protected function build_tab_content( $tab_data ) {

		?>
		<div id="<?php echo $tab_data['slug']; ?>_tab">
			<table class="form-table" role='presentation'>
				<tbody>
					<?php
					foreach ( $tab_data['settings'] as $setting ) {

						$setting_default_value = null;

						if ( array_key_exists( 'default', $setting ) ) {
							$setting_default_value = $setting['default'];
						}

						if ( null === $setting_default_value ) {
							apply_filters(
								'refairplugin_get_default_value_' . $setting['type'],
								$setting_default_value,
							);
						}

						$setting_value = get_option(
							$setting['name'],
							$setting_default_value
						);

						?>
						<tr>
							<th>
							<?php
							$heading_view = '';
							$heading_view = apply_filters(
								'refairplugin_render_heading_setting_view_' . $setting['type'],
								$heading_view,
								$setting,
							);
							echo $heading_view;

							?>
							</th>
							<td>
							<?php
							$data_view = '';
							$data_view = apply_filters(
								'refairplugin_render_data_setting_view_' . $setting['type'],
								$data_view,
								$setting,
								$setting_value
							);
							echo $data_view;
							?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}


	protected function update_refairplugin_options( array $request ) {

		$options = array(
			'google_api_key' => true,
		);

		array_walk(
			$options,
			function ( $autoload, $option ) use ( $request ) {
				if ( isset( $request[ $option ] ) ) {
					update_option( $option, $request[ $option ], $autoload );
				}
			}
		);
	}
}
