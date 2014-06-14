<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   MM_Related_Keywords
 * @author    Javier Villanueva <javier@vivwebsolutions.com>
 * @copyright 2014 ViV Web Solutions
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<p>
		<strong><?php _e( 'Plugin powered by', $this->plugin_slug ); ?></strong>
		<br>
		<img src="<?php echo plugins_url( 'assets/img/logo.jpg', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'MarketMuse Logo', $this->plugin_slug ); ?>" width="200" height="45">
		<br>
		<?php printf( __( 'Boost organic traffic for your SEO and content marketing! Visit <a href="%s" target="_blank">marketmuse.co</a>', $this->plugin_slug ), 'http://marketmuse.co/' ); ?>
	</p>

</div>
