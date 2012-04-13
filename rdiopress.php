<?php
/*
Plugin Name: rdiopress
Plugin URI: http://ortus.dotharbor.net/rdiopress
Description: Use this plugin to show your visitors your last played song at rdio
Version: 0.5
Author: GerritG aka Larcos
Author URI: http://ortus.dotharbor.net
License: GPL2
*/

/*  Copyright 2012  Gerrit Gazic  (email : http://dotharbor.net/impressum)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'lib/rdio.php';
include 'rdiopress_admin.php';

class rdiopress_widget extends WP_Widget {

	// Constructor //
	function rdiopress_widget() {
		$widget_ops = array( 'classname' => 'rdiopress_widget', 'description' => 'Displays your last played song with preview and link' ); // Widget Settings
		$control_ops = array( 'id_base' => 'rdiopress_widget' );
		$this->WP_Widget( 'rdiopress_widget', 'rdiopress', $widget_ops, $control_ops );
	}

	// Extract Args //
	function widget($args, $instance) {
		# Widget options
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$width = $instance['width'];

		# Plugin Options
		$options = get_option('rdiopress_opt');

		# rdio
		$rdio = new Rdio(array("uu4v8uexnwwht843rd8w6j9j", "mKJQkPrkEH"));

		echo $before_widget;

		if ( $title ) { echo $before_title . $title . $after_title; }

		if ($options['authenticated'] == 1) {
			$rdio->token = array($options['oauth_token'], $options['oauth_token_secret']);
		  	$currentUser = $rdio->call('currentUser', array('extras'=>'lastSongPlayed'));
		
		  	if ($currentUser) {		
		  		echo 	'<div class="rp-widget" style="width:' . $width . ';">
			  				<img src="' . $currentUser->result->lastSongPlayed->icon . '" alt="albumart" style="width:' . $width . 'px; margin-bottom:-8px;" />
			  				<div class="rp-player">
				  				<param name="movie" value="' . $currentUser->result->lastSongPlayed->embedUrl . '"></param>
								<param name="allowFullScreen" value="true"></param>
								<param name="allowscriptaccess" value="always"></param>
								<embed src="' . $currentUser->result->lastSongPlayed->embedUrl . '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $width . '" height="80"></embed>
								</object>
							</div>
						</div>';
		  	}

		} else { ?>
			<p>Please authenticate the plugin first! </p>
		<?php }

		echo $after_widget;
	}

	// Update Settings //
	function update($new_instance, $old_instance) {
 		$instance['title'] = ($new_instance['title']);
 		$instance['width'] = ($new_instance['width']);
 		return $instance;
 	}

 	// Widget Control Panel //
	function form($instance) {
 		$defaults = array( 'title' => 'Recently Played', 'width' => '250');
 		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

 		<p>
 			<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
 			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
 		</p>
 		<p>
 			<label for="<?php echo $this->get_field_id('width'); ?>">Width</label>
 			<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>'" type="text" value="<?php echo $instance['width']; ?>" />
 		</p>
	<?php }
}
add_action( 'widgets_init', create_function( '', 'register_widget( "rdiopress_widget" );' ) );

function get_data($url){
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}