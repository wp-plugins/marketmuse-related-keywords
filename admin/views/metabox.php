<?php
/**
 * Represents the view for the individual meta boxes
 *
 * @package   MM_Related_Keywords
 * @author    Javier Villanueva <javier@vivwebsolutions.com>
 * @copyright 2014 ViV Web Solutions
 */
?>

<p class="howto">
	<?php _e( 'Enter two or more topics that this post covers. Separate topics with commas.', $this->plugin_slug ); ?>
</p>

<div class="mm-clearfix">
	<input type="text" id="mm-keywords" name="mm-keywords">
	<ul id="mm-keyword-tags"></ul>
	<input type="button" id="mm-keywords-submit" class="button" value="<?php _e( 'Analyze', $this->plugin_slug ); ?>" />
	<?php wp_nonce_field( $this->plugin_slug, $this->plugin_slug . '-nonce' ); ?>
</div>

<div id="mm-error"></div>

<div id="mm-results">
	<?php
		global $post;

		$i = 0;

		$keywords = get_post_meta( $post->ID, '_mm_keywords', true );

		if ( ! empty( $keywords ) ) :
	?>

	<table class="mm-results-table">

		<thead>
			<th><?php _e( 'Topics', $this->plugin_slug ); ?></th>
			<th data-toggle="tooltip" data-placement="top" title="<?php _e( 'Number of times this topic appears in your content', $this->plugin_slug ); ?>">
				<?php _e( 'Frequency', $this->plugin_slug ); ?>
				<span class="dashicons dashicons-editor-help"></span>
			</th>
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

<p>
	<small>
		<?php printf( __( 'Visit <a href="%s" target="_blank">MarketMuse.co</a> for more suggestions', $this->plugin_slug ), 'http://marketmuse.co/' ); ?>
	</small>
</p>
