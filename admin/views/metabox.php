<?php
/**
 * Represents the view for the individual meta boxes
 *
 * @package   MM_Related_Keywords
 * @author    Javier Villanueva <javier@vivwebsolutions.com>
 * @copyright 2014 ViV Web Solutions
 */

global $post;

$keywords = get_post_meta( $post->ID, '_mm_keywords', true );
?>

<p class="howto">
	<?php _e( 'Enter a keyword that this post covers.', $this->plugin_slug ); ?>
</p>

<div class="mm-clearfix">
	<input type="text" id="mm-keywords" name="mm-keywords" value="<?php echo is_array( $keywords ) ? key( $keywords ) : ''; ?>">
	<input type="button" id="mm-keywords-submit" class="button" value="<?php _e( 'Analyze', $this->plugin_slug ); ?>" />
	<?php wp_nonce_field( $this->plugin_slug, $this->plugin_slug . '-nonce' ); ?>
</div>

<div id="mm-results">
	<?php
		$i = 0;

		$content  = get_post_field( 'post_content', $post->ID );
		$words    = str_word_count( strip_tags( $content ) );

		if ( ! empty( $keywords ) ) :

			function onArray($value) { return $value > 0; }
			$on_array = array_filter( $keywords, 'onArray' );
	?>

	<table class="mm-results-table">

		<thead>
			<tr>
				<td colspan="2">
					<?php _e( 'Focus keyword count', $this->plugin_slug ); ?>: <span id="mm-focus-count"><?php echo array_shift( array_values( $keywords ) ); ?></span> (<span id="mm-focus-percent"><?php echo round( ( array_shift( array_values( $keywords ) ) / $words ) * 100 ); ?></span>%)
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php _e( 'Related topics covered', $this->plugin_slug ); ?>: <span id="mm-keywords-exist"><?php echo count( $on_array ); ?></span> / <span id="mm-keywords-total"><?php echo count( $keywords ); ?></span>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Topics', $this->plugin_slug ); ?></th>
				<th data-toggle="tooltip" data-placement="top" title="<?php _e( 'Number of times this topic appears in your content', $this->plugin_slug ); ?>">
					<?php _e( 'Frequency', $this->plugin_slug ); ?>
					<span class="dashicons dashicons-editor-help"></span>
				</th>
			</tr>
		</thead>

		<tbody>

	<?php foreach ( $keywords as $keyword => $count ) : ?>

		<tr class="<?php echo ( $i++ % 2 === 0 ) ? 'mm-odd' : ''; ?>">
			<td><?php echo esc_html( $keyword ); ?></td>
			<td
				class="mm-count <?php echo ( $count > 0 && $count <= 8 ) ? 'mm-checkmark' : ''; ?> <?php echo ( $count > 8 ) ? 'mm-warning' : ''; ?>"
				data-mm-keyword="<?php echo esc_attr( $keyword ); ?>"
			>
				<?php echo ( $count > 0 && $count <= 8 ) ? '&#x2713;' : ''; ?>
				<?php echo ( $count > 0 ) ? esc_html( $count ) : '&mdash;'; ?>
			</td>
		</tr>

	<?php endforeach; ?>

		</tbody>

	</table>

	<input type="hidden" name="mm-keyword-list" value="<?php echo join( ',', array_keys( $keywords ) ); ?>">

	<?php endif; ?>
</div>

<?php
	$options = get_option( 'mm_settings' );

	if ( empty( $options['public_token'] ) ) :
?>
<p>
	<small>
		<?php _e( 'Want to unlock 50 suggestions?', $this->plugin_slug ); ?><br>
		<?php printf( __( 'To get API key, please contact us at <a href="%s" target="_blank">MarketMuse.com</a>', $this->plugin_slug ), 'https://www.marketmuse.com/' ); ?><br>
		<?php printf( __( 'Enter the key on our <a href="%s">settings page</a>.', $this->plugin_slug ), admin_url( 'options-general.php?page=' . $this->plugin_slug ) ); ?>
	</small>
</p>
<?php endif; ?>
