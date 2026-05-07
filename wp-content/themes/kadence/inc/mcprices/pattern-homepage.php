<?php
/**
 * Editable homepage block pattern content for the McPrices USA layout.
 *
 * The heavier designed sections are rendered through mcprices_home_section
 * shortcodes so the Home page remains manageable in the block editor.
 *
 * @package kadence
 */

$current_year = wp_date( 'Y', null, wp_timezone() );

$page_url = static function ( $path ) {
	$path = trim( (string) $path, '/' );

	return '' === $path ? './' : './' . $path . '/';
};

$interactive_tools = array(
	array(
		'slug'        => 'budget-finder',
		'icon'        => '&#128176;',
		'short_title' => 'Budget Finder',
		'url'         => $page_url( 'budget-finder' ),
	),
	array(
		'slug'        => 'calorie-calculator',
		'icon'        => '&#128290;',
		'short_title' => 'Calorie Builder',
		'url'         => $page_url( 'calorie-calculator' ),
	),
	array(
		'slug'        => 'compare-items',
		'icon'        => '&#9878;',
		'short_title' => 'Compare Items',
		'url'         => $page_url( 'compare-items' ),
	),
);

ob_start();
?>
<!-- wp:group {"className":"mcprices-page mcprices-managed-homepage","layout":{"type":"default"}} -->
<div class="wp-block-group mcprices-page mcprices-managed-homepage">
<!-- wp:html -->
<div class="mcprices-pattern-version-marker" data-mcprices-pattern-version="3.4.1" hidden></div>
<!-- /wp:html -->

<!-- wp:group {"tagName":"section","className":"hero","layout":{"type":"default"}} -->
<section class="wp-block-group hero">
<!-- wp:html -->
<div class="hero-bg"></div>
<div class="hero-pattern"></div>
<!-- /wp:html -->

<!-- wp:group {"className":"hero-content","layout":{"type":"default"}} -->
<div class="wp-block-group hero-content">
<!-- wp:group {"className":"hero-left animate-fadeup","layout":{"type":"default"}} -->
<div class="wp-block-group hero-left animate-fadeup">
<!-- wp:paragraph {"className":"hero-eyebrow"} -->
<p class="hero-eyebrow">Updated April <?php echo esc_html( $current_year ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"hero-title"} -->
<h1 class="wp-block-heading hero-title">McDonald's Menu<br>Prices <span class="highlight">USA <?php echo esc_html( $current_year ); ?></span></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"hero-sub"} -->
<p class="hero-sub">The most complete and up-to-date McDonald's USA price list. Compare current dollar prices, calories, combo meals, McValue savings, breakfast picks, McCafe drinks, sauces, desserts, and limited-time menu items in one place.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"hero-sub"} -->
<p class="hero-sub">Thousands of readers trust this page to compare accurate McDonald&rsquo;s menu prices, calories, and value deals before they order.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<div class="mcprices-hero-search-placeholder" data-mcprices-hero-search-placeholder="1"></div>
<div class="hero-btns">
	<a href="#full-menu" class="btn-primary">&#127828; View Full Menu</a>
	<a href="#deals" class="btn-outline">&#127991;&#65039; Value Deals</a>
	<a href="<?php echo esc_attr( $page_url( 'budget-finder' ) ); ?>" class="btn-outline" data-home-tool-trigger="budget-finder" data-home-tool-scroll="1">&#128736;&#65039; Interactive Tools</a>
</div>
<div class="mcprices-hero-tool-strip" aria-label="Homepage interactive tools">
	<?php foreach ( $interactive_tools as $index => $tool ) : ?>
		<a
			class="mcprices-hero-tool-strip__link<?php echo 0 === $index ? ' is-active' : ''; ?>"
			href="<?php echo esc_attr( $tool['url'] ); ?>"
			data-home-tool-trigger="<?php echo esc_attr( $tool['slug'] ); ?>"
			data-home-tool-tab="<?php echo esc_attr( $tool['slug'] ); ?>"
			data-home-tool-scroll="1"
		>
			<span class="mcprices-hero-tool-strip__icon" aria-hidden="true"><?php echo $tool['icon']; ?></span>
			<span><?php echo esc_html( $tool['short_title'] ); ?></span>
		</a>
	<?php endforeach; ?>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[mcprices_home_section section="hero-right"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:shortcode -->
[mcprices_home_section section="breadcrumbs"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="full-menu"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="categories"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="whats-new"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="interactive-tools"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="featured-menu"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="deals"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="ordering"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="delivery"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="calories"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="seo"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="hours"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="quality"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="history"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="guides"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="faq"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[mcprices_home_section section="footer-note"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
<?php

return trim( ob_get_clean() );
