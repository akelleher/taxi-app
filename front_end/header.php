<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once(SITE_ROOT . '/PHP/User.php');

function curPageName() {
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

if( !isset($_SESSION) ) {
	session_start();
} 
?>

<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL ?>/front_end/resources/header.css">
<header>
	<h1>
		<a href="">
			<img src="./resources/johnny'sapple.png" height="38px" width="38px"> TaxiPing
		</a>
	</h1>
	<nav>
		<ul>
			<?php if( $_SESSION['user']->getIsAdmin() ) : ?>
						<li><a href="admin.php" <?php if( curPageName() === 'admin.php' ) : ?>class="current" <?php endif; ?>>Admin</a></li>
			<?php endif; ?>
			<?php if( $_SESSION['user']->getIsDriver() ) : ?>
						<li><a href="Driver.php" <?php if( curPageName() === 'Driver.php' ) : ?>class="current" <?php endif; ?>>Driver</a></li>
			<?php endif; ?>
			<?php if( $_SESSION['user']->getIsDispatcher() ) : ?>
						<li><a href="Dispatcher.php" <?php if( curPageName() === 'Dispatcher.php' ) : ?> class="current" <?php endif; ?>>Dispatcher</a></li>
			<?php endif; ?>




		</ul>
	</nav>
</header>