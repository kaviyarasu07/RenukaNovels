<?php
/** Plain content pages: About Us, Contact Us, FAQ, Privacy Policy, Terms. */
get_header();
the_post();
?>
<div class="rn-wrap">
	<article class="rn-read" style="padding-bottom:0">
		<h1 class="rn-ptitle"><?php the_title(); ?></h1>
		<div class="rn-card rn-prose"><?php the_content(); ?></div>
	</article>
</div>
<?php
get_footer();
