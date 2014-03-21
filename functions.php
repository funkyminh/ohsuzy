<?php


	// Load custom libraries used in theme
$cudazi_libraries = 
array( 
	'themesetup',
	'theme-options',			
	'debug',
	'filters',
	'shortcodes',
	'widgets',
	'featuredimages',
	'meta-boxes',
	'custom-post-types',
	'comments',
	'attachment-gallery',			
	'plugins/cudazi-latest-posts',
	'plugins/cudazi-twitter-widget'
	);
foreach( $cudazi_libraries as $library ) {
	include_once( 'libraries/' . $library . '.php' );
}



	// Theme and Version Information
$cudazi_theme_data = get_theme_data(TEMPLATEPATH . '/style.css');
define('CUDAZI_THEME_NAME', $cudazi_theme_data['Title']);
define('CUDAZI_THEME_AUTHOR', $cudazi_theme_data['Author']);
define('CUDAZI_THEME_URI', $cudazi_theme_data['URI']);
define('CUDAZI_THEME_VERSION', $cudazi_theme_data['Version']);
define('CUDAZI_THEME_INFOLINE', CUDAZI_THEME_NAME . ' by ' . CUDAZI_THEME_AUTHOR . ' (' . CUDAZI_THEME_URI . ') v' . CUDAZI_THEME_VERSION);

add_action('wp_footer','cudazi_display_themeinfo');
function cudazi_display_themeinfo() {
		echo '<!-- ' . CUDAZI_THEME_INFOLINE . ' -->'; // Display for easier debugging remotely
	}


	// Fallback (Pre 3.0) menu system
	function cudazi_menu_fallback()
	{
		$menu = "<ul class='sf-menu'>";
		//$menu .= wp_list_pages('echo=0&title_li=');
		$menu .= "<li><a href='#'>" . __( 'Add a menu in Apperance, Menus', 'cudazi' ) . "</a></li>";
		$menu .= "</ul>";
		echo $menu;
	}
	
	
	// post->ID returns first post ID, need to correct on the blog page only
	function cudazi_corrected_post_id() {
		global $post;
		$post_id = null;
		if ( get_option('show_on_front') == 'page' && get_option('page_for_posts') && is_home() ) {
			$post_id = get_option('page_for_posts');
		} else {
			if ($post != null) {
				$post_id = $post->ID;
			}
		}
		return $post_id;
	}


	// return page number
	function cudazi_get_page_number() {
		global $post;
		if(get_query_var('paged')) {
			$paged = get_query_var('paged');
		} elseif(get_query_var('page')) {
			$paged = get_query_var('page');
		} else {
			$paged = 1;
		}
		return $paged;
	}
	
	
	// Get Featured Image + Link
	function cudazi_get_featured_image( $img_size, $fallback ) {
		global $post;		
		
		$cudazi_post_hide_featured_single = get_post_meta($post->ID, 'cudazi_post_hide_featured_single', true);
		if ( is_single() && $cudazi_post_hide_featured_single ) {
			return false;
		}
		
		if ( has_post_thumbnail() ) {								
			$featured_image_link_to = get_post_meta($post->ID, 'featured_image_link_to', true);
			$featured_image_link_to_url = get_post_meta($post->ID, 'featured_image_link_to_url', true);			
			if ( $featured_image_link_to_url ) {
				$featured_image_link = $featured_image_link_to_url;
			}else{
				if ( $featured_image_link_to == 'post' ) {
					$featured_image_link = get_permalink();
				}else if ( $featured_image_link_to == 'image' || !$featured_image_link_to ) {
					$featured_image_link = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
					$featured_image_link = $featured_image_link[0];					
				}
			} // end if url set			
			$featured_image = get_the_post_thumbnail($post->ID, $img_size, array( 'class' => '' ));
		}else{ // no thumbnail set
			$featured_image_link = get_permalink();
			$featured_image = $fallback;
		} // end if has featured image				
		
		if ( has_post_thumbnail() ) {		
			return "<div class='post-thumbnail'><a href='" . $featured_image_link . "'>" . $featured_image . "</a></div>";		
		}
	}



	/**
	 * Put Json Instagram stream in file instragram.txt
	 */
	function sync_instagram(){
		$raw_response = wp_remote_get($url_instagram);

		if ( is_wp_error($raw_response) ) {
			$output = "<p>Failed to update from Instagram!</p>\n";
		} else {
			$output = "<p>Sync Instagram OK</p>\n";
			file_put_contents($file_instagram, $raw_response['body']);
		}
		return $output;
	}


	/**
	 * Get Instagram stream from file instragram.txt
	 */
	function get_instagram(){
		include("config.inc");
		$json_body = file_get_contents($file_instagram);

		if ( function_exists('json_decode') ) {
			$response = get_object_vars(json_decode($json_body));
			$instagram = array();
			for ( $i=0; $i < count($response['data']); $i++ ) {
				$response['data'][$i] = get_object_vars($response['data'][$i]);
				array_push($instagram, array(
					'title' => $response['data'][$i]['caption']->text,
					'url' => $response['data'][$i]['images']->thumbnail->url,
					'image' => $response['data'][$i]['images']->standard_resolution->url,
					'link' => $response['data'][$i]['link'],
					'date' => $response['data'][$i]['created_time']
					));
			}
		} else {
			include(ABSPATH . WPINC . '/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');
			$json = new Moxiecode_JSON();
			$response = @$json->decode($json_body);
		}

		return $instagram;
	}


	/**
	 * Put Json Facebook events stream in file facebook.txt
	 */
	function sync_facebook_event(){
		$raw_response = wp_remote_get($url_facebook);

		if ( is_wp_error($raw_response) ) {
			$output = "<p>Failed to update from Facebook!</p>\n";
		} else {
			$output = "<p>Sync Facebook OK</p>\n";
			file_put_contents($file_facebook, $raw_response['body']);
		}
		return $output;
	}


	/**
	 * Get Facebook stream from file facebook.txt
	 */
	function get_facebook(){
		include("config.inc");
		$json_body = file_get_contents($file_facebook);

		if ( function_exists('json_decode') ) {
			$response = get_object_vars(json_decode($json_body));
			$facebook = array();
			for ( $i=0; $i < count($response['data']); $i++ ) {
				$response['data'][$i] = get_object_vars($response['data'][$i]);
				array_push($facebook, array(
					'from' => $response['data'][$i]['from']->name,
					'message' => $response['data'][$i]['message'],
					'link' => $response['data'][$i]['link'],
					'picture' => $response['data'][$i]['picture'],
					'created_time' => $response['data'][$i]['created_time'],
					'icon' => $response['data'][$i]['icon'],
					'story' => $response['data'][$i]['story'],
					'type' => $response['data'][$i]['type'],
					'status_type' => $response['data'][$i]['status_type']
					));
			}
		} else {
			include(ABSPATH . WPINC . '/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');
			$json = new Moxiecode_JSON();
			$response = @$json->decode($json_body);
		}

		return $facebook;
	}


	/**
	 * Put Json Facebook photos in file facebook_photos.txt
	 */
	function sync_facebook_photos(){
		$raw_response = wp_remote_get($url_facebook_photos);

		if ( is_wp_error($raw_response) ) {
			$output = "<p>Failed to update from Facebook!</p>\n";
		} else {
			$output = "<p>Sync Facebook OK</p>\n";
			file_put_contents($file_facebook_photos, $raw_response['body']);
		}
		return $output;
	}


	/**
	 * Get Facebook stream from file facebook_photos.txt
	 */
	function get_facebook_photos(){
		include("config.inc");
		$json_body = file_get_contents($file_facebook_photos);

		if ( function_exists('json_decode') ) {
			$response = get_object_vars(json_decode($json_body));
			$facebook = array();
			for ( $i=0; $i < count($response['data']); $i++ ) {
				$response['data'][$i] = get_object_vars($response['data'][$i]);
				array_push($facebook, array(
					'from' => $response['data'][$i]['from']->name,
					'name' => $response['data'][$i]['name'],
					'picture' => $response['data'][$i]['picture'],
					'source' => $response['data'][$i]['source'],
					'created_time' => $response['data'][$i]['created_time'],
					'icon' => $response['data'][$i]['icon']
					));
			}
		} else {
			include(ABSPATH . WPINC . '/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');
			$json = new Moxiecode_JSON();
			$response = @$json->decode($json_body);
		}

		return $facebook;
	}


	/**
	 * Synchronize social network with param ?sync=ok in url
	 */
	function sync_social_network(){
		include("config.inc");
		if ($_GET['sync']=="ok"){
			sync_instagram();
			//sync_facebook_event();
			//sync_facebook_photos();
		}
	}


	/**
	 * display instagram
	 */
	function display_instagram(){
		sync_instagram();
		$instagram = get_instagram();
		$nb_pic = 4;
		for ($i=0; $i < $nb_pic; $i++) { 
			if (isset($instagram[$i])){
				$date = date('Y-m-d H:i:s', $instagram[$i]["date"]);
				echo "<a target=_blank href=".$instagram[$i]["link"]."><img src=".$instagram[$i]["url"]."></a>";
		    //echo "<li><a href=".$instagram[$i]["image"]." rel='lightbox[album1]' title='Spring'><img src=".$instagram[$i]["url"]." /></a></li>";
			}
		}
	}

	add_shortcode( 'instagram', 'display_instagram' );

	/**
	 * display facebook
	 */
	function display_facebook(){
		$facebook = get_facebook();
		$nb_post = 100;
		for ($i=0; $i < $nb_post; $i++) { 
			if (isset($facebook[$i])){
				echo "<br>";
				echo "<div style=background-color:#E8E5CE;>";
				echo $facebook[$i]['created_time'];
				echo "<br>";
				if (isset($facebook[$i]['story'])) echo "<i>".$facebook[$i]['story']."</i><br>";
				echo $facebook[$i]['message'];
				echo "<a href=".$facebook[$i]['link']."> lien</a>";
				echo "<br>";
				if (isset($facebook[$i]['picture'])) echo "<img src=".$facebook[$i]['picture']."><br>";
				if (isset($facebook[$i]['icon'])) echo "<img src=".$facebook[$i]['icon']."><br>";
				if (isset($facebook[$i]['type'])) echo "<i>".$facebook[$i]['type']."</i><br>";
				if (isset($facebook[$i]['status_type'])) echo "<i>".$facebook[$i]['status_type']."</i><br>";
				echo "</div>";
			}
		}
	}


	/**
	 * display instagram
	 */
	function display_facebook_photos(){
		$facebook_photos = get_facebook_photos();
		$nb_pic = 100;
		for ($i=0; $i < $nb_pic; $i++) { 
			if (isset($facebook_photos[$i])){
				$date = date('Y-m-d H:i:s', $facebook_photos[$i]["date"]);
				echo "<a data-lightbox='image' href=".$facebook_photos[$i]["source"]."><img src=".$facebook_photos[$i]["picture"]."></a>";
	      //echo "<li><a href=".$facebook_photos[$i]["source"]." rel='lightbox[album2]' title='Spring'><img src=".$facebook_photos[$i]["picture"]." /></a></li>";

			}
		}
	}








	?>