<?php
/** Log in / Register (WordPress page with slug "login"). */
if ( is_user_logged_in() ) {
	wp_safe_redirect( rn_account_url() );
	exit;
}
get_header();
$tab = isset( $_GET['tab'] ) && 'register' === $_GET['tab'] ? 'register' : 'login'; // phpcs:ignore WordPress.Security.NonceVerification
$err = isset( $_GET['dm_err'] ) ? sanitize_key( wp_unslash( $_GET['dm_err'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$to  = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$errors = array(
	'nonce'    => 'The page expired. Please try again.',
	'spam'     => 'Could not register. Please try again.',
	'rate'     => 'Too many attempts. Please try again later.',
	'fields'   => 'Please enter your name and a valid email address.',
	'weak'     => 'Password must be at least 8 characters.',
	'mismatch' => 'The two passwords do not match.',
	'exists'   => 'An account with this email already exists. Please log in.',
	'login'    => 'Wrong email or password.',
	'rejected' => 'This account was not approved.',
);
$self = home_url( '/login/' );
?>
<div class="rn-wrap">
	<section class="rn-sec" style="max-width:520px;margin:0 auto;width:100%">
		<div><h1 class="rn-ptitle"><?php echo 'register' === $tab ? 'Register' : 'Log in'; ?></h1><div class="rn-muted">Reading is free without an account. An account lets you comment, rate and bookmark.</div></div>
		<nav class="rn-filters" aria-label="Log in or register">
			<a href="<?php echo esc_url( add_query_arg( 'redirect_to', $to, $self ) ); ?>"<?php echo 'login' === $tab ? ' aria-current="true"' : ''; ?>>Log in</a>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'register', 'redirect_to' => $to ), $self ) ); ?>"<?php echo 'register' === $tab ? ' aria-current="true"' : ''; ?>>Register</a>
		</nav>
		<?php if ( $err && isset( $errors[ $err ] ) ) : ?><div class="rn-note rn-err" role="alert"><?php echo esc_html( $errors[ $err ] ); ?></div><?php endif; ?>

		<?php if ( 'login' === $tab ) : ?>
			<form class="rn-card rn-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dm_login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $to ); ?>">
				<?php wp_nonce_field( 'dm_login' ); ?>
				<label class="rn-field" for="dm_login">Email<input id="dm_login" name="dm_login" type="text" autocomplete="username" required></label>
				<label class="rn-field" for="dm_pass">Password<input id="dm_pass" name="dm_pass" type="password" autocomplete="current-password" required></label>
				<label class="rn-check"><input type="checkbox" name="dm_remember" value="1" checked> Keep me logged in</label>
				<button class="rn-btn" type="submit">Log in</button>
				<p class="rn-muted" style="margin:0;font-size:14px">Forgot your password? <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Reset it</a></p>
			</form>
		<?php else : ?>
			<form class="rn-card rn-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dm_register">
				<?php wp_nonce_field( 'dm_register' ); ?>
				<div class="rn-hp" aria-hidden="true"><label>Website<input name="dm_website" type="text" tabindex="-1" autocomplete="off"></label></div>
				<label class="rn-field" for="dm_name">Your name<input id="dm_name" name="dm_name" type="text" autocomplete="name" required></label>
				<label class="rn-field" for="dm_email">Email<input id="dm_email" name="dm_email" type="email" autocomplete="email" required></label>
				<label class="rn-field" for="dm_pass_r">Password (at least 8 characters)<input id="dm_pass_r" name="dm_pass" type="password" autocomplete="new-password" minlength="8" required></label>
				<label class="rn-field" for="dm_pass2">Confirm password<input id="dm_pass2" name="dm_pass2" type="password" autocomplete="new-password" minlength="8" required></label>
				<p class="rn-muted" style="margin:0;font-size:14px">After you register, the author approves your account once. You can read everything meanwhile.</p>
				<button class="rn-btn" type="submit">Create account</button>
			</form>
		<?php endif; ?>
	</section>
</div>
<?php
get_footer();
