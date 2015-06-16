<?php
/**
 * MarketMuse Related Keywords
 *
 * @package   MM_Related_Keywords_Admin
 * @author    Javier Villanueva <javier@vivwebsolutions.com>
 * @copyright 2014 ViV Web Solutions
 */

/**
 * Plugin class. This class is used to work with the administrative side
 * of the WordPress site.
 *
 * @package MM_Related_Keywords_Admin
 * @author  Javier Villanueva <javier@vivwebsolutions.com>
 */
class MM_Related_Keywords_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = MM_Related_Keywords::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add meta boxes to edit post/pages.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Highlight keywords on tinyMCE.
		add_filter( 'tiny_mce_before_init', array( $this, 'highlight_keywords' ) );

		// Save keywords to post/page metadata
		add_action( 'save_post', array( $this, 'save_keywords' ) );

		// Change admin footer
		add_filter( 'admin_footer_text', array( $this, 'change_footer_admin' ) );

		// Register plugin settings
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		if ( 'post' === $screen->base ) {
			wp_register_style(
				'bootstrap-tooltips',
				plugins_url( 'assets/css/libs/bootstrap.css', __FILE__ ),
				array(),
				MM_Related_Keywords::VERSION
			);

			wp_enqueue_style(
				$this->plugin_slug .'-admin-styles',
				plugins_url( 'assets/css/admin.css', __FILE__ ),
				array( 'bootstrap-tooltips' ),
				MM_Related_Keywords::VERSION
			);
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		if ( 'post' === $screen->base ) {

			global $post;

			wp_register_script(
				'bootstrap-tooltips',
				plugins_url( 'assets/js/libs/bootstrap.js', __FILE__ ),
				array( 'jquery' ),
				MM_Related_Keywords::VERSION
			);

			wp_enqueue_script(
				$this->plugin_slug . '-admin-script',
				plugins_url( 'assets/js/admin.js', __FILE__ ),
				array( 'bootstrap-tooltips' ),
				MM_Related_Keywords::VERSION
			);

			wp_localize_script(
				$this->plugin_slug . '-admin-script',
				'MM_Settings',
				array(
					'buttonFetching'    => __( 'Fetching..', $this->plugin_slug ),
					'buttonSubmit'      => __( 'Analyze', $this->plugin_slug ),
					'headingTopics'     => __( 'Topics', $this->plugin_slug ),
					'headingFrequency'  => __( 'Frequency', $this->plugin_slug ),
					'headingTooltip'    => __( 'Number of times this topic appears in your content', $this->plugin_slug ),
					'focusKeywordCount' => __( 'Focus keyword count', $this->plugin_slug ),
					'relatedTopics'     => __( 'Related topics covered', $this->plugin_slug ),
					'keywords'          => ( get_post_meta( $post->ID, '_mm_keywords', true ) ) ? json_encode( array_keys( get_post_meta( $post->ID, '_mm_keywords', true ) ) ) : '',
					'settings'          => get_option( 'mm_settings' )
				)
			);

		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/**
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'MarketMuse Related Keywords', $this->plugin_slug ),
			__( 'Related Keywords', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add keywords meta box to the side of each post page.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {

		// Get only public post types with UI
		$args = array( 'public' => true, 'show_ui' => true );

		$screens = get_post_types( $args );

		// Exclude attachments
		unset( $screens['attachment'] );

		foreach ( $screens as $screen ) {

			add_meta_box(
				$this->plugin_slug,
				__( 'MarketMuse Related Keywords', $this->plugin_slug ),
				array( $this, 'meta_box_display' ),
				$screen,
				'side',
				'high'
			);

		}

	}

	/**
	 * Render the meta box content.
	 *
	 * @since    1.0.0
	 */
	public function meta_box_display() {
		include_once( 'views/metabox.php' );
	}

	/**
	 * Register highlight javascript function for tinyMCE
	 *
	 * @since    1.0.0
	 * @param    array    $settings
	 * @return   array
	 */
	public function highlight_keywords( $settings ) {
		$settings['init_instance_callback'] = 'mmManageKeywords';
		return $settings;
	}

	/**
	 * Save keywords to post/page if the list is populated
	 *
	 * @since    1.0.0
	 * @param    int      $post_id
	 * @return   void
	 */
	public function save_keywords( $post_id ) {
		// Make sure the user can save the post
		if ( $this->user_can_save( $post_id, $this->plugin_slug . '-nonce' ) && ( ! empty( $_POST['mm-keyword-list'] ) ) ) {

			// Separate keywords by commas
			$keywords         = explode( ',', $_POST['mm-keyword-list'] );
			$display_keywords = array();

			// Count every occurrence of each keyword
			foreach ( $keywords as $index => $keyword ) {

				if ( ( $count = substr_count( strtolower( $_POST['post_content'] ), strtolower( $keyword ) ) ) > 0 ) {
					$display_keywords[ $keyword ] = $count;
				} else {
					$display_keywords[ $keyword ] = 0;
				}

			}

			// Save them to the post/page
			update_post_meta( $post_id, '_mm_keywords', $display_keywords );
		}
	}

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @since    1.0.0
	 * @param    int      $post_id
	 * @param    bool
	 * @return   bool
	 */
	private function user_can_save( $post_id, $nonce ) {

		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], $this->plugin_slug ) );

		// Return true if the user is able to save; otherwise, false.
		return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	}

	/**
	 * Change admin footer when displaying the plugin's settings page
	 *
	 * @since    1.0.0
	 * @param    string   $footer_text
	 * @return   string
	 */
	public function change_footer_admin( $footer_text ) {
		$text = '';

		$screen = get_current_screen();

		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			$text .= sprintf( __( 'Plugin development and design by <a href="%s" target="_blank">ViV Web Solutions</a>', $this->plugin_slug ), 'http://vivwebsolutions.com/' );
			$text .= '<br>';
		}

		echo $text . $footer_text;
	}

	/**
	 * Initialize settings API
	 *
	 * @since    1.1.0
	 * @return   void
	 */
	public function init_settings() {

		register_setting( 'mm_settings_page', 'mm_settings' );

		add_settings_section(
			'mm_settings_section',
			'',
			'',
			'mm_settings_page'
		);

		add_settings_field(
			'public_token',
			__( 'API Key', $this->plugin_slug ),
			array( $this, 'mm_fields_render' ),
			'mm_settings_page',
			'mm_settings_section'
		);

	}

	/**
	 * Render setting fields
	 *
	 * @since    1.1.0
	 * @return   string
	 */
	public function mm_fields_render() {
		$options = get_option( 'mm_settings' );
	?>
		<input type="text" name="mm_settings[public_token]" value="<?php echo $options['public_token']; ?>" class="regular-text">
		<p class="description"><?php printf( __( 'To request an API key, please visit <a href="%s" target="_blank">marketmuse.com</a> and Contact Us', $this->plugin_slug ), 'https://marketmuse.com/' ); ?></p>
	<?php
	}

}
