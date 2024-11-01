<?php
get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php
				$whiteboard = showWhiteboard('', true);
				if (!$whiteboard) {
					echo 'No whiteboard for today.';
				} else {
					echo $whiteboard;
				}
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>