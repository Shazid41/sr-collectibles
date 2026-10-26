<?php
/**
 * Front page — FH Collectibles storefront.
 *
 * @package fh-collectibles
 */

get_header();

$fh_hero_slides = array(
	array(
		'kicker'    => __( 'Precision Die-Cast Metal', 'fh-collectibles' ),
		'lead'      => __( 'Pocket-sized machines,', 'fh-collectibles' ),
		'lead_soft' => __( 'built for serious collectors.', 'fh-collectibles' ),
		'cta'       => __( 'Shop The Drop', 'fh-collectibles' ),
		'title'     => array( __( 'THE ULTIMATE', 'fh-collectibles' ), __( 'COLLECTIBLES', 'fh-collectibles' ) ),
		'image'     => 'hero-burgundy.jpg',
		'alt'       => __( 'Burgundy die-cast sports car keychain in studio lighting', 'fh-collectibles' ),
	),
	array(
		'kicker'    => __( '1:64 Scale · Collector Grade', 'fh-collectibles' ),
		'lead'      => __( 'Numbered drops, sealed vault editions—', 'fh-collectibles' ),
		'lead_soft' => __( 'once they are gone, they are gone.', 'fh-collectibles' ),
		'cta'       => __( 'Explore Limited Drops', 'fh-collectibles' ),
		'title'     => array( __( 'RARE FINDS.', 'fh-collectibles' ), __( 'PURE PASSION', 'fh-collectibles' ) ),
		'image'     => 'hero-onyx.jpg',
		'alt'       => __( 'Black and gold die-cast sports car keychain on dark stage', 'fh-collectibles' ),
	),
);

$fh_products = fh_home_products();
$fh_total    = is_countable( $fh_products ) ? count( $fh_products ) : 0;

$fh_categories = array(
	array(
		'slug'  => 'sports-car-keychains',
		'title' => __( 'Sports Car Keychains', 'fh-collectibles' ),
		'desc'  => __( 'Aggressive aero, modern supercars with high gloss finish.', 'fh-collectibles' ),
		'icon'  => 'bolt',
	),
	array(
		'slug'  => 'racing-editions',
		'title' => __( 'Racing Editions', 'fh-collectibles' ),
		'desc'  => __( 'Authentic track liveries, numbers, and gold spoke wheels.', 'fh-collectibles' ),
		'icon'  => 'flag',
	),
	array(
		'slug'  => 'classic-collectibles',
		'title' => __( 'Classic Collectibles', 'fh-collectibles' ),
		'desc'  => __( 'Vintage 70s muscle cars, retro curves and chrome bumpers.', 'fh-collectibles' ),
		'icon'  => 'clock',
	),
	array(
		'slug'  => 'limited-editions',
		'title' => __( 'Limited Editions', 'fh-collectibles' ),
		'desc'  => __( 'Numbered serial cards, gold-plated clasps, premium box sets.', 'fh-collectibles' ),
		'icon'  => 'star',
	),
);

$fh_apex = fh_has_woocommerce() ? wc_get_product( wc_get_product_id_by_sku( 'FH-APEX-01' ) ) : null;

$fh_sub_status = isset( $_GET['fh_sub'] ) ? sanitize_key( wp_unslash( $_GET['fh_sub'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<main id="fh-main">

	<!-- ============================ HERO ============================ -->
	<section class="fh-hero" id="home" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured collections', 'fh-collectibles' ); ?>">
		<div class="fh-hero__fx" aria-hidden="true">
			<span class="fh-orb fh-orb--1"></span>
			<span class="fh-orb fh-orb--2"></span>
			<span class="fh-hero__grid"></span>
			<span class="fh-spark fh-spark--1"></span>
			<span class="fh-spark fh-spark--2"></span>
			<span class="fh-spark fh-spark--3"></span>
			<span class="fh-spark fh-spark--4"></span>
			<span class="fh-spark fh-spark--5"></span>
			<span class="fh-spark fh-spark--6"></span>
		</div>
		<div class="fh-container">
			<hr class="fh-hero__rule" />
			<div class="fh-hero__viewport" data-hero>
				<div class="fh-hero__track" data-hero-track>
					<?php foreach ( $fh_hero_slides as $fh_i => $fh_slide ) : ?>
						<div class="fh-hero__slide<?php echo 0 === $fh_i ? '' : ' is-inactive'; ?>" data-hero-slide aria-hidden="<?php echo 0 === $fh_i ? 'false' : 'true'; ?>"<?php echo 0 === $fh_i ? '' : ' inert'; ?>>
							<div class="fh-hero__bg" style="background-image:url('<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $fh_slide['image'] ) ); ?>');" aria-hidden="true"></div>
							<span class="screen-reader-text"><?php echo esc_html( $fh_slide['alt'] ); ?></span>
							<div class="fh-hero__copy">
								<p class="lead"><?php echo esc_html( $fh_slide['lead'] ); ?><br /><span><?php echo esc_html( $fh_slide['lead_soft'] ); ?></span></p>
								<span class="fh-kicker"><?php echo esc_html( $fh_slide['kicker'] ); ?></span>
								<div>
									<a class="fh-btn fh-btn--gold fh-hero__link" href="<?php echo esc_url( fh_section_url( 'products' ) ); ?>"><?php echo esc_html( $fh_slide['cta'] ); ?></a>
								</div>
							</div>
							<div class="fh-hero__media" aria-hidden="true"></div>
							<div class="fh-hero__title-wrap">
								<h1 class="fh-hero__title"><?php echo esc_html( $fh_slide['title'][0] ); ?><span><?php echo esc_html( $fh_slide['title'][1] ); ?></span></h1>
								<div class="fh-hero__indicators" role="tablist" aria-label="<?php esc_attr_e( 'Hero slides', 'fh-collectibles' ); ?>">
									<?php foreach ( $fh_hero_slides as $fh_j => $fh_s ) : ?>
										<button type="button" role="tab" data-hero-dot="<?php echo esc_attr( $fh_j ); ?>" aria-selected="<?php echo $fh_j === $fh_i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'fh-collectibles' ), $fh_j + 1 ) ); ?>"></button>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<hr class="fh-hero__rule fh-hero__rule--bottom" />
		</div>
	</section>

	<!-- ======================= FEATURED PRODUCTS ======================= -->
	<section class="fh-products" id="products">
		<div class="fh-container">
			<div class="fh-section-head" data-reveal>
				<div>
					<span class="fh-kicker"><?php esc_html_e( 'Curated Roster', 'fh-collectibles' ); ?></span>
					<h2 class="fh-section-title"><?php esc_html_e( 'Featured Keychain Cars', 'fh-collectibles' ); ?></h2>
				</div>
				<?php if ( $fh_total > 0 ) : ?>
					<p class="fh-section-head__meta"><?php esc_html_e( 'Showing', 'fh-collectibles' ); ?> <strong><?php echo esc_html( $fh_total ); ?></strong> <?php esc_html_e( 'precision metal models', 'fh-collectibles' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="fh-product-grid">
				<?php
				if ( ! empty( $fh_products ) ) {
					foreach ( $fh_products as $fh_index => $fh_product ) {
						set_query_var( 'fh_product', $fh_product );
						set_query_var( 'fh_index', $fh_index );
						get_template_part( 'template-parts/product-card' );
					}
				} else {
					// Fallback demo cards when WooCommerce is not active.
					$fh_demo = array(
						array( 'product-onyx.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Midnight Onyx Edition', 'fh-collectibles' ) ),
						array( 'product-burgundy.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Classic Burgundy GT', 'fh-collectibles' ) ),
						array( 'product-alpine.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Alpine White Track Spec', 'fh-collectibles' ) ),
						array( 'product-daytona.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Electric Daytona Blue', 'fh-collectibles' ) ),
						array( 'product-sunset.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Sunset Flare Orange', 'fh-collectibles' ) ),
						array( 'product-stealth.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Matte Shadow Stealth', 'fh-collectibles' ) ),
						array( 'product-lime.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Viper Acid Lime Track', 'fh-collectibles' ) ),
						array( 'product-crimson.jpg', __( 'Hot Wheels Keychain', 'fh-collectibles' ), __( 'Scuderia Crimson Coupe', 'fh-collectibles' ) ),
					);
					foreach ( $fh_demo as $fh_index => $fh_item ) {
						set_query_var( 'fh_demo_card', $fh_item );
						set_query_var( 'fh_index', $fh_index );
						get_template_part( 'template-parts/product-card', 'demo' );
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- ==================== CURSOR STATEMENT ==================== -->
	<section class="fh-cursortext" id="statement" aria-label="Brand statement">
		<div class="fh-container">
			<span class="fh-kicker fh-cursortext__kicker"><?php esc_html_e( 'Move your cursor through the letters', 'fh-collectibles' ); ?></span>
			<p class="fh-cursortext__line" data-cursor-text><?php esc_html_e( 'MINIATURE MACHINES', 'fh-collectibles' ); ?></p>
			<p class="fh-cursortext__line" data-cursor-text><?php esc_html_e( 'MAXIMUM PASSION', 'fh-collectibles' ); ?></p>
		</div>
	</section>

	<!-- ========================= CATEGORIES ========================= -->
	<section class="fh-categories" id="categories">
		<div class="fh-container">
			<div class="fh-section-head" data-reveal>
				<div>
					<span class="fh-kicker"><?php esc_html_e( 'Tiers & Series', 'fh-collectibles' ); ?></span>
					<h2 class="fh-section-title"><?php esc_html_e( 'Shop by Category', 'fh-collectibles' ); ?></h2>
				</div>
				<?php if ( fh_has_woocommerce() ) : ?>
					<a class="fh-link-more" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
						<?php esc_html_e( 'View All Drops', 'fh-collectibles' ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
					</a>
				<?php endif; ?>
			</div>

			<div class="fh-categories__grid">
				<?php foreach ( $fh_categories as $fh_cat ) : ?>
					<?php
					$fh_term = fh_has_woocommerce() ? get_term_by( 'slug', $fh_cat['slug'], 'product_cat' ) : null;
					$fh_url  = $fh_term && ! is_wp_error( $fh_term ) ? get_term_link( $fh_term ) : fh_section_url( 'products' );
					$fh_count = ( $fh_term && ! is_wp_error( $fh_term ) ) ? (int) $fh_term->count : 0;
					?>
					<a class="fh-cat-card" href="<?php echo esc_url( $fh_url ); ?>" data-reveal>
						<span class="fh-cat-card__icon" aria-hidden="true">
							<?php
							switch ( $fh_cat['icon'] ) {
								case 'flag':
									echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>';
									break;
								case 'clock':
									echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>';
									break;
								case 'star':
									echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>';
									break;
								default:
									echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>';
							}
							?>
						</span>
						<h3 class="fh-cat-card__title"><?php echo esc_html( $fh_cat['title'] ); ?></h3>
						<p class="fh-cat-card__desc"><?php echo esc_html( $fh_cat['desc'] ); ?></p>
						<span class="fh-cat-card__more"><?php echo esc_html( sprintf( _n( '%d Model Available', '%d Models Available', $fh_count, 'fh-collectibles' ), $fh_count ) ); ?> →</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ========================== FEATURES ========================== -->
	<section class="fh-features" id="features">
		<div class="fh-container">
			<div class="fh-features__band">
				<div class="fh-features__grid">
					<?php
					$fh_features = array(
						array(
							'title' => __( '1:64 Die-Cast Alloys', 'fh-collectibles' ),
							'desc'  => __( 'Cast with zinc alloy and stainless steel rings for permanent daily durability.', 'fh-collectibles' ),
							'icon'  => '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>',
						),
						array(
							'title' => __( 'Scratch-Resistant Clear Coat', 'fh-collectibles' ),
							'desc'  => __( 'Triple-stage automotive clear coat prevents paint chips and scratches in pocket.', 'fh-collectibles' ),
							'icon'  => '<path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>',
						),
						array(
							'title' => __( 'Luxury Presentation Box', 'fh-collectibles' ),
							'desc'  => __( 'Arrives packaged in an embossed matte velvet lined magnetic gift box.', 'fh-collectibles' ),
							'icon'  => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>',
						),
						array(
							'title' => __( 'Collector Guarantee', 'fh-collectibles' ),
							'desc'  => __( '30-day mint-condition replacement policy with numbered certificate of origin.', 'fh-collectibles' ),
							'icon'  => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>',
						),
					);
					foreach ( $fh_features as $fh_feature ) :
						?>
						<div class="fh-feature" data-reveal>
							<span class="fh-feature__icon" aria-hidden="true">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><?php echo $fh_feature['icon']; // phpcs:ignore WordPress.Security.EscapeOutput ?></svg>
							</span>
							<div>
								<h4 class="fh-feature__title"><?php echo esc_html( $fh_feature['title'] ); ?></h4>
								<p class="fh-feature__desc"><?php echo esc_html( $fh_feature['desc'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- ====================== LIMITED SPOTLIGHT ====================== -->
	<section class="fh-spotlight" id="apex">
		<div class="fh-container">
			<div class="fh-spotlight__banner" data-reveal>
				<div class="fh-spotlight__grid">
					<div>
						<div class="fh-pill">
							<span><?php esc_html_e( 'Rare Batch', 'fh-collectibles' ); ?></span>
							<span aria-hidden="true">•</span>
							<em><?php esc_html_e( '042 / 500 Worldwide', 'fh-collectibles' ); ?></em>
						</div>
						<h3 class="fh-spotlight__title"><?php esc_html_e( 'APEX Series 01 — 24K Gold Chassis', 'fh-collectibles' ); ?></h3>
						<p class="fh-spotlight__desc"><?php esc_html_e( 'Featuring mirror polished chrome accents, bespoke micro-machined wheels, and custom display pedestal with acrylic showcase case.', 'fh-collectibles' ); ?></p>
						<div class="fh-spotlight__actions">
							<span class="fh-spotlight__price"><?php echo esc_html( $fh_apex instanceof WC_Product ? wp_strip_all_tags( wc_price( $fh_apex->get_price() ) ) : '$48.00' ); ?></span>
							<?php
							if ( $fh_apex instanceof WC_Product ) {
								fh_add_to_cart_button( $fh_apex, __( 'Claim Limited Edition', 'fh-collectibles' ), 'fh-btn--gold fh-btn--lg' );
							} else {
								printf(
									'<a class="fh-btn fh-btn--gold fh-btn--lg" href="%1$s">%2$s</a>',
									esc_url( fh_section_url( 'products' ) ),
									esc_html__( 'Claim Limited Edition', 'fh-collectibles' )
								);
							}
							?>
						</div>
					</div>
					<div class="fh-verify-wrap">
						<div class="fh-verify" role="presentation">
							<span class="fh-verify__label"><?php esc_html_e( 'Collector Verification', 'fh-collectibles' ); ?></span>
							<span class="fh-verify__code">SR·01</span>
							<span class="fh-verify__sub"><?php esc_html_e( 'Serialized Laser Engraved Baseplate', 'fh-collectibles' ); ?></span>
							<div class="fh-verify__meta">
								<span><?php esc_html_e( 'AUTHENTIC', 'fh-collectibles' ); ?></span>
								<span aria-hidden="true">•</span>
								<span><?php esc_html_e( '1:64 SCALE', 'fh-collectibles' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ========================= NEWSLETTER ========================= -->
	<section class="fh-newsletter" id="newsletter">
		<div class="fh-container">
			<div class="fh-newsletter__box" data-reveal>
				<span class="fh-kicker"><?php esc_html_e( 'Early Collector Access', 'fh-collectibles' ); ?></span>
				<h2 class="fh-newsletter__title"><?php esc_html_e( 'Never Miss a Drop', 'fh-collectibles' ); ?></h2>
				<p class="fh-newsletter__desc"><?php esc_html_e( 'Limited editions sell out in minutes. Subscribe for secret link drops, restock alerts, and VIP discounts.', 'fh-collectibles' ); ?></p>

				<?php if ( 'success' === $fh_sub_status ) : ?>
					<p class="fh-newsletter__msg fh-newsletter__msg--ok" role="status"><?php esc_html_e( "You're on the list — watch your inbox for the next drop.", 'fh-collectibles' ); ?></p>
				<?php elseif ( in_array( $fh_sub_status, array( 'invalid', 'error' ), true ) ) : ?>
					<p class="fh-newsletter__msg fh-newsletter__msg--err" role="alert"><?php esc_html_e( 'Please enter a valid email address.', 'fh-collectibles' ); ?></p>
				<?php endif; ?>

				<form class="fh-newsletter__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="fh_subscribe" />
					<?php wp_nonce_field( 'fh_newsletter', 'fh_newsletter_nonce' ); ?>
					<p style="position:absolute;left:-9999px;" aria-hidden="true"><label>Leave this field empty<input type="text" name="fh_hp" tabindex="-1" autocomplete="off" /></label></p>
					<label class="screen-reader-text" for="fh-newsletter-email"><?php esc_html_e( 'Email address', 'fh-collectibles' ); ?></label>
					<input type="email" id="fh-newsletter-email" name="fh_email" placeholder="<?php esc_attr_e( 'Enter your email address', 'fh-collectibles' ); ?>" required />
					<button class="fh-btn fh-btn--gold" type="submit"><?php esc_html_e( 'Subscribe', 'fh-collectibles' ); ?></button>
				</form>
				<span class="fh-newsletter__note"><?php esc_html_e( 'No spam. Only mint drops and tracking updates.', 'fh-collectibles' ); ?></span>
			</div>
		</div>
	</section>

</main>

<div class="fh-toast" id="fh-toast" role="status" aria-live="polite">
	<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
	<span id="fh-toast-text"><?php esc_html_e( 'Added to cart', 'fh-collectibles' ); ?></span>
	<a href="<?php echo esc_url( fh_has_woocommerce() ? wc_get_cart_url() : '#' ); ?>"><?php esc_html_e( 'View cart', 'fh-collectibles' ); ?></a>
</div>

<?php
get_footer();
