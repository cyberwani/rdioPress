<?php 
/* What to do when the plugin is activated? */
register_activation_hook(__FILE__,'rdiopress_install');

/* What to do when the plugin is deactivated? */
register_deactivation_hook( __FILE__, 'rdiopress_remove' );

function rdiopress_install() {
	/* Create a new database field */
	add_option("rdiopress_opt");
}

function rdiopress_remove() {
	/* Delete the database field */
	delete_option('rdiopress_opt');
}

add_action('admin_menu', 'rdiopress_admin_menu');
function rdiopress_admin_menu() {
	add_options_page('rdiopress', 'rdiopress', 'manage_options', 'rdiopress', 'rdiopress_admin_options_page');
}

function rdiopress_admin_options_page() { 
	$current_url = "http" . ((!empty($_SERVER['HTTPS'])) ? "s" : "") . "://" . $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] . "?page=rdiopress";
	$rdio = new Rdio(array("uu4v8uexnwwht843rd8w6j9j", "mKJQkPrkEH"));
	$options = get_option('rdiopress_opt'); 

	if(isset($_POST['delete'])){
		$options['oauth_token'] = "";
		$options['oauth_token_secret'] = "";
		$options['authenticated'] = 0;
		update_option('rdiopress_opt', $options);
	} ?>

	<div class="wrap">
		<?php screen_icon();  ?>
		<h2>rdiopress Options</h2>
		<hr/>

		<h3>Authentication</h3>
		<p>To use the rdiopress widget you need to authenticate the plugin to access your rdio account.</p>
		
		<?php if ($options['oauth_token'] && $options['oauth_token_secret'] && ($_GET['oauth_verifier'] || $options['authenticated'] == 1)) {
			# we have a token
			$rdio->token = array($options['oauth_token'], $options['oauth_token_secret']);
		
			if ($_GET['oauth_verifier']) {
			    # we've been passed a verifier, that means that we're in the middle of
			    # authentication.
			    $rdio->complete_authentication($_GET['oauth_verifier']);
			    $options['oauth_token'] = $rdio->token[0];
			    $options['oauth_token_secret'] = $rdio->token[1];
			    $options['authenticated'] = 1;
			    update_option('rdiopress_opt', $options);
		  	}
		  	
		  	#AUTHENTICATED
		  	?>
		  	<p>You authenticated the plugin successfully!</p>

		  	<form action="<?=$current_url ?>" method="post">
		  		<input type="submit" name="delete" value="delete authentication"/>
		  	</form>

		<?php } else { 

			$authorize_url = $rdio->begin_authentication($current_url);
			$options['oauth_token'] = $rdio->token[0];
			$options['oauth_token_secret'] = $rdio->token[1]; 
			update_option('rdiopress_opt', $options); ?>

			<a class="button" href="<?=$authorize_url ?>" title="authenticate">
      			authenticate
    		</a>

		<?php } ?>
	</div>
<?php } ?>