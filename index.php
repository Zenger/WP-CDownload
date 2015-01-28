<?php 
	
	ini_set("memory_limit", "512M");
	#date_default_timezone_set("Europe/Chisinau"); // Optional
	set_time_limit(0);

	/* Init WP */

	list($wp_path) = explode('wp-content' , __FILE__);

	require_once( $wp_path . "/wp-config.php");

	$wp->init(); $wp->parse_request(); $wp->query_posts();

	$wp->register_globals(); $wp->send_headers();

	define('CD_NOT_ALLOWED', "You are not allowed to access this!");
	define('CD_UPGRADE', "You must upgrade your status to have access to this file!");	

	/* Check if the user downloads PSD */
	$isPSD =  (isset($_GET['psd']) && $_GET['psd'] == true);


/* Serve the file to browser helper function */

	function throwDownloadHeader( $file, $sub = "/files/")
	{	
		status_header(200);

		if (file_exists($file)) 
		{
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename='.basename($file));
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    if (ob_get_level()) ob_clean();
		    flush();
		    readfile($file);
		    exit;
		}
	}

	/* Get requested file */

	if (!isset($_GET) || empty($_GET['template_id'])) 
	{
		status_header(403); 
		echo CD_NOT_ALLOWED . "<!-- 1 -->";
		exit();
	}

	else
	{
		$template_id = intval($_GET['template_id']);

		
		$name =  sanitize_title( get_the_title( $template_id ) )  ;

		
		
		if (!$name) {
			status_header( 403 );
			echo CD_NOT_ALLOWED . "<!-- 2 -->";;
			exit();
		}
	}

	if (! App::is_member()  ) // @TODO:Change this to a proper validation method of your users
	{
		status_header( 403 );
		echo CD_NOT_ALLOWED . "<!-- 3 -->";;
		exit();
	}




	if (!$isPSD)
	{

		$user = wp_get_current_user();
		$themes = (array)get_user_meta( $user->ID, 'downloaded_themes', true );
		$themes[] = $name;

		$themes = array_unique( $themes );

		update_user_meta( $user->ID, 'downloaded_themes', $themes  );

		throwDownloadHeader( get_template_directory() . '/cdownload/files/'. $name . ".zip");
	
	}
	else
	{

		throwDownloadHeader( get_template_directory() , '/cdownload/files/PSD/' . $name . ".zip" );
	} 