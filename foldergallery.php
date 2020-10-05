<?php
/*
Plugin Name: Folder Gallery Slider
Plugin URI: https://github.com/svenbolte/foldergallery
Author URI: https://github.com/svenbolte
Author: PBMod
Description: This plugin creates picture galleries and sliders from a folder or from recent posts. It can output directory contents with secure download links. csv files can bis displayed as table and csv files read from external url.
Tags: gallery, folder, lightbox, lightview, bxslider, slideshow, image sliders, csv-folder-to-table, csv-to-table-from-url
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: foldergallery
Domain Path: /languages
Version: 9.7.6.14
Stable tag: 9.7.6.14
Requires at least: 5.1
Tested up to: 5.5.1
Requires PHP: 7.2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

new foldergallery();
class foldergallery{
	public function __construct() {		
		add_action( 'admin_menu', array( $this, 'fg_menu' ) );	
		add_action( 'admin_init', array( $this, 'fg_settings_init' ) );
		add_action('plugins_loaded', array( $this, 'fg_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'fg_styles' ) );
		add_shortcode( 'foldergallery', array( $this, 'fg_gallery' ) );
		add_shortcode( 'folderdir', array( $this, 'meinedirliste' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'fg_plugin_action_links' ) );
		add_action( 'init', array( $this, 'fg_init_handle_download' ) );	
	}

/**
 * Init handle download FG. on folderdir option protect=1
 */
function fg_init_handle_download() {
	if ( isset( $_GET[ 'dlid' ] ) ) {
		// Onedaypass prüfen
		if ($_GET['code'] == md5( $_GET[ 'dlid' ] . intval(date('Y-m-d H:i:s')) / 24 * 3600)) { // if it match it is legit
			// onedaypass_process( absint( $_GET[ 'dlid' ] ) );
			$url_parse = wp_parse_url( $_GET[ 'dlid' ] );
			$path = ABSPATH . $url_parse['path'];
			$mm_type="application/octet-stream"; // modify accordingly to the file type of $path, but in most cases no need to do so
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Type: " . $mm_type);
			header("Content-Length: " .(string)(filesize($path)) );
			header('Content-Disposition: attachment; filename="'.basename($path).'"');
			header("Content-Transfer-Encoding: binary\n");
			readfile($path); // outputs the content of the file
			exit();		  
		} else {
			echo 'Pfad nicht gefunden oder Code falsch'; // not legit
		}  
	}	
}
	
	public function foldergallery() {
		self::__construct();
	}

	public function fg_init() {
		load_plugin_textdomain( 'foldergallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		$fg_options = get_option( 'FolderGallery' );
		if ( empty( $fg_options ) ) {
			update_option( 'FolderGallery', $this->fg_settings_default() );
			return;
		}
		if ( ! isset( $fg_options['engine'] ) ) {
			$fg_options['engine'] = 'none';
			update_option( 'FolderGallery', $fg_options );
		}
		if ( 'lightview' == $fg_options['engine'] ) {
			if ( ! is_dir( WP_CONTENT_DIR . '/lightview' ) ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}		
		if ( 'fancybox2' == $fg_options['engine'] ) {
			if ( ! is_dir( WP_CONTENT_DIR . '/fancybox' ) ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( 'fancybox3' == $fg_options['engine'] ) {
			if ( ! is_dir( WP_CONTENT_DIR . '/fancybox3' ) ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( 'lightbox2' == $fg_options['engine'] ) {
			if ( ! is_dir( WP_CONTENT_DIR . '/lightbox' ) ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( ! isset( $fg_options['fb_speed'] ) ) { // 1.1 + 1.2 + 1.3 update
			$fg_options['thumbnails'] = 'all';
			$fg_options['fb_title'] = 'float';
			$fg_options['caption'] = 'default';
			$fg_options['fb_speed'] = 0;
			update_option( 'FolderGallery', $fg_options );
		}
		if ( ! isset( $fg_options['fb_effect'] ) ) { // 1.4 + 1.4.1 update
			$fg_options['show_thumbnail_captions'] = 0;
			$fg_options['caption'] = 'default';
			$fg_options['sort'] = 'filename';
			$fg_options['fb_effect'] = 'elastic';
			update_option( 'FolderGallery', $fg_options );
		}
		if ( ! isset( $fg_options['orientation'] ) ) { // 1.7 update
			$fg_options['orientation'] = 0;
			update_option( 'FolderGallery', $fg_options );
		}
		if ( ! isset( $fg_options['fb3_speed'] ) ) { // 1.7.4 update
			$fg_options['fb3_speed'] = 3;
			$fg_options['fb3_loop'] = 0;
			$fg_options['fb3_toolbar'] = 1;
			$fg_options['fb3_infobar'] = 0;
			$fg_options['fb3_arrows'] = 1;
			$fg_options['fb3_fullscreen'] = 0;
			$fg_options['fb3_autostart'] = 0;
			$fg_options['wpml'] = 0;
			update_option( 'FolderGallery', $fg_options );
		}	
	}

	public function fg_styles(){
		$fg_options = get_option( 'FolderGallery' );
		switch ( $fg_options['engine'] ) {
			case 'lightbox2' :
				wp_enqueue_style( 'fg-lightbox-style', content_url( '/lightbox/css/lightbox.css', __FILE__ ) );
			break;
			case 'fancybox2' :
				wp_enqueue_style( 'fancybox-style', content_url( '/fancybox/source/jquery.fancybox.css', __FILE__ ) );
			break;
			case 'fancybox3' :
				wp_enqueue_style( 'fancybox-style', content_url( '/fancybox3/dist/jquery.fancybox.min.css', __FILE__ ) );
			break;
			case 'lightview' :
				wp_enqueue_style( 'lightview-style', content_url( '/lightview/css/lightview/lightview.css', __FILE__ ) );		
			break;
			case 'photoswipe' :
			case 'responsive-lightbox' :
			case 'easy-fancybox' :
			case 'slenderbox-plugin' :
			case 'none' :
				// do nothing for now
			break;
		}
	}

	public function fg_scripts(){
		static $firstcall = 1;
		$fg_options = get_option( 'FolderGallery' );	
		switch ( $fg_options['engine'] ) {
			case 'lightbox2' :
				wp_enqueue_script( 'lightbox-script', content_url( '/lightbox/js/lightbox.min.js', __FILE__ ), array( 'jquery' ) );
			break;
			case 'fancybox2' :
				wp_enqueue_script( 'fancybox-script', content_url( '/fancybox/source/jquery.fancybox.pack.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'fg-fancybox-script', plugins_url( '/js/fg-fancybox.js', __FILE__ ), array( 'jquery' ) );
				if ( $firstcall ) {
					wp_localize_script( 'fg-fancybox-script', 'FancyBoxGalleryOptions', array(
						'title' => $fg_options['fb_title'],
						'speed' => $fg_options['fb_speed'],
						'effect' => $fg_options['fb_effect'],						
						)
					);
					$firstcall = 0;
				}
			break;
			case 'fancybox3' :
				wp_enqueue_script( 'fancybox-script', content_url( '/fancybox3/dist/jquery.fancybox.min.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'fg-fancybox-script', plugins_url( '/js/fg-fancybox3.js', __FILE__ ), array( 'jquery' ) );
				if ( $firstcall ) {
					wp_localize_script( 'fg-fancybox-script', 'FancyBoxGalleryOptions', array(
						'speed' => $fg_options['fb3_speed'],
						'loop' => $fg_options['fb3_loop'],
						'toolbar' => $fg_options['fb3_toolbar'],
						'infobar' => $fg_options['fb3_infobar'],
						'arrows' => $fg_options['fb3_arrows'],
						'fullscreen' => $fg_options['fb3_fullscreen'],
						'autostart' => $fg_options['fb3_autostart'],					
						)
					);
					$firstcall = 0;
				}
			break;

			case 'lightview' :
				global $is_IE;
				if ( $is_IE ) {
					wp_enqueue_script( 'excanvas', content_url( '/lightview/js/excanvas/excanvas.js', __FILE__  ), array( 'jquery' ) );
				}
				wp_enqueue_script( 'lightview_spinners', content_url( '/lightview/js/spinners/spinners.min.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'lightview-script', content_url( '/lightview/js/lightview/lightview.js', __FILE__ ) );   		
			break;
			case 'photoswipe' :
			case 'responsive-lightbox' :
			case 'easy-fancybox' :
			case 'slenderbox-plugin' :
			case 'none' :
				// Do nothing for now
			break;
		}	
	}

	/* --------- Folder Gallery Main Functions --------- */

	public function save_thumbnail( $path, $savepath, $th_width, $th_height ) {
		$fg_options = get_option( 'FolderGallery' );
		// Get picture
		$image = wp_get_image_editor( $path );
		if ( is_wp_error( $image ) ) return;		
		// Correct EXIF orientation	(of main picture)
		if ( function_exists( 'exif_read_data' ) && $fg_options['orientation'] == 1 ) {	
			$exif = @ exif_read_data( $path );
			if ( $exif !== FALSE ) {
				$orientation = @ $exif['Orientation'];
				if ( $orientation && $orientation != 1 ) {
					switch ( $orientation ) {
						case 2:
							$image->flip( FALSE, TRUE );
							$image->save( $path ); 	
							break;							
						case 3:
							$image->rotate( 180 );
							$image->save( $path ); 
							break;
						case 4:
							$image->flip( TRUE, FALSE );
							$image->save( $path ); 	
							break;
						case 5:
							$image->flip( FALSE, TRUE );
							$image->rotate( 90 );
							$image->save( $path ); 	
							break;	
						case 6:
							$image->rotate( -90 );
							$image->save( $path ); 
							break;
						case 7:
							$image->flip( FALSE, TRUE );
							$image->rotate( -90 );
							$image->save( $path ); 	
							break;	
						case 8:
							$image->rotate( 90 );
							$image->save( $path ); 
							break;			
					}
				}
			}
		}
		// Create thumbnail
		if ( 0 == $th_height ) { // 0 height => auto
			$size = $image->get_size();
			$width = $size['width'];
			$height = $size['height'];
			$th_height = floor( $height * ( $th_width / $width ) );
		}
		$image->resize( $th_width, $th_height, true );
		$image->save( $savepath );
	}

	public function myglob( $directory ) {
		$files = array();
		if( $handle = opendir( $directory ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( 'jpg' == $ext || 'png' == $ext || 'gif' == $ext || 'bmp' == $ext ) {
					$files[] = $file;
				}
			}
			closedir( $handle );
		}
		return $files;
	}
	
	public function file_size($size_in_bytes ) {
		if ($size_in_bytes < 1000) {
			return $size_in_bytes . ' B';
		} elseif ($size_in_bytes < 1000*1000) {
			$size_in_kb = (int) ($size_in_bytes/1000);
			return $size_in_kb . ' KB';	
		} else {
			$size_in_mb = (int) ($size_in_bytes/1000/1000);
			return $size_in_mb . ' MB';
		}
	}
	
	public function filedescription( $filepath, $filename ) {
		if ( file_exists( $filepath.'/descriptions.txt')) { // Check the resource is valid
			$file = fopen($filepath.'/descriptions.txt', 'r');
			while (($result = fgetcsv($file)) !== false)
			{
				$csvdata[] = $result;
			}
			fclose($file);
			foreach ($csvdata as $i => $line) {
				if ( $csvdata[$i][0] == $filename ) { return $csvdata[$i][1]; } 
			}
		}
	}	
	
	public function file_array( $directory , $sort) { // List all image files in $directory
		$cwd = getcwd();
		chdir( $directory );
		$files = glob( '*.{jpg,JPG,gif,GIF,png,PNG,jpeg,JPEG,bmp,BMP}' , GLOB_BRACE );
		// Free.fr doesn't accept glob function. Use a workaround		
		if ( 0 == count( $files ) ||  $files === FALSE ) {
			chdir( $cwd ); // Back to root
			$files = $this->myglob( $directory );
			chdir( $directory );
		}
		// Verify there's something to sort
		if ( 0 == count($files) || $files === FALSE ) {
			chdir( $cwd );	
			return array();		
		}
		// Remove first file if its name is !!!
		sort( $files ); // Sort by name
		$firstfile = $files[0];
		if ( $this->filename_without_extension( $firstfile ) == '!!!' ) {
			unset( $files[0] );
		} else {
			$firstfile = false;
		}
		// Sort files
		switch ( $sort ) {
			case 'random' :
				shuffle( $files );		
			break;
			case 'date' :
				array_multisort( array_map( 'filemtime' , $files ) , SORT_ASC, $files );			
			break;
			case 'date_desc' :
				array_multisort( array_map( 'filemtime' , $files ) , SORT_DESC, $files );			
			break;
			case 'size' :
				array_multisort( array_map( 'filesize' , $files ) , SORT_ASC, $files );			
			break;
			case 'size_desc' :
				array_multisort( array_map( 'filesize' , $files ) , SORT_DESC, $files );			
			break;
			case 'filename_desc' :
				rsort( $files );
			break;
			default:
				//sort( $files ); already done above
		}
		// Set back !!! file, if any
		if ( $firstfile ) {
			array_unshift( $files, $firstfile );
		}
		chdir( $cwd );	
		return $files;
	}

	public function filename_without_extension ( $filename ) {
		$info = pathinfo($filename);
		return basename($filename,'.'.$info['extension']);
	}

	function fg_home_url( $path = '', $scheme = null ) {
		static $home_url_fct = -1 ;
		if ( $home_url_fct < 0 ) {
			$fg_options = get_option( 'FolderGallery' );
			$home_url_fct = $fg_options['wpml'];
		}
		if ( 0 == $home_url_fct ) {
			return home_url($path, $scheme);
		}
		// WP get_home_url code (WPML fix)
		$url = get_option( 'home' );
		if ( is_ssl() && ! is_admin() ) {
			$scheme = 'https';
		} else {
			$scheme = parse_url( $url, PHP_URL_SCHEME );
		}
    	$url = set_url_scheme( $url, $scheme );
    	$url .= '/' . ltrim( $path, '/' );
    	return $url;
	}


	// Für Dirlist Search Parameter Wert in Zeile suchen - search function fulltext
	function in_array_reg($item , $array){
		return preg_match('/'.preg_quote($item, '/').'/i' , json_encode($array, JSON_UNESCAPED_SLASHES));
	}            
	
	// Verzeichnisliste ausgeben mit Erstelldatum und Moddatum [folderdir folder="wp-content/uploads/bilder/]" 
	public function meinedirliste( $atts ) {  // generate document/file download list
		extract( shortcode_atts( array(
			'folder'  => 'wp-content/uploads/pdf/',
			'protect'  => 0,
			'sort'	  => 'filename',
		), $atts ) );
		if (isset($_GET['sort'])) {
		  $sort = $_GET['sort'];
		} 
		$folder = rtrim( $folder, '/' ); // Remove trailing / from path
		if ( !is_dir( $folder ) ) {
			return '<p style="color:red;"><strong>' . __( 'Folder Gallery Error:', 'foldergallery' ) . '</strong> ' .
				sprintf( __( 'Unable to find the directory %s.', 'foldergallery' ), $folder ) . '</p>';	
		}
		// Add htaccess protection if enabled, else delete it
		if ( 1 == $protect ) {
			if ( !file_exists( $folder . '/.htaccess' ) && wp_is_writable( $folder ) ) {
				$content = "Options -Indexes\n";
				$content .= "deny from all";
				@file_put_contents( $folder . '/.htaccess', $content );
			}
		}
		else {
			if ( file_exists( $folder . '/.htaccess' ) && wp_is_writable( $folder ) ) {
				@unlink( $folder . '/.htaccess' );
			}
		}
		// Add or update descriptions-vorlage.txt if protection if enabled, else delete it
		if ( file_exists( $folder . '/descriptions-vorlage.txt' ) && wp_is_writable( $folder ) ) {
			@unlink( $folder . '/descriptions-vorlage.txt' );
		}	
			$filetypes="pdf docx xlsx pptx vsdx pubx exe zip mp3 mp4";
			$directory=$folder;
			$filed = array();
			$extensions = explode(" ", $filetypes);
			$extensions = array_merge( array_map( 'strtolower', $extensions ) , array_map( 'strtoupper', $extensions ) );		
			$content='';
			if( $handle = opendir( $directory ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $extensions ) ) {
						$filed[] = $file;
						//$content .= $file . ",\n";
					}	
				}
				closedir( $handle );
				sort( $filed );
				foreach( $filed as $file ) {
					$content .= $file . ",\n";
				}
			}
			@file_put_contents( $folder . '/descriptions-vorlage.txt', $content );
		// List files
		$filetypes="pdf docx xlsx pptx vsdx pubx exe zip mp3 mp4";
		if (!wp_style_is( 'font-awesome', 'enqueued' )) {
			$creatext='erstellt:'; $modtext='erstellt:';
		}  else {
			$creatext='<i class="fa fa-calendar-o"></i>';$modtext='<i class="fa fa-calendar-check-o"></i>';
		}
		$directory=$folder;
		$extensions = explode(" ", $filetypes);
		$extensions = array_merge( array_map( 'strtolower', $extensions ) , array_map( 'strtoupper', $extensions ) );		
		$files = array();
		if( $handle = opendir( $directory ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( in_array( $ext, $extensions ) ) {
					if ($file != '.' && $file != '..') {
						if ( file_exists( plugin_dir_path( __FILE__ ).'icons/'.$ext.'.png' ) ) {
							$fileicon = esc_url( plugin_dir_url(__FILE__). 'icons/'.$ext.'.png' );
						} else {
							$fileicon = esc_url( plugin_dir_url(__FILE__). 'icons/_blank.png' );
						}
						$dateigroesse = $this->file_size(filesize($directory ."/". $file));
						$mtime = date("Y-m-d H:i:s", filemtime($directory ."/". $file));
						$mtimed = date("d.m.y, H:i:s", filemtime($directory ."/". $file));
						$ctime = date("Y.m.d H:i:s", filectime($directory ."/". $file));
						$ctimed = date("d.m.Y, H:i:s", filectime($directory ."/". $file));
						$description = $this->filedescription($directory,$file);
						$content = '<tr><td><img style="width:50px;height:auto;" src="' . $fileicon . '"></td><td style="vertical-align:middle">';
						if ( 1 == $protect ) {
							global $wp;
							$hashwert = md5($folder ."/". $file . intval(date('Y-m-d H:i:s')) / 24 * 3600 );
							$dllink = '<a style="font-size:1.2em" title="'.$ext.'&#10;herunter laden" href="'. add_query_arg( array('dlid' => $folder ."/". $file,'code' => $hashwert) , home_url() ) . '">'.$file.'</a>';
						} else {
							$dllink = '<a style="font-size:1.2em" title="'.$ext.' anzeigen oder&#10;herunter laden" href="'. home_url() . "/". $folder ."/". $file.'">' . $file . ' </a>';
						}
						$content .= $dllink . '  &nbsp; '.$dateigroesse.'  &nbsp; '.$creatext.' '. $ctimed; 
						$content .= ' &nbsp; ' . $modtext . ' ' . $mtimed .' ' . ago(strtotime($mtime)). ' &nbsp; ' . $description . '</td></tr>';
						$file_object = array(
							'name' => $file,
							'size' => filesize($directory ."/". $file),
							'mtime' => $mtime,
							'ctime' => $ctime,
							'content' => $content,
							'description' => $description
						);
						$files[] = $file_object;
					}	
				}	
			}
			closedir( $handle );
		}

		// Sort files
		switch ( $sort ) {
			case 'size' :
				array_multisort(array_column($files, 'size'), SORT_ASC, $files);
			break;
			case 'size_desc' :
				array_multisort(array_column($files, 'size'), SORT_DESC, $files);
			break;
			case 'date' :
				array_multisort(array_column($files, 'mtime'), SORT_ASC, $files);
			break;
			case 'date_desc' :
				array_multisort(array_column($files, 'mtime'), SORT_DESC, $files);
			break;
			case 'filename_desc' :
				array_multisort(array_column($files, 'name'), SORT_DESC, $files);
			break;
			default:
				// nach filename
				array_multisort(array_column($files, 'name'), SORT_ASC, $files);
		}
		
		// Zeilen filtern, wenn Suchbegriff gesetzt
		$search='';
		if (isset($_GET['search'])) {
		  $search = sanitize_text_field( $_GET['search'] );
		}
		$nb_elem_per_page = 25;
		$number_of_pages = intval(count($files)/$nb_elem_per_page)+1;
		$page = isset($_GET['seite'])?intval($_GET['seite']):0;
		// ausgeben
		$gallery_code = '<div style="text-align:right"><form name="sorter" method="get"> '.intval(count($files)).' Dateien';
		$gallery_code.= ' <input type="text" placeholder="Suchbegriff" name="search" id="search" value="'.$search.'"> &nbsp; ';
		$gallery_code.= '<select name="sort"><option value="filename"';
		if ( 'filename' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Filename', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="filename_desc"';
		if ( 'filename_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Filename (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="date"';
		if ( 'date' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Date', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="date_desc"';
		if ( 'date_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Date (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="size"';
		if ( 'size' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Size', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="size_desc"';
		if ( 'size_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Size (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="random"';
		if ( 'random' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Random', 'foldergallery' ) . '</option>' ;
		$gallery_code.='</select><input type="submit" value="'. __( 'search and sort', 'foldergallery' ).'" /></form></div>';
		$gallery_code.='<table>';

		// Suchfilter, wenn filter gesetzt, nicht paginieren
		$fcount=0;
		$totalsize=0;
		if ( !empty($search)) { $nb_elem_per_page = 1000; $page = 0; }
		
		foreach (array_slice($files, $page*$nb_elem_per_page, $nb_elem_per_page) as $fout) { 
		// foreach( $files as $fout ) {
			 if ( !isset( $search ) || isset( $search ) && $this->in_array_reg($search, $fout) ) {
				 $fcount += 1;
				 $totalsize += intval($fout['size']);
				 $gallery_code.= $fout['content'];
			 }
			
		}	
		$gallery_code.='</table>';
		$gallery_code .= $fcount.' '.__( 'files with', 'foldergallery' ). ' '.$this->file_size($totalsize);
		// Page Navigation Footer
		if ( empty($search)) {
			for($i=0;$i<$number_of_pages;$i++){
				$seitennummer = intval($i+1);
				if ( $i !== $page ) { $klasse="page-numbers"; } else { $klasse="page-numbers current"; }
				$gallery_code .= ' &nbsp;<a class="'.$klasse.'" href="'.add_query_arg( array('sort'=>$sortorder,'search'=>$search,'seite'=>$i), home_url($wp->request) ).'">'.$seitennummer.'</a>';
			}	
		} 
		return $gallery_code;
	}


	public function fg_gallery( $atts ) { // Generate gallery
		$fg_options = get_option( 'FolderGallery' );
		extract( shortcode_atts( array(
			'folder'  => 'wp-content/uploads/',
			'title'   => 'Fotogalerie',
			'width'   => $fg_options['thumbnails_width'],
			'height'  => $fg_options['thumbnails_height'],
			'columns' => $fg_options['columns'],
			'margin'  => $fg_options['margin'],
			'padding' => $fg_options['padding'],
			'border'  => $fg_options['border'],
			'thumbnails' => $fg_options['thumbnails'],
			'options' => $fg_options['lw_options'],
			'caption' => $fg_options['caption'],
			'subtitle'=> false, // 1.3 compatibility
			'show_thumbnail_captions'=> $fg_options['show_thumbnail_captions'],
			'sort'	  => $fg_options['sort'],
		), $atts ) );
		if (isset($_GET['sort'])) {
		  $sort = $_GET['sort'];
		} 
		
		// 1.3 Compatibility
		if ( $subtitle ) $caption = $subtitle;

		$folder = rtrim( $folder, '/' ); // Remove trailing / from path

		if ( !is_dir( $folder ) ) {
			return '<p style="color:red;"><strong>' . __( 'Folder Gallery Error:', 'foldergallery' ) . '</strong> ' .
				sprintf( __( 'Unable to find the directory %s.', 'foldergallery' ), $folder ) . '</p>';	
		}

		$pictures = $this->file_array( $folder, $sort );

		$NoP = count( $pictures );		
		if ( 0 == $NoP ) {
			return '<p style="color:red;"><strong>' . __( 'Folder Gallery Error:', 'foldergallery' ) . '</strong> ' .
				sprintf( __( 'No picture available inside %s.', 'foldergallery' ), $folder ) . '</p>';
		}	
		// Cleanup parameters
		$width=intval($width);
		$height=intval($height);
		$margin=intval($margin);
		$border=intval($border);
		$padding=intval($padding);
		$thumbpagination='';

		// Cache folder
		$cache_folder = $folder . '/cache_' . $width . 'x' . $height;
		if ( ! is_dir( $cache_folder ) ) {
				@mkdir( $cache_folder, 0777 );
		}
		if ( ! is_dir( $cache_folder ) ) {
			return '<p style="color:red;"><strong>' . __( 'Folder Gallery Error:', 'foldergallery' ) . '</strong> ' .
				sprintf( __( 'Unable to create the thumbnails directory inside %s.', 'foldergallery' ), $folder ) . ' ' .
				__( 'Verify that this directory is writable (chmod 777).', 'foldergallery' ) . '</p>';
		}
		
		if ( 1 == $fg_options['permissions'] ) @chmod( $cache_folder, 0777);
		
		// Add or update descriptions-vorlage.txt if protection if enabled, else delete it
		if ( file_exists( $folder . '/descriptions-vorlage.txt' ) && wp_is_writable( $folder ) ) {
			@unlink( $folder . '/descriptions-vorlage.txt' );
		}	
			$filetypes="jpg png gif bmp jpeg";
			$directory=$folder;
			$filed = array();
			$extensions = explode(" ", $filetypes);
			$extensions = array_merge( array_map( 'strtolower', $extensions ) , array_map( 'strtoupper', $extensions ) );		
			$content='';
			if( $handle = opendir( $directory ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $extensions ) ) {
						$filed[] = $file;
						//$content .= $file . ",\n";
					}	
				}
				closedir( $handle );
				sort( $filed );
				foreach( $filed as $file ) {
					$content .= $file . ",\n";
				}
			}
			@file_put_contents( $folder . '/descriptions-vorlage.txt', $content );
		
		// Image and Thumbnail style
		if ( 'none' == $thumbnails ) {
			$thmbdivstyle = '';
			$imgstyle = "display: none;";
		} else {
			$thmbdivstyle = ' style="width:' . ($width + 2*$border + 2*$padding) . 'px;';
			$thmbdivstyle .= "margin:0px {$margin}px {$margin}px 0px;\"";
			$imgstyle = "width:{$width}px;";
			$imgstyle .= 'margin:0;';
			$imgstyle .= "padding:{$padding}px;";
			$imgstyle .= "border-width:{$border}px;";
		}

		$this->fg_scripts();			
		$lightbox_id = uniqid(); //md5( $folder . );
		// Main Div
		if ( 'photoswipe' == $fg_options['engine'] ) {
			$gallery_code = '<div class="fg_gallery gallery-icon">';
		} else {
			$gallery_code = '<div class="fg_gallery">';
		}		
		// Sortierselectbox
		$gallery_code.= '<div style="text-align:right"><form name="sorter" method="get"> <select name="sort">';
		$gallery_code.=	'<option value="filename"';
		if ( 'filename' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Filename', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="filename_desc"';
		if ( 'filename_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Filename (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="date"';
		if ( 'date' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Date', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="date_desc"';
		if ( 'date_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Date (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="size"';
		if ( 'size' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Size', 'foldergallery' ) . '</option>' ;		
		$gallery_code.=	'<option value="size_desc"';
		if ( 'size_desc' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Size (descending)', 'foldergallery' ) . '</option>' ;
		$gallery_code.=	'<option value="random"';
		if ( 'random' == $sort ) $gallery_code.= ' selected="selected"';
		$gallery_code.= '>' . __( 'Random', 'foldergallery' ) . '</option>' ;
		$gallery_code.='</select><input type="submit" value="sortieren" /></form></div>';

		
		// Default single thumbnail
		$thumbnail_idx = 0;
		// If first picture == !!! then skip it (but use it as 'single' thumbnail).
		if ( $this->filename_without_extension( $pictures[ 0 ] ) == '!!!' ) {
			$start_idx = 1 ;		
		} else {
			$start_idx = 0 ;
		}
		if ( intval($thumbnails) > 1 ) { $thumbpagination = intval($thumbnails); }
		// (single) thumbnail idx set as thumbnails=-n shortcode attribute		
		if ( intval($thumbnails) < 0 ) {
			$thumbnail_idx = - intval($thumbnails) -1;
			$thumbnails = 'single';
		}
			//// Startindex für Pagination aus url auslesen
			if (isset($_GET['seite'])) {
			  $seite = $_GET['seite'];
			  $start_idx = intval($thumbnails) * intval((intval($seite)-1));
			} else {
			  //Handle the case where there is no parameter
			}
			//// Startindex Pagination Ende	

		// Trick to display only the first thumbnails.		
		if ( intval($thumbnails) > 1 ) { // 1 = single should not be used
			$max_thumbnails_idx = intval($thumbnails) - 1 + $start_idx;
			$thumbnails = 'all';
		} else {
			$max_thumbnails_idx = $NoP - 1 + $start_idx;
		}

		// Main Loop
		for ( $idx = $start_idx ; $idx < $NoP ; $idx++ ) {
			// Set the thumbnail to use, depending of thumbnails option.
			if ( 'all' == $thumbnails ) {
				$thumbnail_idx = $idx;	
			}
			$thumbnail = $cache_folder . '/' . strtolower($pictures[ $thumbnail_idx ]);
			// Generate thumbnail
			if ( ! file_exists( $thumbnail ) ) {
				$this->save_thumbnail( $folder . '/' . $pictures[ $thumbnail_idx ], $thumbnail, $width, $height );
			}
			if ( ( $idx > $start_idx && 'all' != $thumbnails ) || $idx > $max_thumbnails_idx ) {
				$thmbdivstyle = ' style="display:none;"';
				$columns = 0;
			}
			// Set the Picture Caption
			switch ( $caption ) {
				case 'none' :
					$thecaption = '';
				break;
				case 'filename' :
					$thecaption = $pictures[ $idx ];
				break;
				case 'filenamewithoutextension' :
					$thecaption = $this->filename_without_extension( $pictures[ $idx ] );
				break;
				case 'smartfilename' :
					$thecaption = $this->filename_without_extension( $pictures[ $idx ] );
					$thecaption = preg_replace ( '/^\d+/' , '' , $thecaption );
					$thecaption = str_replace( '_', ' ', $thecaption );
				break;
				case 'modificationdater' :
					$moddate = filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600;				
 					$gmtoffset = get_option( 'gmt_offset' );
					$tmznstr = sprintf( "%+03d%02d", $gmtoffset, (abs($gmtoffset) - intval(abs($gmtoffset)))*60 );
					$thecaption = str_replace( '+0000', $tmznstr, date( 'r', $moddate));
				break;
				case 'modificationdatec' :
					$moddate = filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600;				
					$gmtoffset = get_option( 'gmt_offset' );
					$tmznstr = sprintf( "%+03d:%02d", $gmtoffset, (abs($gmtoffset) - intval(abs($gmtoffset)))*60 );
					$thecaption = str_replace( '+00:00', $tmznstr, date( 'c', $moddate)) ;
				break;
				case 'modificationdate' :
					$moddate = filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600;
					$thecaption = date_i18n( get_option( 'date_format' ), $moddate);					
				break;
				case 'modificationdateandtime' :
					$moddate = filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600;
					$thecaption = date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) , $moddate);					
				break;
				case 'namenumbersize' :
					// Caption mit Namen, Nummer, Größe
					$filesizer = $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					$thecaption = $this->filename_without_extension( $pictures[ $idx ]) ;
					if ( 'lightbox2' != $fg_options['engine'] ) {
						$thecaption .= ' &nbsp;(' . ($idx+1) . '/' . ($NoP) . ') ' . $filesizer;
					}	
				break;
				case 'namenumbersizedescr' :
					// Caption mit Namen, Nummer, Größe, Beschreibung
					$filesizer = $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					$thecaption = $this->filename_without_extension( $pictures[ $idx ]) ;
					if ( 'lightbox2' != $fg_options['engine'] ) {
						$thecaption .= ' &nbsp;(' . ($idx+1) . '/' . ($NoP) . ') ' . $filesizer . ' &nbsp; ' . $this->filedescription($folder,$pictures[ $idx ]);
					}	
				break;
				default :
					// Komplette Caption mit allen Daten anzeigen: Name Nummer, Size, Moddatum, Beschreibung
					$filesizer = $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					$thecaption = $this->filename_without_extension( $pictures[ $idx ]) ;
					if ( 'lightbox2' != $fg_options['engine'] ) {
						$moddate = date("d.m.Y H:i:s", filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600);
						// $moddate = date("d.m.Y H:i:s", filemtime( $folder . ' / ' . $pictures[ $idx ] ) );
						$thecaption .= ' &nbsp;(' . ($idx+1) . '/' . ($NoP) . ') ' . $filesizer . ' &nbsp;' . $moddate . ' &nbsp; ' . $this->filedescription($folder,$pictures[ $idx ]);
					}	
			}		
			// Let's start
			$gallery_code .= "\n<div class=\"fg_thumbnail\"$thmbdivstyle>\n";
			// Set the link
			switch ( $fg_options['engine'] ) {
				case 'lightbox2' :
					$gallery_code.= '<a title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '" data-lightbox="' . $lightbox_id . '">';
				break;
				case 'fancybox2' :				
					$gallery_code.= '<a class="fancybox-gallery" title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '" data-fancybox-group="' . $lightbox_id . '">';
				break;
				case 'fancybox3' :				
					$gallery_code.= '<a class="fancybox-gallery" title="' . $thecaption . '" data-caption="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '" data-fancybox="' . $lightbox_id . '">';
				break;
				case 'lightview' :
					if ( $options ) $options = " data-lightview-group-options=\"$options\"";
					$gallery_code .= '<a title="' . $thecaption . '" href="' . $this->fg_home_url( '/'  . $folder . '/' . $pictures[ $idx ] ) . '" class="lightview" data-lightview-group="' . $lightbox_id . '"' . $options . '>';
					$options = ''; // group-options required only once per group.
				break;
				case 'responsive-lightbox' :
					$gallery_code .= '<a rel="lightbox[' . $lightbox_id . ']" data-lightbox-gallery="' . $lightbox_id . '" title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '">';
				break;
				case 'easy-fancybox' :
					$gallery_code .= '<a class="fancybox" rel="' . $lightbox_id . '" title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '">';
				break;
				case 'slenderbox-plugin' :
					$gallery_code .= '<a data-sbox="' . $lightbox_id . '" title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '">';
				break;				
				case 'photoswipe' :
				case 'none' :
					$gallery_code .= '<a title="' . $thecaption . '" href="' . $this->fg_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '">';
				break;
			}
			// Show image (possibly hidden, but required for alt tag)
			$gallery_code .= '<img src="' . $this->fg_home_url( '/' . $thumbnail ) . '" style="' . $imgstyle . '" alt="' . $thecaption . '" />';
			// If no thumbnail, show link instead
			if ( 'none' == $thumbnails && $idx == $start_idx ) {
					$gallery_code .= '<span class="fg_title_link">' . $title . '</span>';
			}
			// Close link
			$gallery_code .= '</a>';
			// Display caption
			if ( $show_thumbnail_captions && 'all' == $thumbnails ) $gallery_code .= '<div class="fg_caption">' . $thecaption . '</div>';	
			// Display title
			if ( 'single' == $thumbnails && $idx == $start_idx && $title != '' ) {
				$gallery_code .= '<div class="fg_title">' . $title . '</div>';
			}
			$gallery_code .= '</div>';

			if ( $columns > 0 && $idx < $NoP-1 ) {
				if ( ( $idx + 1 - $start_idx) % $columns == 0 ) $gallery_code .= "\n" . '<br style="clear: both" />';
			}
		}
		if ( 'all' == $thumbnails ) {
			$gallery_code .= '<br style="clear: both" />';
		}
		// Pagination links //
		global $wp;
		$gallery_code .= "\n\n<div class='nav-links'>";
		if ( intval($thumbpagination) > 1 ) {
			for ( $plink = 0 ; $plink < $NoP ; $plink++ ) {
				if ( ($plink/intval($thumbpagination) + 1) <> intval($seite) ) { $klasse="page-numbers"; } else { $klasse="page-numbers current"; }
				if ($plink % intval($thumbpagination) == 0 ) { $gallery_code .= " &nbsp;<a title='Fotos ".($plink + 1)."-".($plink + intval($thumbpagination))."' class='".$klasse."' href='".add_query_arg( array(), home_url($wp->request) )."?seite=".($plink/intval($thumbpagination) + 1) ."&sort=".$sort ."'>". ($plink/intval($thumbpagination) + 1) ."</a>"; }
			}	
			$gallery_code .= "\n</div>\n";	
		}	
		$gallery_code .= "\n</div>\n";

		return $gallery_code;
	}

	/* --------- Folder Gallery Settings --------- */

	public function fg_menu() {
		add_options_page( 'Folder Gallery Settings', 'Folder Gallery', 'manage_options', 'foldergallery', array( $this, 'fg_settings' ) );
	}

	public function fg_settings_init() {
		register_setting( 'FolderGallery', 'FolderGallery', array( $this, 'fg_settings_validate' ) );
		$fg_options = get_option( 'FolderGallery' );
		if ( 'photoswipe' == $fg_options['engine'] ) {
			if ( ! is_plugin_active('photoswipe/photoswipe.php') ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( 'responsive-lightbox' == $fg_options['engine'] ) {
			if ( ! is_plugin_active('responsive-lightbox/responsive-lightbox.php') ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( 'easy-fancybox' == $fg_options['engine'] ) {
			if ( ! is_plugin_active('easy-fancybox/easy-fancybox.php') ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
		if ( 'slenderbox-plugin' == $fg_options['engine'] ) {
			if ( ! is_plugin_active('slenderbox/slenderbox.php') ) {
				$fg_options['engine'] = 'none';
				update_option( 'FolderGallery', $fg_options );
			}
		}
	}

	public function fg_plugin_action_links( $links ) { 
 		// Add a link to this plugin's settings page
 		$settings_link = '<a href="' . admin_url( 'options-general.php?page=foldergallery' ) . '">' . __( 'FGal Settings', 'foldergallery' ) . '</a>';
 		array_unshift( $links, $settings_link ); 
 		return $links; 
	}

	public function fg_settings_validate( $input ) {
		$input['columns']    = intval( $input['columns'] );
		$input['thumbnails_width']  = intval( $input['thumbnails_width'] );
			if ( 0 == $input['thumbnails_width'] ) $input['thumbnails_width'] = 150;
		$input['thumbnails_height'] = intval( $input['thumbnails_height'] );
		$input['border']            = intval( $input['border'] );
		$input['padding']           = intval( $input['padding'] );
		$input['margin']            = intval( $input['margin'] );
		if ( ! in_array( $input['sort'], array( 'filename','filename_desc','date','date_desc','random','size', 'size_desc' ) ) ) $input['sort'] = 'filename';
		if ( ! in_array( $input['thumbnails'], array( 'all','none','single' ) ) ) $input['thumbnails'] = 'all';
		if ( ! in_array( $input['fb_title'], array( 'inside','outside','float','over','null' ) ) ) $input['fb_title'] = 'all';
		if ( ! in_array( $input['fb_effect'], array( 'elastic','fade' ) ) ) $input['fb_effect'] = 'elastic';
		if ( ! in_array( $input['caption'], array( 'default','none','filename','filenamewithoutextension','smartfilename','modificationdater','modificationdatec','modificationdate','modificationdateandtime','namenumbersize','namenumbersizedescr'  ) ) ) $input['caption'] = 'default';
		$input['show_thumbnail_captions']     = intval( $input['show_thumbnail_captions'] );
		$input['fb_speed']             = intval( $input['fb_speed'] );
		$input['permissions']          = intval( @ $input['permissions'] );
		$input['orientation']          = intval( @ $input['orientation'] );
		$input['wpml']                 = intval( @ $input['wpml'] );
		$input['fb3_speed']            = intval( $input['fb3_speed'] );
		$input['fb3_loop']             = intval( @ $input['fb3_loop'] );
		$input['fb3_toolbar']          = intval( @ $input['fb3_toolbar'] );
		$input['fb3_infobar']          = intval( @ $input['fb3_infobar'] );
		$input['fb3_arrows']           = intval( @ $input['fb3_arrows'] );
		$input['fb3_fullscreen']       = intval( @ $input['fb3_fullscreen'] );	
		$input['fb3_autostart']        = intval( @ $input['fb3_autostart'] );	
		return $input;
	}

	public function fg_settings_default() {
		$defaults = array(
			'engine'			=> 'none',
			'sort'				=> 'filename',
			'border' 			=> 1,
			'padding' 			=> 2,
			'margin' 			=> 5,
			'columns' 	        => 0,
			'thumbnails_width' 	=> 160,
			'thumbnails_height' => 0,
			'lw_options'        => '',
			'thumbnails'		=> 'all',
			'caption'			=> 'default',
			'show_thumbnail_captions'		=> 0,
			'fb_title'			=> 'float',
			'fb_effect'			=> 'elastic',
			'fb_speed'			=> 0,
			'permissions'		=> 0,
			'orientation'		=> 0,
			'wpml'				=> 0,
			'fb3_speed' 		=> 3,
			'fb3_loop' 			=> 0,
			'fb3_toolbar' 		=> 1,
			'fb3_infobar' 		=> 0,
			'fb3_arrows' 		=> 1,
			'fb3_fullscreen' 	=> 0,
			'fb3_autostart'		=> 0,
		);
		return $defaults;
	}

	public function fg_option_field( $field, $label, $extra = 'px' ) {
		$fg_options = get_option( 'FolderGallery' );
		if ( $label ) {
			echo '<tr valign="top">' . "\n";
			echo '<th scope="row"><label for="' . $field . '">' . $label . "</label></th>\n<td>\n";
		}
		echo '<input id="' . $field . '" name="FolderGallery[' . $field . ']" type="text" value="' . $fg_options["$field"] . '" class="small-text"> ' . $extra . "\n" ;
		if ( $label ) echo "</td>\n</tr>\n";
	}

	public function fg_option_checkbox( $field, $label, $text) {
		$fg_options = get_option( 'FolderGallery' );
		if ( $label ) {
			echo '<tr><th>' . $label . '</th><td>';
		}
		echo '<label for="' . $field . '"><input name="FolderGallery[' . $field . ']" type="checkbox" id="' . $field . '" value="1"';
		if ( 1 == $fg_options["$field"] ) {
			echo ' checked="checked">';
		} else {
			echo '>';
		}
		echo $text . '</label>';
		if ( $label ) {
			echo "</td></tr>\n";
		} else {
			echo "<br />";
		}
	}
	
	public function fg_settings() {
		$fg_options = get_option( 'FolderGallery' );
		$upload_dir = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir'];
		echo '<div class="wrap">' . "\n";
		echo '<h2>' . __( 'Folder Gallery Slider Settings', 'foldergallery' ) . "</h2>\n";
		rssnews_admin_options();
		echo '<h3>' . __( 'Shortcodes', 'foldergallery' ) . "</h3>\n";
		echo '<div class="postbox">' . "\n";
		echo '<p><code>[foldergallery folder="wp-content/uploads/../bilder/" title="Foto-Galerie" columns=auto width=280 height=200 thumbnails="all" show_thumbnail_captions=1 border=0 padding=0 margin=0]</code><br>' . __('shortcode to display folder contents as a responsive paged gallery', 'foldergallery' ).'</p>';
		echo '<p><code>[folderdir folder="wp-content/uploads/bilder/" protect=1]</code><br>' . __('shortcode to display folder document contents as a table - protect=1 disables deep links and protects folder', 'foldergallery' ).'</p>';
		echo '<p><code>[csvtohtml_create path="mapfiles" source_files="sweden.csv;norway.csv;iceland.csv"]</code><br>'. __('html table from the files sweden.csv, norway.csv and iceland.csv that exists in', 'foldergallery' ) . ' ' . $upload_basedir . '/mapfiles/</p>';
		echo '<p><code>[csvtohtml_create source_type="guess" source_files="https://domain.de/liste.csv" include_cols="1,2,3" sort_cols="1,2" sort_cols_order="desc,asc"]</code><br>'. __('html table from the file if exists on the root of domain', 'foldergallery' ) . ' ' . $upload_basedir . '</p>';
		echo '<p><code>[rssdisplay excerpt="1" wordcount=25 url="https://domain.de/rss.xml" ]</code><br>'. __('shortcode to display rss feed in short or long form in pages/posts/html widgets', 'foldergallery' ) . '</p>';
		echo '<p><code>[ics_events url="https://ssl.pbcs.de/dcounter/calendar-ics.asp?action=history" items="8" sumonly="1"]</code><br>'. __('shortcode to display ICS or ical events in list or calendar on pages/shortcodes or html widget', 'foldergallery' ) . '</p>';
		echo '</div>';
		echo '<h3>' . __( 'Folder Gallery Settings', 'foldergallery' ) . "</h3>\n";
		echo '<div class="postbox">' . "\n";
		echo '<form method="post" action="options.php">' . "\n";
		settings_fields( 'FolderGallery' );
		echo "\n" . '<table class="form-table"><tbody>' . "\n";
		
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="engine">' . __( 'Gallery Engine', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderGallery[engine]" id="FolderGallery[engine]">' . "\n";	
			if ( is_dir( WP_CONTENT_DIR . '/lightbox' ) ) {
				echo "\t" .	'<option value="lightbox2"';
				if ( 'lightbox2' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Lightbox 2</option>' . "\n";	
			}		
			if ( is_dir( WP_CONTENT_DIR . '/fancybox' ) ) {
				echo "\t" .	'<option value="fancybox2"';
				if ( 'fancybox2' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Fancybox 2</option>' . "\n";
			}
			if ( is_dir( WP_CONTENT_DIR . '/fancybox3' ) ) {
				echo "\t" .	'<option value="fancybox3"';
				if ( 'fancybox3' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Fancybox 3</option>' . "\n";
			}
			if ( is_dir( WP_CONTENT_DIR . '/lightview' ) ) {
				echo "\t" .	'<option value="lightview"';
				if ( 'lightview' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Lightview 3</option>' . "\n";
			}
			if ( is_plugin_active('easy-fancybox/easy-fancybox.php') ) {
				echo "\t" .	'<option value="easy-fancybox"';
				if ( 'easy-fancybox' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Easy Fancybox (Plugin)</option>' . "\n";			
			}
			if ( is_plugin_active('responsive-lightbox/responsive-lightbox.php') ) {
				echo "\t" .	'<option value="responsive-lightbox"';
				if ( 'responsive-lightbox' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Responsive Lightbox (Plugin)</option>' . "\n";			
			}
			if ( is_plugin_active('photoswipe/photoswipe.php') ) {
				echo "\t" .	'<option value="photoswipe"';
				if ( 'photoswipe' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Photo Swipe (Plugin)</option>' . "\n";			
			}	
			if ( is_plugin_active('slenderbox/slenderbox.php') ) {
				echo "\t" .	'<option value="slenderbox-plugin"';
				if ( 'slenderbox-plugin' == $fg_options['engine'] ) echo ' selected="selected"';
				echo '>Slenderbox (Plugin)</option>' . "\n";			
			}	
			echo "\t" .	'<option value="none"';
				if ( 'none' == $fg_options['engine'] ) echo ' selected="selected"';
		echo '>' . __( 'None', 'foldergallery') . '</option>' . "\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";
		echo "</tbody></table>\n";
		echo '<h3 class="title">' . __('Thumbnail Settings','foldergallery') . "</h3>\n";
		echo '<table class="form-table"><tbody>' . "\n";

		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="thumbnails">' . __( 'Display Thumbnails', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderGallery[thumbnails]" id="FolderGallery[thumbnails]">' . "\n";	
			echo "\t" .	'<option value="all"';
				if ( 'all' == $fg_options['thumbnails'] ) echo ' selected="selected"';
				echo '>' . __( 'All', 'foldergallery' ) . '</option>' . "\n";		
			echo "\t" .	'<option value="single"';
				if ( 'single' == $fg_options['thumbnails'] ) echo ' selected="selected"';
				echo '>' . __( 'Single', 'foldergallery' ) . '</option>' . "\n";
			echo "\t" .	'<option value="none"';
				if ( 'none' == $fg_options['thumbnails'] ) echo ' selected="selected"';
				echo '>' . __( 'None', 'foldergallery' ) . '</option>' . "\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";

		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="sort">' . __( 'Sort Pictures by', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderGallery[sort]" id="FolderGallery[sort]">' . "\n";	
			echo "\t" .	'<option value="filename"';
				if ( 'filename' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Filename', 'foldergallery' ) . '</option>' . "\n";		
			echo "\t" .	'<option value="filename_desc"';
				if ( 'filename_desc' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Filename (descending)', 'foldergallery' ) . '</option>' . "\n";
			echo "\t" .	'<option value="date"';
				if ( 'date' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Date', 'foldergallery' ) . '</option>' . "\n";		
			echo "\t" .	'<option value="date_desc"';
				if ( 'date_desc' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Date (descending)', 'foldergallery' ) . '</option>' . "\n";
			echo "\t" .	'<option value="size"';
				if ( 'size' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Size', 'foldergallery' ) . '</option>' . "\n";		
			echo "\t" .	'<option value="size_desc"';
				if ( 'size_desc' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Size (descending)', 'foldergallery' ) . '</option>' . "\n";
			echo "\t" .	'<option value="random"';
				if ( 'random' == $fg_options['sort'] ) echo ' selected="selected"';
				echo '>' . __( 'Random', 'foldergallery' ) . '</option>' . "\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";

		$this->fg_option_field( 'columns', __( 'Columns', 'foldergallery' ), __( '(0 = auto)', 'foldergallery' ) );
		$this->fg_option_field( 'thumbnails_width', __( 'Thumbnails Width', 'foldergallery' ) );
		$this->fg_option_field( 'thumbnails_height', __( 'Thumbnails Height', 'foldergallery' ), ' px ' . __( '(0 = auto)', 'foldergallery' ) );
		$this->fg_option_field( 'border', __( 'Picture Border', 'foldergallery' ) );
		$this->fg_option_field( 'padding', __( 'Padding', 'foldergallery' ) );
		$this->fg_option_field( 'margin', __( 'Margin', 'foldergallery' ) );

		// show_thumbnail_captions
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="show_thumbnail_captions">' . __( 'Show Thumbnail Captions', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderGallery[show_thumbnail_captions]" id="FolderGallery[show_thumbnail_captions]">' . "\n";		
			echo "\t" .	'<option value="0"';
				if ( '0' == $fg_options['show_thumbnail_captions'] ) echo ' selected="selected"';
				echo '>'. __('No', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="1"';
				if ( '1' == $fg_options['show_thumbnail_captions'] ) echo ' selected="selected"';
				echo '>' . __('Yes', 'foldergallery') . '</option>' . "\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";

		echo "</tbody></table>\n";
		echo '<h3 class="title">' . __('Lightbox Settings','foldergallery') . "</h3>\n";
		echo '<table class="form-table"><tbody>' . "\n";

		// Caption
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="caption">' . __( 'Caption Format', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderGallery[caption]" id="FolderGallery[caption]">' . "\n";		
			echo "\t" .	'<option value="default"';
				if ( 'default' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>'. __('Default (title number size date description)', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="filename"';
				if ( 'filename' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Filename', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="filenamewithoutextension"';
				if ( 'filenamewithoutextension' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Filename without extension', 'foldergallery') . '</option>' . "\n";	
			echo "\t" .	'<option value="smartfilename"';
				if ( 'smartfilename' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Smart Filename', 'foldergallery') . '</option>' . "\n";	
			echo "\t" .	'<option value="modificationdate"';
				if ( 'modificationdate' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Modification date', 'foldergallery') . '</option>' . "\n";				
			echo "\t" .	'<option value="modificationdateandtime"';
				if ( 'modificationdateandtime' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Modification date and time', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="namenumbersize"';
				if ( 'namenumbersize' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Name Number Filesize', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="namenumbersizedescr"';
				if ( 'namenumbersizedescr' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Name Number Filesize Description', 'foldergallery') . '</option>' . "\n";
			echo "\t" .	'<option value="modificationdater"';
				if ( 'modificationdater' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Modification date (RFC 2822)', 'foldergallery') . '</option>' . "\n";	
			echo "\t" .	'<option value="modificationdatec"';
				if ( 'modificationdatec' == $fg_options['caption'] ) echo ' selected="selected"';
				echo '>' . __('Modification date (ISO 8601)', 'foldergallery') . '</option>' . "\n";	
			echo "\t" .	'<option value="none"';
				if ( 'none' == $fg_options['caption'] ) echo ' selected="selected"';
			echo '>' . __( 'None', 'foldergallery') . '</option>' . "\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";


		// Lightview		
		if ( 'lightview' == $fg_options['engine'] ) {			
			echo '<tr valign="top">' . "\n";
			echo '<th scope="row"><label for="lw_options">' . __( 'Lightview Options', 'foldergallery' ) . '</label></th>' . "\n";
			echo '<td><textarea id="lw_options" rows="5" cols="50" name="FolderGallery[lw_options]" class="large-text code">' . $fg_options['lw_options'] . "</textarea>\n";
			echo '<p class="description">' . __( 'Lightview default options, comma-separated.', 'foldergallery' );
			echo " E.g., <code>controls: { slider: false }, skin: 'mac'</code>. ";
			echo __( 'For details, see:', 'foldergallery' );
			echo ' <a href="http://projects.nickstakenburg.com/lightview/documentation/options" target="_blank">http://projects.nickstakenburg.com/lightview</a>.</p>' . "\n";
			echo "</td>\n";
			echo "</tr>\n";
		} else {
			echo '<input type="hidden" name="FolderGallery[lw_options]" id="lw_options" value="' . $fg_options['lw_options'] . '" />';
		}		
		// Fancybox 2 options
		if ( 'fancybox2' == $fg_options['engine'] ) {
			echo '<tr valign="top">' . "\n";
			echo '<th scope="row"><label for="fb_title">' . __( 'Fancybox Caption Style', 'foldergallery' ) . '</label></th>' . "\n";
			echo '<td><select name="FolderGallery[fb_title]" id="FolderGallery[fb_title]">' . "\n";		
				echo "\t" .	'<option value="inside"';
					if ( 'inside' == $fg_options['fb_title'] ) echo ' selected="selected"';
					echo '>' . __( 'Inside', 'foldergallery' ) . '</option>' . "\n";	
				echo "\t" .	'<option value="outside"';
					if ( 'outside' == $fg_options['fb_title'] ) echo ' selected="selected"';
					echo '>' . __( 'Outside', 'foldergallery' ) . '</option>' . "\n";			
				echo "\t" .	'<option value="over"';
					if ( 'over' == $fg_options['fb_title'] ) echo ' selected="selected"';
					echo '>' . __( 'Over', 'foldergallery' ) . '</option>' . "\n";			
				echo "\t" .	'<option value="float"';
					if ( 'float' == $fg_options['fb_title'] ) echo ' selected="selected"';
					echo '>' . __( 'Float', 'foldergallery' ) . '</option>' . "\n";
				echo "\t" .	'<option value="null"';
					if ( 'null' == $fg_options['fb_title'] ) echo ' selected="selected"';
					echo '>' . __( 'None', 'foldergallery' ) . '</option>' . "\n";
			echo "</select>\n";
			echo "</td>\n</tr>\n";
			
			echo '<tr valign="top">' . "\n";
			echo '<th scope="row"><label for="fb_effect">' . __( 'Fancybox Transition', 'foldergallery' ) . '</label></th>' . "\n";
			echo '<td><select name="FolderGallery[fb_effect]" id="FolderGallery[fb_effect]">' . "\n";		
				echo "\t" .	'<option value="elastic"';
					if ( 'elastic' == $fg_options['fb_effect'] ) echo ' selected="selected"';
					echo '>' . 'Elastic' . '</option>' . "\n";	
				echo "\t" .	'<option value="fade"';
					if ( 'fade' == $fg_options['fb_effect'] ) echo ' selected="selected"';
					echo '>' . 'Fade' . '</option>' . "\n";			
			echo "</select>\n";
			echo "</td>\n</tr>\n";
			
			$this->fg_option_field( 'fb_speed', __( 'Autoplay Speed', 'foldergallery' ), ' seconds ' . __( '(0 = off)', 'foldergallery' ) );
			
		} else {
			echo '<input type="hidden" name="FolderGallery[fb_title]" id="fb_title" value="' . $fg_options['fb_title'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb_effect]" id="fb_effect" value="' . $fg_options['fb_effect'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb_speed]" id="fb_speed" value="' . $fg_options['fb_speed'] . '" />';
		}
			
		// Fancybox 3 options
		if ( 'fancybox3' == $fg_options['engine'] ) {
			
			echo '<tr><th scope="row">FancyBox 3</th>';
			echo '<td><fieldset>';
			$this->fg_option_checkbox( 'fb3_loop', '', __('Enable infinite gallery navigation', 'foldergallery' ) );	
			$this->fg_option_checkbox( 'fb3_toolbar', '', __('Display toolbar (buttons at the top)', 'foldergallery' ) );
			$this->fg_option_checkbox( 'fb3_infobar','', __('Display infobar (counter and arrows at the top)', 'foldergallery' ) );
			$this->fg_option_checkbox( 'fb3_arrows', '', __('Display navigation arrows at the screen edges', 'foldergallery' ) );
			$this->fg_option_checkbox( 'fb3_fullscreen', '', __('Display images fullscreen', 'foldergallery' ) );
			$this->fg_option_checkbox( 'fb3_autostart', '', __('Start slideshow automatically', 'foldergallery' ) );
			echo __( 'Slideshow speed', 'foldergallery' ) . ': ' ;
			$this->fg_option_field( 'fb3_speed', '', __(' seconds ', 'foldergallery' ) );
			echo '</fieldset></td></tr>';
			
		} else {
			echo '<input type="hidden" name="FolderGallery[fb3_loop]" id="fb3_loop" value="' . $fg_options['fb3_loop'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_toolbar]" id="fb3_toolbar" value="' . $fg_options['fb3_toolbar'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_infobar]" id="fb3_infobar" value="' . $fg_options['fb3_infobar'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_arrows]" id="fb3_arrows" value="' . $fg_options['fb3_arrows'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_fullscreen]" id="fb3_fullscreen" value="' . $fg_options['fb3_fullscreen'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_autostart]" id="fb3_autostart" value="' . $fg_options['fb3_autostart'] . '" />';
			echo '<input type="hidden" name="FolderGallery[fb3_speed]" id="fb3_speed" value="' . $fg_options['fb3_speed'] . '" />';
		}		
		
		// Misc Settings
		echo "</tbody></table>\n";
		echo '<h3 class="title">' . __('Misc Settings','foldergallery') . "</h3>\n";
		echo '<table class="form-table"><tbody>' . "\n";
		$this->fg_option_checkbox( 'permissions', __('Permissions', 'foldergallery'), __('Force 777 permissions on cache folders','foldergallery') );
		if ( function_exists( 'exif_read_data' ) ) {		
			$this->fg_option_checkbox( 'orientation', __('Orientation', 'foldergallery'), __('Correct picture orientation according to EXIF tag. (Pictures will be overwritten.)','foldergallery') );
		}
		$this->fg_option_checkbox( 'wpml', __('WPML', 'foldergallery'), __('Fix WPML paths','foldergallery') );
		echo "</tbody></table>\n";
		echo '</div>';
		submit_button();
		echo "</form></div>\n";
	}
		
} //End Of Class

//
//   Jetzt Folder Slider Classes ----------------------------------------------------------------------------------------------------

new folderslider();

class folderslider{

	private $slider_no = 0;
	
	function __construct() {		
		add_action( 'admin_menu', array( $this, 'fsd_menu' ) );	
		add_action( 'admin_init', array( $this, 'fsd_settings_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'fsd_styles' ) );
		add_shortcode( 'folderslider', array( $this, 'fsd_slider' ) );
		add_action('plugins_loaded', array( $this, 'fsd_init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'fsd_plugin_action_links' ) );
	}

	public function folderslider() {
		self::__construct();
	}

	public function fsd_init() {
		if ( !is_admin() ) add_filter( 'widget_text', array( $this, 'fsd_widget_shortcode' ), 11 );
		$fsd_options = get_option( 'FolderSlider' );
		if ( empty( $fsd_options ) ) {
			update_option( 'FolderSlider', $this->fsd_settings_default() );
			return;
		}
		if ( ! isset( $fsd_options['css'] ) ) {
			$fsd_options['css'] = 'shadow';
			if ( isset( $fsd_options['shadow'] ) ) {
				if ( !$fsd_options['shadow'] ) {
					$fsd_options['css'] = 'noborder';
				}
			}
			update_option( 'FolderSlider', $fsd_options );
		}
		if ( ! isset( $fsd_options['wpml'] ) ) { // 1.1.3 update
			$fsd_options['wpml'] = 0;
			update_option( 'FolderSlider', $fsd_options );
		}
	}
	
	public function fsd_widget_shortcode( $content ) {
		if ( false === stripos( $content, '[folderslider' ) ) {
			return $content;
		} else {
			return do_shortcode( $content );
		}
	}
	
	public function fsd_styles() {
		wp_enqueue_style( 'bxslider-style', plugins_url( 'jquery.bxslider/jquery.bxslider.min.css', __FILE__ ) );
		wp_enqueue_style( 'fsd-style', plugins_url( 'style.css', __FILE__ ) );
	}

	public function fsd_scripts( $param, $num ) {
		wp_enqueue_script( 'bxslider-script', plugins_url( 'jquery.bxslider/jquery.bxslider.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'fsd_slider-script', plugins_url( 'slider.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'fsd_slider-script', 'FSDparam' . $num , $param );
	}

	public function file_size($size_in_bytes ) {
		if ($size_in_bytes < 1000) {
			return $size_in_bytes . ' B';
		} elseif ($size_in_bytes < 1000*1000) {
			$size_in_kb = (int) ($size_in_bytes/1000);
			return $size_in_kb . ' KB';	
		} else {
			$size_in_mb = (int) ($size_in_bytes/1000/1000);
			return $size_in_mb . 'MB';
		}
	}

	public function file_array( $directory ) { // List all JPG & PNG & GIF files in $directory
		$files = array();
		if( $handle = opendir( $directory ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( 'jpg' == $ext || 'png' == $ext || 'gif' == $ext ) {
					$files[] = $file;
				}
			}
			closedir( $handle );
		}
		sort( $files );
		return $files;
	}

	public function filename_without_extension ( $filename ) {
		$info = pathinfo($filename);
		return basename($filename,'.'.$info['extension']);
	}

	public function smartfilename ( $filename ) {
		$filename = $this->filename_without_extension ( $filename );
		$filename = preg_replace ( '/^\d+/' , '' , $filename );
		$filename = str_replace( '_', ' ', $filename );
		return $filename;
	}
	
	function fsd_home_url( $path = '', $scheme = null ) {
		static $home_url_fct = -1 ;
		if ( $home_url_fct < 0 ) {
			$fsd_options = get_option( 'FolderSlider' );
			$home_url_fct = $fsd_options['wpml'];
		}
		if ( 0 == $home_url_fct ) {
			return home_url($path, $scheme);
		}
		// WP get_home_url code (WPML fix)
		$url = get_option( 'home' );
		if ( is_ssl() && ! is_admin() ) {
			$scheme = 'https';
		} else {
			$scheme = parse_url( $url, PHP_URL_SCHEME );
		}
    	$url = set_url_scheme( $url, $scheme );
    	$url .= '/' . ltrim( $path, '/' );
    	return $url;
	}
					
	public function fsd_slider( $atts ) { // Generate slider
		$fsd_options = get_option( 'FolderSlider' );
		extract( shortcode_atts( array(
			'folder'  => 'wp-content/uploads/',
			'width'   => $fsd_options['width'],
			'height'  => $fsd_options['height'],
			'mode'    => $fsd_options['mode'],
			'controls' => $fsd_options['controls'],
			'autostart' => $fsd_options['autostart'],
			'playcontrol' => $fsd_options['playcontrol'],
			'speed' => $fsd_options['speed'],
			'captions' => $fsd_options['captions'],
			'pager' => $fsd_options['pager'],
			'css'=> $fsd_options['css'],
			'minslides'=> $fsd_options['minslides'],
			'maxslides'=> $fsd_options['maxslides'],
			'moveslides'=> $fsd_options['moveslides'],
			'adaptiveheight'=> $fsd_options['adaptiveheight'],
		), $atts ) );

		$folder = rtrim( $folder, '/' ); // Remove trailing / from path

		// Parameter für Recent Posts Carousel: Folder ist 'recentposts'
		if ( $folder == 'recentposts') {
			++$this->slider_no;
			// Optionen für Carousel modus und adaptive height aus
			$param = array( 'width'=>$width, 'controls'=>($controls == 'true'), 'auto'=>($autostart == 'true'), 'playcontrol'=>($playcontrol == 'true'), 'speed'=>intval($speed*1000), 'captions'=>($captions != 'none'), 'pager'=>($pager == 'true'), 'mode'=>$mode, 'adaptiveheight'=>$adaptiveheight, 'minslides'=>$minslides, 'maxslides'=>$maxslides, 'moveslides'=>$moveslides );
			$this->fsd_scripts($param, $this->slider_no);
			$picture_size = "";
			if ( $width > 0)  $picture_size = " width=\"$width\"";
			if ( $height > 0)  $picture_size .= " height=\"$height\"";
			// -- Exclude Cat: 16 GWS, 10 PB ----- mit CatBildern----------
			$siteurl = preg_replace('#^https?://#', '', get_site_url() );
			if ( $siteurl == 'tech-nachrichten.de' ) {
				$hidecat='16';
			} elseif ( $siteurl == 'wp.pbcs.de' ) {
				$hidecat='10';
			} else {
				$hidecat='';
			}
			$sticky = get_option( 'sticky_posts' );
			$args = array (
				'post_type'              => 'post',
				'nopaging'               => false,
				'posts_per_page'         => '8',
				'order'                  => 'DESC',
				'orderby'                => 'date',
				'ignore_sticky_posts'	 => '1',
				'post__not_in' 			 => $sticky,
				'category__not_in'		 => $hidecat,
				'showposts'				 => '8'
			);
			global $post;
			query_posts( $args );
			$slider_code = '<div class="bx-wrapper-noborder" style="font-size:1.6em;text-align:center">'. "\n";
			$slider_code .= '<ul class="bxslider bxslider' . $this->slider_no . '">';
			while (have_posts()) : the_post();
				$category = get_the_category(); 
				$cuttext = get_the_title();
				// $cuttext .= '<small> - '. ago(get_the_modified_time( 'U, d.m.Y H:i:s', false, $post, true )) . '</small>';
				if ( class_exists('ZCategoriesImages') && z_taxonomy_image_url($category[0]->term_id) != NULL ) {
					$cuttitle = '<img style="max-height:280px;min-height:190px" title="'.$cuttext.'" src="' . z_taxonomy_image_url($category[0]->term_id) . '" ' .$picture_size.'>';
				} else {
					$cuttitle = '<img style="max-height:280px;min-height:190px" title="'.$cuttext.'" src="' . esc_url( plugin_dir_url(__FILE__). 'icons/slider-newsposts.jpg' ) . '" ' .$picture_size.'>';
				}
				$slider_code .= '<li><a href="'.get_the_permalink().'">'. $cuttitle.'</a></li>';
			endwhile;
			wp_reset_query();
			$slider_code .= "</ul>\n</div>\n";
			// Post Ticker Ende -------------------
			return $slider_code;
		}

		// Ordnerstruktur vorhanden?
		if ( !is_dir( $folder ) ) {
			return '<p style="color:red;"><strong>Folder Slider Fehler: </strong>Verzeichnis nicht gefunden ' . $folder . '</p>' ;
		}
		$pictures = $this->file_array( $folder );
		$NoP = count( $pictures );
		if ( 0 == $NoP ) {
			return '<p style="color:red;"><strong>Folder Slider Fehler: </strong>Kein Bild im Verzeichnis '. $folder . '</p>';
		} else {
			//Calculate Slider's Width from first picture
			if ( $width == 0 ) { 
				$image = wp_get_image_editor( $folder . '/' . $pictures[ 0 ] );
				if ( ! is_wp_error( $image ) ) {
					$size = $image->get_size();
					$width = $size['width'];
				}
			}
		}	
		
		// Set JS parameters
		++$this->slider_no;
		// Optionen für Carousel modus und adaptive height aus
		$param = array( 'width'=>$width, 'controls'=>($controls == 'true'), 'auto'=>($autostart == 'true'), 'playcontrol'=>($playcontrol == 'true'), 'speed'=>intval($speed*1000), 'captions'=>($captions != 'none'), 'pager'=>($pager == 'true'), 'mode'=>$mode, 'adaptiveheight'=>$adaptiveheight, 'minslides'=>$minslides, 'maxslides'=>$maxslides, 'moveslides'=>$moveslides );
		$this->fsd_scripts($param, $this->slider_no);
		$picture_size = "";
		if ( $width > 0)  $picture_size = " width=\"$width\"";
		if ( $height > 0)  $picture_size .= " height=\"$height\"";

		switch ( $css ) {
			case 'noborder':
				$slider_code = '<div class="bx-wrapper-noborder">'. "\n";
			break;
			case 'shadownoborder':
				$slider_code = '<div class="bx-wrapper-shadow">'. "\n";
			break;
			case 'black-border':
				$slider_code = '<div class="bx-wrapper-border-black">'. "\n";
			break;
			case 'white-border':
				$slider_code = '<div class="bx-wrapper-border-white">'. "\n";
			break;
			case 'gray-border':
				$slider_code = '<div class="bx-wrapper-border-gray">'. "\n";
			break;
			case 'shadow':
				$slider_code = '<div class="bx-wrapper-border-shadow">'. "\n";
			break;
			default:
				$slider_code = '<div>'. "\n";
			break;
		}
		
		$slider_code .= '<ul class="bxslider bxslider' . $this->slider_no . '">';
		
		for ( $idx = 0 ; $idx < $NoP ; $idx++ ) {
			switch ( $captions ) {
				case 'filename':
					$title = $pictures[ $idx ];
					break;
				case 'filenamewithoutextension':
					$title = $this->filename_without_extension( $pictures[ $idx ] );
					break;
				case 'smartfilename':
					$title = strtoupper($this->smartfilename( $pictures[ $idx ] ) );
					break;
				case 'filenamesize':
					$title = strtoupper($this->smartfilename( $pictures[ $idx ] ) ) . ' - ' . $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					break;
				case 'filenamesizedate':
					$moddate = filectime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600;
					$thecaption = date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) , $moddate);					
					$title = strtoupper($this->smartfilename( $pictures[ $idx ] ) ) . ' - ' . $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					$title .= ' <br> Bild '.($idx+1) .' - '. $thecaption . ' <br> vor '. human_time_diff($moddate,current_time('U'));
					break;
				default:
					$title = '';
				break;
			}	
			// Bei Doppelklick öffnet sich das Bild z.B. für eine installierte Lightbox mit Zoom
			if ( $fsd_options['lightboxlink'] == 1 ) {
				$sliderlink ='<a title="Doppelklicken zum Zoomen" style="cursor:zoom-in" href="' . $this->fsd_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '">';
				$sliderlinkend = '</a>';
			}	
			$slider_code .= '<li>' . $sliderlink . '<img src="' . $this->fsd_home_url( '/' . $folder . '/' . $pictures[ $idx ] ) . '"';
			$slider_code .= $picture_size;
			if ( $title ) {
				$slider_code .= " title=\"$title\"";
				$slider_code .= " alt=\"$title\"";
			} else {
				$slider_code .= ' alt="' . $pictures[ $idx ] . '"' ;
			}
			$slider_code .= " />" . $sliderlinkend . "</li>\n";
		}
		
		$slider_code .= "</ul>\n</div>\n";
		
		return $slider_code;
	}

/* --------- Folder Slider Settings --------- */

	public function fsd_settings_default() {
		$defaults = array(
			'width'   => 0,
			'height'  => 0,
			'mode'    => 'horizontal',
			'controls' => true,
			'playcontrol' => true,
			'autostart' => true,
			'speed' => 3,
			'captions' => 'none',
			'pager' => true,
			'css' => 'shadow',
			'wpml' => 0,
			'lightboxlink' => 0,
			'minslides' => 1,
			'maxslides' => 5,
			'moveslides' => 1,
			'adaptiveheight' => 0,
		);
		return $defaults;
	}

	public function fsd_menu() {
		add_options_page( 'Folder Slider Settings', 'Folder Slider', 'manage_options', 'folder-slider', array( $this, 'fsd_settings' ) );
	}

	public function fsd_settings_init() {
		register_setting( 'FolderSlider', 'FolderSlider', array( $this, 'fsd_settings_validate' ) );
	}

	public function fsd_plugin_action_links( $links ) { 
 		// Add a link to this plugin's settings page
 		$settings_link = '<a href="' . admin_url( 'options-general.php?page=folder-slider' ) . '">' . __( 'FSlider Settings', 'foldergallery' ) . '</a>';
 		array_unshift( $links, $settings_link ); 
 		return $links; 
	}

	public function fsd_settings_validate( $input ) {
		$input['minslides']  = intval( $input['minslides'] );
		if ( 0 == $input['minslides'] ) $input['minslides'] = 1;
		$input['maxslides']  = intval( $input['maxslides'] );
		if ( 0 == $input['maxslides'] ) $input['maxslides'] = 1;
		$input['moveslides']  = intval( $input['moveslides'] );
		if ( 0 == $input['moveslides'] ) $input['moveslides'] = 1;
		$input['width']  = intval( $input['width'] );
		$input['height'] = intval( $input['height'] );
		if ( ! in_array( $input['mode'], array( 'horizontal','vertical','fade' ) ) ) $input['mode'] = 'horizontal';
		if ( ! in_array( $input['captions'], array( 'none','filename','filenamewithoutextension','smartfilename','filenamesize,','filenamesizedate' ) ) ) $input['captions'] = 'none';
		if ( ! in_array( $input['css'], array( 'noborder','shadow','shadownoborder','black-border','white-border','gray-border' ) ) ) $input['css'] = 'noborder';
		$input['speed']          = floatval( $input['speed'] );
		if ( 0 == $input['speed'] ) $input['speed'] = 5;
		$input['controls'] = ( 1 == @ $input['controls'] );
		$input['adaptiveheight'] = ( 1 == @ $input['controls'] );
		$input['playcontrol'] = ( 1 == @ $input['playcontrol'] );
		$input['autostart'] = ( 1 == @ $input['autostart'] );
		$input['pager'] = ( 1 == @ $input['pager'] );
		$input['wpml'] = intval( @ $input['wpml'] );
		$input['lightboxlink'] = intval( @ $input['lightboxlink'] );
		return $input;
	}

	public function fsd_option_field( $field, $label, $extra = 'px' ) {
		$fsd_options = get_option( 'FolderSlider' );
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="' . $field . '">' . $label . "</label></th>\n";
		echo '<td><input id="' . $field . '" name="FolderSlider[' . $field . ']" type="text" value="' . $fsd_options["$field"] . '" class="small-text"> ' . $extra . "</td>\n";
		echo "</tr>\n";
	}

	public function fsd_settings()
	{
		$fsd_options = get_option( 'FolderSlider' );
		echo '<div class="wrap">' . "\n";
		echo '<h2>' . __( 'Folder Slider Settings', 'foldergallery' ) . "</h2>\n";
		echo '<div class="postbox">' . "\n";
		echo '<form method="post" action="options.php">' . "\n";
		settings_fields( 'FolderSlider' );
		echo "\n" . '<table class="form-table"><tbody>' . "\n";
		echo '<tr valign="top"><td colspan=2>' . "\n";
		echo "<code>[folderslider folder='wp-content/uploads/bilder/' width=400 height=0 speed=2.5 autostart=true captions=smartfilename controls=true pager=false playcontrol=false adaptiveheight=false maxslides=4 minslides=1 moveslides=1]</code><br>zeigt Slider Karussell an";
		echo ", Parameter: <code>folder=recentposts</code> zeigt letzte 8 Posts als Slider an\n";
		echo "</td>\n</tr>\n";		
		// Transition Mode
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="mode">' . __( 'Transition Mode', 'foldergallery' ) . "</label></th>\n";
		echo '<td><select name="FolderSlider[mode]" id="FolderSlider[mode]">' . "\n";		
			echo "\t" .	'<option value="horizontal"';
				if ( 'horizontal' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Horizontal', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="vertical"';
				if ( 'vertical' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Vertical', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="fade"';
				if ( 'fade' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Fade', 'foldergallery' ) . "</option>\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";

		// Captions
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="captions">' . __( 'Caption Format', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderSlider[captions]" id="FolderSlider[captions]">' . "\n";		
			echo "\t" .	'<option value="none"';
				if ( 'none' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __( 'None', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="filename"';
				if ( 'filename' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="filenamewithoutextension"';
				if ( 'filenamewithoutextension' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename without extension', 'foldergallery' ) . "</option>\n";	
			echo "\t" .	'<option value="smartfilename"';
				if ( 'smartfilename' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Smart Filename', 'foldergallery' ) . "</option>\n";	
			echo "\t" .	'<option value="filenamesize"';
				if ( 'filenamesize' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename, size', 'foldergallery' ) . "</option>\n";	
			echo "\t" .	'<option value="filenamesizedate"';
				if ( 'filenamesizedate' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename, Size, Pic-Index, Filedate, humandate', 'foldergallery' ) . "</option>\n";	
		echo "</select>\n";
		echo "</td>\n</tr>\n";
		
		// CSS
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="css">' . __( 'CSS', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderSlider[css]" id="FolderSlider[css]">' . "\n";		
			echo "\t" .	'<option value="noborder"';
				if ( 'noborder' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __( 'No border', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="shadow"';
				if ( 'shadow' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Border with shadow', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="shadownoborder"';
				if ( 'shadownoborder' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Shadow without border', 'foldergallery' ) . "</option>\n";
			echo "\t" .	'<option value="black-border"';
				if ( 'black-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Black border', 'foldergallery' ) . "</option>\n";	
			echo "\t" .	'<option value="white-border"';
				if ( 'white-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('White border', 'foldergallery' ) . "</option>\n";	
			echo "\t" .	'<option value="gray-border"';
				if ( 'gray-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Gray border', 'foldergallery' ) . "</option>\n";	
		echo "</select>\n";
		echo "</td>\n</tr>\n";		

		$this->fsd_option_field( 'width', __( 'Width', 'foldergallery' ) , ' px ' . __( '(0 = auto)', 'foldergallery' ) );
		$this->fsd_option_field( 'height', __( 'Height', 'foldergallery' ), ' px ' . __( '(0 = auto)', 'foldergallery' ) );
		$this->fsd_option_field( 'speed', __( 'Speed', 'foldergallery' ), ' ' . __('seconds', 'foldergallery' ) );
		$this->fsd_option_field( 'maxslides', __( 'Carousel', 'foldergallery' ), ' ' . __('Bild(er) nebeneinander', 'foldergallery' ) );

		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'Controls', 'foldergallery' ) . "</th>\n";
		echo "<td><fieldset>\n";
		echo '<label for="controls">';
			echo '<input name="FolderSlider[controls]" type="checkbox" id="FolderSlider[controls]" value="1"';
			if ( $fsd_options['controls'] ) echo ' checked="checked"';
			echo '> ' . __('Show Previous/Next Buttons', 'foldergallery' ) . "</label><br />\n";
		echo '<label for="controls">';
			echo '<input name="FolderSlider[activeheight]" type="checkbox" id="FolderSlider[activeheight]" value="1"';
			if ( $fsd_options['activeheight'] ) echo ' checked="checked"';
			echo '> ' . __('Auto Adjust Height', 'foldergallery' ) . "</label><br />\n";
		echo '<label for="playcontrol">';
			echo '<input name="FolderSlider[playcontrol]" type="checkbox" id="FolderSlider[playcontrol]" value="1"';
			if ( $fsd_options['playcontrol'] ) echo ' checked="checked"';
			echo '> ' . __('Show Play/Pause Button', 'foldergallery' ) . "</label><br />\n";
		echo '<label for="autostart">';
			echo '<input name="FolderSlider[autostart]" type="checkbox" id="FolderSlider[autostart]" value="1"';
			if ( $fsd_options['autostart'] ) echo ' checked="checked"';
			echo '> ' . __('Start Slider Automatically', 'foldergallery' ) . "</label><br />\n";
		echo '<label for="pager">';
			echo '<input name="FolderSlider[pager]" type="checkbox" id="FolderSlider[pager]" value="1"';
			if ( $fsd_options['pager'] ) echo ' checked="checked"';
			echo '> ' . __('Show Pager', 'foldergallery' ) . "</label>\n";
		echo "</fieldset>\n";
		echo "</td>\n</tr>\n";		

		// WPML
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'WPML', 'foldergallery' ) . '</th>' . "\n";
		echo '<td><label for="wpml">';
			echo '<input name="FolderSlider[wpml]" type="checkbox" id="FolderSlider[wpml]" value="1"';
			if ( 1 == $fsd_options['wpml'] ) echo ' checked="checked"';
			echo '> ' . __('Fix WPML Paths', 'foldergallery' ) . "</label><br />\n";
		echo "</td>\n</tr>\n";

		// Doubleklick auf Bild öffnet Link zum Darstellen in einer Lightbox wie Fancybox3 mit a href Zuordnung (wie in Theme penguin)
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'Link zum Bild', 'foldergallery' ) . '</th>' . "\n";
		echo '<td><label for="lightboxlink">';
			echo '<input name="FolderSlider[lightboxlink]" type="checkbox" id="FolderSlider[lightboxlink]" value="1"';
			if ( 1 == $fsd_options['lightboxlink'] ) echo ' checked="checked"';
			echo '> ' . __('Zoom Link zum Bild für Lightbox wie Fancybox aktivieren', 'foldergallery' ) . "</label><br />\n";
		echo "</td>\n</tr>\n";
		echo "</tbody></table></div>\n";
		submit_button();
		echo "</form></div>\n";

	}
		
} //End Of Class

// ------------------------------------- Now Class for CSV display as table ---------------------------------

if( !class_exists('csvtohtmlwp') ) {
    ini_set("auto_detect_line_endings", true); //Does not apply when loading external file(s), therefore also custom function for this below
        
	
	/* Class to fetch values based on a "guess" (normal format) */    
    class csvtohtmlwp_guess {
    
    /*
     *   fetch_content
     * 
     *  This function returns an array of headers and rows based 
     *  on given content
     * 
     *  @param  string $content_arr             content array to use to identify headers and rows
     *  @return   array                                      array of 'rows' and 'headers'
     *                 
     */    
    public function fetch_content( $content_arr, $cutarr_fromend ) {
        
        //Skip (first) empty rows
        $new_arr = array();        
        foreach ( $content_arr as $row => $subset) {
           
            foreach ( $subset as $ss) {
                 $na = '';
                foreach ($ss as $subset_value) { 
                    $na .= $subset_value;
                }
                
                //Copy item fron content_arr to new arr only if there are any
                //values in this subset
                if ( strlen ( $na ) > 0) {
                    $new_arr[] = $ss;
                }
            }

        }
        
        $first_value = true;
        $header_values = array();
        if ( isset ( $new_arr[0] ) )
        {
            foreach ( $new_arr[0] as $hvalues) {
                    $header_values[] = $hvalues; //Add all but first value in arrya
            }
        }        
        
        $row_values = array();
        unset ( $new_arr[0] );
        
        foreach ( $new_arr  as $row) {
            $row_values[]= $row;
        }
        
        
        //Fetch last items? (eg. 2013,2014 instead of 2010,2011,2012,2013,2014)
        if ( $cutarr_fromend === 0) {$cutarr_fromend = 1;}
        
        //Get last slice of header array
        $slice_header = array_merge ( array_slice ( $header_values, 0, 1), array_slice( $header_values, $cutarr_fromend) );

        //"Recreate header values array"
        $header_values = array();
        foreach ( $slice_header as $sh) {
            $header_values[] = $sh;                
        }

        //"Recreate" row values array
        $rvalues = array();
        foreach( $row_values as $rv) {
            $rvalues[] = array_merge( array_slice( $rv, 0,1), array_slice( $rv, $cutarr_fromend ) );
        }

        $row_values = array();
        foreach ( $rvalues as $rv) {
            $row_values[] = $rv;
        }   
        
        $nr = 0;
        $firstrow = 0;
        
        $row3values = array();        
        $row2values = array();        
                  
        foreach($row_values as $row_key => $row_value) {
            foreach ( $header_values as $hkey => $h_value) {

                    $row2values[$hkey][0] = $h_value;
                    if ( isset($row_values[$row_key][$hkey]) )
                    {
                        $row2values[$hkey][1] = $row_values[$row_key][$hkey];
                    }
                    else {
                        $row2values[$hkey][1] = '';                 
                    }
                
                $nr++;
            }
            $row3values[] = $row2values;        
         }
                
        //Return row and headers
        return array( 'header_values' => $header_values, 'row_values' => $row3values );
    }
    
    }
	
	// .........................................................................................
	
	//Main class
    class csvtohtmlwp
    {
    private $csv_delimit; //Used when using anynmous function in array_map when loading file(s) into array(s)
    private $default_eol = "\r\n"; //Default - use this as this has been default in previous version of the plugin
    private $encoding_to = null;
    private $encoding_from = null;
    private $sorting_on_columns = null; //Should contain an array
    
    /*
    *  Constructor
    *
    *  This function will construct all the neccessary actions, filters and functions for the sourcetotable plugin to work
    *
    *
    *  @param	N/A
    *  @return	N/A
    */	
    public function __construct() 
    {                        
        add_action( 'init', array( $this, 'init' ) );
    }
    

    /*
     *  init
     * 
     *  This function initiates the actual shortcodes etc
     *                 
     */        
    public function init() 
    {               
        //Add shortcodes
        add_shortcode( 'csvtohtml_create', array ( $this, 'source_to_table') );
    }
		
    
    /*
     *   valid_sourcetypes
     * 
     *  This function is a helper-function that is used for retrieving true/false if a source_type is valid or not
     *  (defined sourcetypes are used so plugin knows how to fetch content from csv files)
     * 
     *  @param  string $source_type              what sourcetype to check
     *  @return   bool                           true if valid, else false
     *                 
     */    
    protected function valid_sourcetypes( $source_type = null ) {
        if ( $source_type === null) {
            return false;
        }
        
        //If guess is set as sourcetype, then plugin tries to figure out what sourcetype that should be used, 
        //but this is merely just a guess so it's better to define an actual source_type if applicable
        $valid_types = array( 'guess' );
        if (in_array( $source_type, $valid_types) !== false) {
            return true;
        }
        
        return false;
    }
    

    /**
     * Detects the end-of-line character of a string.
     * 
     * @param string $str The string to check.
     * @return string The detected eol. If no eol found, use default eol from object
     */    
    private function detect_eol( $str )
    {
        static $eols = array(
            "\0x000D000A", // [UNICODE] CR+LF: CR (U+000D) followed by LF (U+000A)
            "\0x000A",     // [UNICODE] LF: Line Feed, U+000A
            "\0x000B",     // [UNICODE] VT: Vertical Tab, U+000B
            "\0x000C",     // [UNICODE] FF: Form Feed, U+000C
            "\0x000D",     // [UNICODE] CR: Carriage Return, U+000D
            "\0x0085",     // [UNICODE] NEL: Next Line, U+0085
            "\0x2028",     // [UNICODE] LS: Line Separator, U+2028
            "\0x2029",     // [UNICODE] PS: Paragraph Separator, U+2029
            "\0x0D0A",     // [ASCII] CR+LF: Windows, TOPS-10, RT-11, CP/M, MP/M, DOS, Atari TOS, OS/2, Symbian OS, Palm OS
            "\0x0A0D",     // [ASCII] LF+CR: BBC Acorn, RISC OS spooled text output.
            "\0x0A",       // [ASCII] LF: Multics, Unix, Unix-like, BeOS, Amiga, RISC OS
            "\0x0D",       // [ASCII] CR: Commodore 8-bit, BBC Acorn, TRS-80, Apple II, Mac OS <=v9, OS-9
            "\0x1E",       // [ASCII] RS: QNX (pre-POSIX)
            "\0x15",       // [EBCDEIC] NEL: OS/390, OS/400
            "\r\n",
            "\r",
            "\n"
        );
        $cur_cnt = 0;
        $cur_eol = $this->default_eol;
        
        //Check if eols in array above exists in string
        foreach($eols as $eol){      
            $char_cnt = mb_substr_count($str, $eol);
                    
            if($char_cnt > $cur_cnt)
            {
                $cur_cnt = $char_cnt;
                $cur_eol = $eol;
            }
        }
        return $cur_eol;
    }


    /*
     *   Create object from given sourcetype
     * 
     *  Returns an object based on sourcetype given by user
     * 
     *  @param  string $source_type     source type from user
     *  @return   object $obj                     
     *                 
     */    
    private function object_fromsourcetype( $source_type ) {
		// require_once( 'guess.php' ); 
        $obj = new csvtohtmlwp_guess();
        return $obj;
    }
    
    
    /*
     *   adjust_columns
     * 
     *  This function is a helper function for including or excluding columns in the final html table
     * 
     *  @param  string $what_columns             What columns it is about (1,2,3,7-12)
     *  @return   array                                        What columns to use
     *                 
     */       
    private function adjust_columns ( $what_columns ) 
    {
            $ex_cols = explode(',', $what_columns );
            foreach($ex_cols as $key=>$ec) {
                //Add hypen and number to array, so array will be consistent
                //with values users put in (1-3,7 will be 1,2,3,7 and not 7,1,2,3)
                if (stristr( $ec, '-') === false) 
                {
                    $ex_cols[$key] .= '-' . $ec;
                }
            }
            
            //If two values given like 2-7...then add 2,3,4,5,6 and 7.
            foreach($ex_cols as $key=>$col_interval) 
            {
                $ac = explode('-', $col_interval); //3-7 would be array(3,7)
                if ((int)count($ac) === 2) { //Only include when array has two elements                                    
                    //Remove blank spaces left and right of each element in $ac-array
                    $ac[0] = (int)trim($ac[0]); //interval start
                    $ac[1] = (int)trim($ac[1]) + 1; //interval stop
                    
                    //Go through interval and to $ac-array (add column array)
                    for ($i=$ac[0];$i<$ac[1];$i++) {
                        $ex_cols[] = $i;
                    }
                    unset ( $ex_cols[$key] );
                }
            }

            //Which columns to use?
            $use_cols = array();
            foreach ( $ex_cols as $c ) 
            {
                $use_cols[] = (int)($c - 1);
            }
                        
            return $use_cols;
    }
    
    
    /*
     *  custom_sort_columns
     * 
     *  This function is used for sorting one or several columns
     * 
     *  @param    $a                        First value
     *  @param    $b                        Second value
     *  @return   integer                   Returned comparision of firt and second value 
     *                 
     */      
    private function custom_sort_columns($a, $b)
    {        
        //This has to be an array to work
        if ( $this->sorting_on_columns === null ) 
        {
            return false;
        }        
        
        $columns = $this->sorting_on_columns;
        $first_column = true;        
        foreach($columns as $item)
        {            
            $col = $item[0];
            
            //If column not set, ignore sorting
            if (!isset($a[$col]) || !isset($b[$col])) 
            {
                return 0;
            }

            $sortorder = mb_strtolower( $item[1] );
            
            //First column to be sorted
            if ($first_column === true)
            {
                if ( $sortorder === 'asc' )
                {
                    $sorted_column = strnatcmp( $a[$col], $b[$col] );   
                }
                else
                {
                    $sorted_column = strnatcmp( $b[$col], $a[$col] );   
                }
                $first_column = false;     
            }                
            //If this column and previous column is identical, then sort on this column
            //(if it is not first column to be sorted)
            else if (!$sorted_column)
            {
                if ( $sortorder === 'asc' )
                {
                    $sorted_column = strnatcmp( $a[$col], $b[$col] );   
                }
                else
                {
                    $sorted_column = strnatcmp( $b[$col], $a[$col] );   
                }    
            }
        }                 
        
        return $sorted_column;
    }
     
     
    /*
     *  convertarrayitem_encoding
     * 
     *  This function is used as a callback for walk_array and it changes
     *  characterencoding for each item in an array
     * 
     *  @param    array  $given_item           Arrayitem to translate encoding
     *  @return   N/A                          Change of arrayitem by reference
     *                 
     */  
    private function convertarrayitem_encoding( &$given_item ) 
    {       
        $encoding_to = $this->encoding_to;
        $encoding_from = $this->encoding_from;        
        
        $option_encoding = 0; //Only to encoding 
        if ( $encoding_from !== null && $encoding_to !== null ) 
        {
            $option_encoding = 1; //Both from and to encoding
        }
                         
        if ( $option_encoding === 1 )
        {
            if ( is_array($given_item) !== true ) 
            {
                $given_item = mb_convert_encoding($given_item, $encoding_to, $encoding_from);                  
            }
        }
        else if ( $option_encoding === 0 )
        {
            if ( is_array($given_item) !== true )
            {
                $given_item = mb_convert_encoding($given_item, $encoding_to);                       
            }
        }
                    
    }
    

	// Für Search Parameter Wert in Zeile suchen - search function fulltext
	function in_array_r($item , $array){
		return preg_match('/'.preg_quote($item, '/').'/i' , json_encode($array, JSON_UNESCAPED_SLASHES));
	}            


    /*
     *   source_to_table
     * 
     *  This function creates a (html) table based on given source (csv) files
     *  Files are divided by semicolon
     * 
     *  @param  string $attr             shortcode attributes
     *  @return   string                      html-content
     *                 
     */    
    public function source_to_table( $attrs ) 
    {
		global $wp;
        $defaults = array(
            'html_id' > null,
            'html_class' => null,
            'title' => null, //if given then put titletext in top left corner
            'path' => '', //This is the base path AFTER the upload path of Wordpress (eg. /2016/03 = /wp-content/uploads/2016/03)
            'source_type' => 'guess', //So plugin knows HOW to fetch content from file(s)
            'source_files' => null, //Files are be divided with sources_separator (file1;file2 etc). It's also possible to include urls to csv files. It's also possible to use a wildcard (example *.csv) for fetching all files from specified path. This only works when fetching files directly from own server.
            'csv_delimiter' => ',', //Delimiter for csv - files (defaults to comma)
            'fetch_lastheaders' => 0,   //If fetch_lastheaders=3 => (2012,2013,2014, if header_count = 2 => (2013,2014) etc
            'exclude_cols' => null, //If you want to exclude some columns (eg. 1,4,9). Set to "last" if you want to remove last column.
            'include_cols' => null, //If you want to include these columns (only) use this option (eg. 1,4,9). If include_cols is given, then exclude_cols are ignored
            'eol_detection' => 'auto', //Use linefeed when using external files, Default auto = autodetect, CR/LF = Carriage return when using external files, CR = Carriage return, LF = Line feed
            'convert_encoding_from' => null, //If you want to convert character encoding from source. (use both from and to for best result) 
            'convert_encoding_to' => null, //If you want to convert character encoding from source. (use both from and to for best result)            
            'sort_cols' => null, //Which column(s) to sort on in format nr,nr och nr-nr (example 1,2,4 or 1-2,4)
            'sort_cols_order' => null, //Which order to sort columns on (asc/desc). If you have 3 columns, you can define these like asc,desc,asc
            'add_ext_auto' => 'no', //If file is not included with .csv, then add .csv automatically if this value is yes. Otherwise, set no
            'float_divider' => '.', //If fetching float values from csv use this character to display "float-dividers" (default 6.4, 1.2 etc)
            'debug_mode' => 'no'
        );

        //Extract values from shortcode and if not set use defaults above
        $args = wp_parse_args( $attrs, $defaults );
        extract ( $args );
		
		// Sort order from url parameter
		if (isset($_GET['sort'])) { $sort_cols = $_GET['sort']; }
		if (isset($_GET['order'])) { $sort_cols_order = $_GET['order']; }
		
        $this->csv_delimit = $csv_delimiter; //Use this char as delimiter
      
        //Base upload path of uploads
        $upload_dir = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir'];

        //If user has put some wildcard in source_files then create a list of files
        //based on that wildcard in the folder that is specified    
        if ( stristr( $source_files, '*' ) !== false ) 
        {
            $files_path = glob( $upload_basedir . '/' . $path . '/'. $source_files);
            $source_files = '';
            foreach ($files_path as $filename) 
            {
                $source_files .= basename($filename) . ';';
            }
            if ( strlen($source_files) > 0) {
                $source_files = substr($source_files,0,-1); //Remove last semicolon
            }
        }

        //Find location of sources (if more then one source, user should divide them with 'sources_separator' (default semicolon) )
        //Example:  [stt_create path="2015/04" sources="bayern;badenwuertemberg"] 
        ///wp-content/uploads/2015/04/bayern.csv
        ///wp-content/uploads/2015/04/badenwuertemberg.csv        
        $sources = explode( ';', $source_files );

        //Create an array of ("csv content")
        $content_arr = array();
        
        foreach( $sources as $s) 
        {
            //If $s(file) misses an extension add csv extension to filename(s)
            //if add extension auto is set to yes (yes is default)
            if (stristr($s, '.csv') === false && $add_ext_auto === 'yes') {
                $file = $s . '.csv';
            }
            else {
                $file = $s;
            }
          
            //Add array item with content from file(s)
        
            //If source file do not have http or https in it or if path is given, then it's a local file
            $local_file = true;
            
            if ( stristr($file, 'http') !== false || stristr($file, 'https') !== false )
            {
                $local_file = false;
            }                    
            

            
            //Load external file and add it into array
            if ( $local_file === false ) 
            {         
                $file_arr = false;
                //Check if (external) file exists
                $wp_response = wp_remote_get($file);
                $ret_code = wp_remote_retrieve_response_code( $wp_response );
                $ret_message = wp_remote_retrieve_response_message( $wp_response );

                //200 OK               
                if ( $ret_code === 200)
                {
					// Download der ICS Datei 1 Stunde cachen	
					if (!in_the_loop () || !is_main_query ()) { $iswidget = 'widget'; } else { $iswidget = get_post_type( get_the_ID()) . get_the_ID(); }
					$cache_key = 'foldergallery-' . $iswidget . '-' . md5($file);
					$body_data = get_site_transient($cache_key);
					if ($body_data === False) {
						$body_data = wp_remote_retrieve_body( $wp_response );                        
						 if ($body_data === False) {
							 $body_data = null;
							 error_log('Keine Datei gefunden bei:  ' . $wp_response);
						 } else {
							 set_site_transient($cache_key, $body_data, 3600);   // 4debug: auf 0 setzen, wenn neu geladen werden muss
						 }
					}
					
					// $body_data = wp_remote_retrieve_body( $wp_response );                        

                    //What end of line to use when handling file(s)
                    switch (strtolower( $eol_detection ) ) 
                    {
                        case 'auto':
                            $use_eol = $this->detect_eol ( $body_data ); 
                            break;
                        case 'lf':
                            $use_eol = "\n";
                        case 'cr':
                            $use_eol = "\r";
                            break;
                        case 'cr/lf':
                            $use_eol = "\r\n";
                            break;
                        default:
                            $use_eol = $this->default_eol;
                    }

                    //Explode array with selected end of line
                    $file_arr = explode( $use_eol, $body_data);

                    //remove last item from array
                    $x = count ( $file_arr ) - 1;
                    unset ( $file_arr[$x] );
                }

                //try to fetch file with file() (fetching file as an array)
                if ( $file_arr === false ) 
                {                
                    $file_arr = @file ( $file );
                    if ( !is_array( $file_arr ) ) 
                    {
                        $file_arr = false;
                    }
                }                
                
                //Put an array with content into this array item
                //(but only if  array has been created from file/url)
                if ( $file_arr !== false ) 
                {
                    //Put an array with csv content into this array item                    
                    $content_arr[] = array_map(function($v){return str_getcsv($v, $this->csv_delimit);}, $file_arr);   
                }
            }
            
            //Load local file into content array
            if ( $local_file === true ) 
            {
                
                if ( strlen( $path ) > 0 ) 
                {
                    $file = $upload_basedir . '/' . $path . '/' . $file; //File from uploads folder and path
                }
                else 
                {
                    $file = $upload_basedir . '/' . $file; //File directly from root upload folder
                }
                
                if (file_exists($file)) 
                {                                        
                    //Put an array with csv content into this array item
                    $content_arr[] = array_map(function($v){return str_getcsv($v, $this->csv_delimit);}, file( $file ));                    
                }
            }
        }        
        
        //Create the object used for fetching
        $obj = $this->object_fromsourcetype( $source_type );        
                
        //Fetch row and headers from objects created above
        $header_values = array();
        $row_values = array();
        
         //Nr of items from end of array
        //If not set=0, then $cutarr_fromend would be 0 = last index)
        $cutarr_fromend = -1 * abs( (int)$fetch_lastheaders );
                
        //Cut array from end is set if fetch_lastheaders is sent
        $values_from_obj = $obj->fetch_content( $content_arr, $cutarr_fromend );        
        $header_values = $values_from_obj['header_values'];
        $header_ori_values = $header_values;
        $row_values = $values_from_obj['row_values'];   
        
        //If encoding is specified, then encode entire array to specified characterset
        if ( $convert_encoding_from !== null || $convert_encoding_to !== null )
        {
            $this->encoding_from = $convert_encoding_from;
            $this->encoding_to = $convert_encoding_to;        
            array_walk_recursive($header_values, array($this, 'convertarrayitem_encoding') );
            array_walk_recursive($row_values, array($this, 'convertarrayitem_encoding') );
        }
        
        //Include columns (only) ?
        if ($include_cols !== null) 
        {
            $include_cols = $this->adjust_columns( $include_cols );
            
            //Recreate header_values
            $new_headervalues = array();
            foreach ( $include_cols as $c) {
                if (isset ( $header_values[$c]) ) {
                    $new_headervalues[$c] = $header_values[$c];
                }
            }
            
            $header_values = array();
            foreach($new_headervalues as $nhv) 
            {
                $header_values[]= $nhv;
            }
            
            //Recreate row values (with appropiate columns)
            $new_rowvalues = array();

            //Add column values into new array from scratch
            //Go through include columns (indexes) for every row and
            //add item to the new array
            $nr = 0;
            foreach( $row_values as $key=>$rv ) 
            {            
                foreach($include_cols as $ic) 
                {
                    if ( isset( $rv[$ic])) 
                    {
                        $new_rowvalues[$nr][] = $rv[$ic];
                    }
                }              
                $nr++;             
           }            
             
           $row_values = array();
           foreach($new_rowvalues as $nrv) 
           {
               $row_values[]= $nrv;
           }
        }
        //Exclude columns? (if include_cols is set, this attribute is ignored)
        else if ( $exclude_cols !== null ) 
        {
            //Remove last column?
            if (stristr($exclude_cols, 'last') !== false ) 
            {
                $last_col = count ( $row_values[0] );                  
                $exclude_cols = str_replace('last', $last_col, $exclude_cols );
            }
            
            //remove given column(s)
            $remove_cols = $this->adjust_columns( $exclude_cols );
   
            //Remove header values
            foreach($remove_cols as $rc) 
            {
                unset( $header_values[$rc] );                
            }
            
             //Remove column values
             //Go through each row and for each row
             //remove (unset) the index set by remove_cols above
             foreach( $row_values as $key=>$rv ) 
             {  
                foreach($remove_cols as $rc) 
                {
                    unset ( $row_values[$key][$rc] );
                }             
             }
        }



        //Sort by specific column(s) in format: 1,2,4 or 2-4
        if ( $sort_cols !== null)
        {                       
            //Create new array in a "sort-friendly format"
            $new_arr = array();
            $index = 0;
            $cnt_headers = count($header_values);
            foreach( $row_values as $r )
            {
                for ($c=0;$c<$cnt_headers;$c++) 
                {
                    $new_arr[$index][$c] = $r[$c][1]; //Column $c, value
                }
                
                $index++;
            }
            
            //Do the sorting    
            $this->sorting_on_columns = $this->adjust_columns( $sort_cols );    
            
            $sort_cols_order_arr = array();
            if ( $sort_cols_order === null )
            {                
                $so = 'asc';
                foreach($this->sorting_on_columns as $key => $soc)
                {
                    $sort_cols_order_arr[$key] = $so;
                }
            }
            else 
            {
                //Set unique sortorders for each column
                $sort_cols_order_arr = explode(',',$sort_cols_order);
            }

            foreach( $this->sorting_on_columns as $key => &$soc )
            {
                $so = 'asc';
                if (isset($sort_cols_order_arr[$key])) 
                {
                    $so = $sort_cols_order_arr[$key];
                }
                
                $soc = array(
                            $this->sorting_on_columns[$key],
                            $so
                        );                
            }            
            usort($new_arr, array( $this, 'custom_sort_columns') );
            
            //Put values from the orded array $new_arr into $row_values
            $index = 0;
            foreach($row_values as &$r)
            {
                for ($c=0;$c<$cnt_headers;$c++) 
                {
                    $r[$c][1] = $new_arr[$index][$c]; 
                }
                $index++;
            }
        }

        //If title given, set this title in left top corner of htmltable
        if ( isset($title) && isset($header_values[0])) 
        {
            $header_values[0] = sanitize_text_field( $title );
        }
        
        //Create table
        if ( isset($html_id) ) 
        {
            $htmlid_set = 'id="' .  $html_id . '" '; 
        }
        else 
        {
            $htmlid_set = '';
        }
        
        if ( isset($html_class) ) 
        {
            $html_class = ' ' . $html_class;
        }
        else 
        {
            $html_class = '';
        }
        
		// Zeilen filtern, wenn Suchbegriff gesetzt
		$search='';
		if (isset($_GET['search'])) {
		  $search = sanitize_text_field( $_GET['search'] );
		}
		$html = '<div style="text-align:right"><form><input type="text" placeholder="Suchbegriff" name="search" id="search" value="'.$search.'"><input type="submit" value="suchen"></form></div>';
        $html .= '<table ' . $htmlid_set . 'class="csvtohtml' . $html_class . '"><thead><tr class="headers">';
        $nr_col = 1;

		// Page navigation
		global $wp;
		$sortorder='asc';
		$nb_elem_per_page = 20;
		$number_of_pages = intval(count($row_values)/$nb_elem_per_page)+1;
		$page = isset($_GET['seite'])?intval($_GET['seite']):0;

		foreach( $header_values as $hv) 
        {
			if (isset($_GET['order'])) { if ( $_GET['order'] == 'asc' ) { $sortorder = 'desc'; } else { $sortorder='asc'; } } else { $sort_order = 'desc'; }
            $key = array_search($hv, $header_ori_values)+1;
			$html .= '<th class="colset colset-' . $nr_col . '"><a title="Sortieren" href="'.add_query_arg( array('sort'=>$nr_col, 'order'=>$sortorder,'search'=>$search,'seite'=>$page), home_url($wp->request) ).'">' . $hv;
			if (isset($_GET['order']) && $_GET['order'] == 'desc' && $_GET['sort'] == $nr_col) $html.='<i class="fa fa-angle-down"></i>';
			if (isset($_GET['order']) && $_GET['order'] == 'asc' && $_GET['sort'] == $nr_col) $html.='<i class="fa fa-angle-up"></i>';
			$html.= '</a></th>';
            $nr_col++;
        }
        $html .= '</tr></thead><tbody>';
        $nr_row = 1;
        
		// Suchfilter, wenn filter gesetzt, nicht paginieren
		if ( !empty($search)) { $nb_elem_per_page = 200; $page = 0; }
		// foreach( $row_values as $rv ) {
		foreach (array_slice($row_values, $page*$nb_elem_per_page, $nb_elem_per_page) as $rv) { 
			if ( ! isset( $search ) || isset( $search ) && $this->in_array_r($search, $rv) ) {
				$html .= '<tr class="rowset rowset-' .$nr_row . '">';    
					
				$nr_col = 1;
				foreach ( $rv as $inner_value) {
					//Display other float divider (e.g. 6,3 instead 6.2)
					if ($float_divider != '.') {
							$inner_value[1] = str_replace('.', $float_divider, $inner_value[1]);
					}
					$html .= '<td class="colset colset-' . $nr_col . '">' . sanitize_text_field($inner_value[1] ) . '</td>';      
					$nr_col++;
				}
				$html .= '</tr>';
				$nr_row++;
			}
		}
		// Page navigation		
		$html .= '</tbody></table>';
		if ( empty($search)) {
			for($i=0;$i<$number_of_pages;$i++){
				$seitennummer = $i+1;
				$html .= ' &nbsp;<a class="page-numbers" href="'.add_query_arg( array('sort'=>$sort_cols, 'order'=>$sort_cols_order,'search'=>$search,'seite'=>$i), home_url($wp->request) ).'">'.$seitennummer.'</a>';
			}	
		}
		$html .= '<br>';
        return $html;
    }
}
  
$csvtohtmlwp = new csvtohtmlwp();
}

// 
// ----------------------------- Nun noch der Shortcode, um RSS-Feeds auf einer Seite anzuzeigen --------------------------
//

add_shortcode( 'rssdisplay', 't5_feed_shortcode' );

function t5_feed_shortcode( $attrs )
{
    $args = shortcode_atts(
        array (
            'url' => 'https://ssl.pbcs.de/dcounter/shopadd.asp?mode=rss&items=30&shopid=',
			'excerpt' => '0',
			'wordcount' => 0,
        ),
        $attrs
    );
	global $excerpt;
    // a SimplePie instance
    $feed = fetch_feed( esc_url_raw( $args[ 'url' ] ) );
	$excerpt = $args[ 'excerpt' ];
	$wordcount= $args['wordcount'];
    if ( is_wp_error( $feed ) )
        return __( 'Feed display Error', 'foldergallery' );
    if ( ! $feed->get_item_quantity() )
        return __( 'Feed is down', 'foldergallery' );

    $lis = array();
    foreach ( $feed->get_items(0, 30) as $item )
    {
        if ( '' === $title = esc_attr( strip_tags( $item->get_title() ) ) ) $title = __( 'Untitled' );
        if ( '' === $content = $item->get_description() ) $content = __( 'none' );
		if ( '1' == $excerpt ) { $content = esc_attr( strip_tags( $item->get_description() )); }
		if ( $wordcount > 0 ) {
			$content = implode(" ", array_slice( explode(" ", $content), 0, $wordcount) );
			$compactcss ='compact';
		} else { $compactcss='rssdisplay'; }
        $lis[] = sprintf(
            '<a class="headline" href="%1$s">%2$s</a><br>%3$s',
            esc_url( strip_tags( $item->get_link() ) ),
            $title, $content
        );
    }
    return '<ul class="'.$compactcss.'"><li>' . join( '</li><li>', $lis ) . '</ul>';
}

// 
// ----------------------------- Shortcode, um ICS und ICAL Kalender einzuzeigen (6h Cached) auf einer Seite/ Beitrag anzuzeigen --------------------------
//

require_once 'class.iCalReader.php';

// Calendar display month - draws a calendar
function draw_calendar($month,$year,$eventarray){
	setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge'); 
	/* days and weeks vars now ... */
	$calheader = date('Y-m-d',mktime(0,0,0,$month,1,$year));
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	if ( $running_day == 0 ) { $running_day = 7; }
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();
	/* draw table */
	$calendar = '<table><thead><th style="text-align:center" colspan=8>' . strftime('%B %Y', mktime(0,0,0,$month,1,$year) ) . '</th></thead>';
	/* table headings */
	$headings = array('MO','DI','MI','DO','FR','SA','SO','Kw');
	$calendar.= '<tr><td style="padding:2px;text-align:center">'.implode('</td><td style="padding:2px;text-align:center">',$headings).'</td></tr>';
	/* row for week one */
	$calendar.= '<tr class="calendar-row">';
	/* print "blank" days until the first of the current week */
	for($x = 1; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np"></td>';
		$days_in_this_week++;
	endfor;
	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td class="calendar-day">';
		/* add in the day number */
		$running_week = date('W',mktime(0,0,0,$month,$list_day,$year));
		$calendar.= '<div class="day-number">'.$list_day.'</div>';
		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		foreach ($eventarray as $calevent) {
			if ( substr($calevent['DTSTART'],0,8) == date('Ymd',mktime(0,0,0,$month,$list_day,$year)) ) {
				$calendar.= '<span style="word-break:break-all" title="'.esc_html($calevent['SUMMARY']).'">' . esc_html(substr($calevent['SUMMARY'],0,40)) . '</span> <br> ';
			}	
		}	
		$calendar.= '</td>';
		if($running_day == 7):
			$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = 0;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;
	/* finish the rest of the days in the week */
	if($days_in_this_week < 8 && $days_in_this_week > 1):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np"></td>';
		endfor;
	$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
	endif;
	/* end the table */
	$calendar.= '</table>';
	/* all done, return result */
	return $calendar;
}

/**
 * Display events
 * 
 * @param array $atts Shortcode attributes
 * @return void
 * */
function ICSEvents($atts) {
	extract( shortcode_atts( array(
		'title' => '', 	  // Set a title if you want to. it will be displayed as table header
		'url'  => '',	  // URL with the ics file - required parameter
		'items'  => 5,	  // set a number to limit number of items displayed in listings/calendars
		'sumonly' => 0,	  // set to "1" if you do not want to list description and location
		'showold' => 0,   // set to "1" to list older entries (happened before today)
		'view' => 'list', // list or calendar display or list,calendar for both
		'noeventsmessage' => '',  //if no events found nothing or this text will be displayed
	), $atts ) );
	// check if url is valid	
	$wp_response = wp_remote_get($url);
	$ret_code = wp_remote_retrieve_response_code( $wp_response );
	$ret_message = wp_remote_retrieve_response_message( $wp_response );
	//200 OK               
	if ( $ret_code === 200) {
		$ical = new ical($url);
		$events = $ical->sortEventsWithOrder($ical->events());
		date_default_timezone_set('Europe/Berlin');
		setlocale(LC_ALL, 'de_DE.UTF-8', 'German_Germany');
		// $now = time();
		$now = mktime(0,0,0,date("m"),date("d"),date("Y")); 
		$eventsToDisplay = array();
		foreach ($events as $event) {
			if ($showold==1 || $ical->iCalDateToUnixTimestamp( $event['DTSTART'] ) >= $now && count($eventsToDisplay) < $items) {
				$eventsToDisplay[] = $event;
			}
		}
		$wtage = array(  0 => "SO", 1 => "MO", 2 => "DI", 3 => "MI", 4 => "DO", 5 => "FR", 6 => "SA", 7 => "SO"  );
		$html = '<table style="overflow-wrap:anywhere">';
		if ( !empty($title) ) { $html .= '<thead><th colspan="2">'.$title.'</th></thead>'; }
		if (empty($eventsToDisplay)) {
			if (isset($noeventsmsg)) {	$html .= $noeventsmsg;  }
		} else {
			if ( strpos($view,"list") !== false ) {
				foreach ($eventsToDisplay as $event) {
					$timestamp = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
					if ( $timestamp > $now ) { $prepo = 'in '; } else { $prepo = 'vor '; }
					$wielangeher = $prepo . human_time_diff($timestamp,$now);
					if ( $wielangeher == 'vor 1 Sekunde' ) { $wielangeher = 'heute'; }
					$html .= '<tr><td><nobr><abbr title="'.strftime('%a %e. %B %Y, %W. Kw', $timestamp).'">'.$wtage[date('N', $timestamp)].' ' . strftime('%e. %b', $timestamp).'</nobr><br>' . $wielangeher;
					$html .= '</td><td>';
					if ( $sumonly==0 ) { $html .= '<span class="headline">'; }
					$html .= $event['SUMMARY'] . '</span>';
					if ( $sumonly==0 ) { $html .= '</span>'; }
					if ( $sumonly==0 && !empty($event['DESCRIPTION']) ) {
						$html .= '<br><small>'.$event['DESCRIPTION'].'</small>';
					}
					if ( $sumonly==0 && !empty($event['LOCATION']) && '-' !== $event['LOCATION'] ) {
						$html .= '<br>'.$event['LOCATION'].'';
					}
					if (strlen($event['DTSTART']) > 8) {
						$html .= '<br>'.strftime('%a %d. %b %Y %H:%M', $timestamp).' Uhr';
					}
					if (strlen($event['DTEND']) > 8) {
						$timeend = $ical->iCalDateToUnixTimestamp($event['DTEND']);
						$html .= ' - '.strftime('%a %d. %b %Y %H:%M', $timeend).' Uhr';
					}
					$html .= '</tr>';
				}
			}       // List view	
		}
		$html .= '</tr></table>';
		if ( strpos($view,"calendar") !== false ) {
			/** Get all months with ical events **/
			$outputed_values = array();
			foreach ($eventsToDisplay as $calevent) {
				$workername = substr($calevent['DTSTART'],0,6);
				if (!in_array($workername, $outputed_values)){
					$mdatum = substr($calevent['DTSTART'],0,4).'-'. substr($calevent['DTSTART'],4,2).'-'.substr($calevent['DTSTART'],6,2);
					$html .= draw_calendar(date("m", strtotime($mdatum)),date("Y", strtotime($mdatum)),$eventsToDisplay);
					array_push($outputed_values, $workername);
				}	
			}
			//$html .= draw_calendar(date("m"),date("Y"),$eventsToDisplay);
		}
	}
	return $html;
}
add_shortcode('ics_events', 'ICSEvents');

// 
// ----------------------------- Scheduled RSS to Posts Importer ----------------------------------------------------
//

global $wpdb, $wp_version, $number;

// Plugin installation and default value
function rssnews_install() {
	$rss2_url = "https://www.wordpress.org/news/feed/"; 
	add_option('rssnews_rss1', $rss2_url);
	add_option('rssnews_direction1', "Off");
	add_option('rssnews_rss2', $rss2_url);
	add_option('rssnews_direction2', "Off");
	add_option('rssnews_rss3', $rss2_url);
	add_option('rssnews_direction3', "Off");
	add_option('rssnews_rss4', $rss2_url);
	add_option('rssnews_direction4', "Off");
	add_option('rssnews_rss5', $rss2_url);
	add_option('rssnews_direction5', "Off");
	add_option('pbrss-latestpostdate1', "01 September 1990, 5:18 pm");
	add_option('pbrss-latestpostdate2', "01 September 1990, 5:18 pm");
	add_option('pbrss-latestpostdate3', "01 September 1990, 5:18 pm");
	add_option('pbrss-latestpostdate4', "01 September 1990, 5:18 pm");
	add_option('pbrss-latestpostdate5', "01 September 1990, 5:18 pm");
}

function val_default_direction($value) {
	$returnvalue = "Off";
	if( $value == "Off" || $value == "On" )	{
		$returnvalue = $value;
	}	
	return $returnvalue;
}


//  Schedule and update pbrss news with the news rss feed
if (!wp_next_scheduled('update_feed1')  && get_option('rssnews_direction1') =='On') { wp_schedule_event(current_time('timestamp',true), 'daily', 'update_feed1'); }
add_action('update_feed1', function() { $numer=1; update_pbrss_news($numer); } );
if (!wp_next_scheduled('update_feed2')  && get_option('rssnews_direction2') =='On') { wp_schedule_event(current_time('timestamp',true), 'daily', 'update_feed2'); }
add_action('update_feed2',  function() { $numer=2; update_pbrss_news($numer); } );
if (!wp_next_scheduled('update_feed3')  && get_option('rssnews_direction3') =='On') { wp_schedule_event(current_time('timestamp',true), 'daily', 'update_feed3'); }
add_action('update_feed3',  function() { $numer=3; update_pbrss_news($numer); } );
if (!wp_next_scheduled('update_feed4')  && get_option('rssnews_direction4') =='On') { wp_schedule_event(current_time('timestamp',true), 'daily', 'update_feed4'); }
add_action('update_feed4',  function() { $numer=4; update_pbrss_news($numer); } );
if (!wp_next_scheduled('update_feed5')  && get_option('rssnews_direction5') =='On') { wp_schedule_event(current_time('timestamp',true), 'daily', 'update_feed5'); }
add_action('update_feed5',  function() { $numer=5; update_pbrss_news($numer); } );
// ---- clean the scheduler
for ($i = 1; $i <= 10; $i++) {
	if ( get_option('rssnews_direction'.$i) =='Off' ) { wp_clear_scheduled_hook( 'update_feed'.$i ); }
}	

function update_pbrss_news($number) {
    // To reset set this one (for debugging)
	// update_option( 'pbrss-latestpostdate'.$number, '01 September 1990, 5:18 pm' );
	if (file_exists (ABSPATH.'/wp-admin/includes/taxonomy.php')) {
			require_once (ABSPATH.'/wp-admin/includes/taxonomy.php'); 
	}
	include_once( ABSPATH . WPINC . '/feed.php' );
    // retrieve the previous date from database
        $time = get_option('pbrss-latestpostdate'.$number);
		if ( empty($time) ) { $time = '01 September 1990, 5:18 pm'; }

        //read the feed
        if(function_exists('fetch_feed')){
            // $uri = 'https://ssl.pbcs.de/dcounter/softwareverzeichnis.asp?action=rss&items=5';
			if ( $number == 1 ) { $uri=esc_url_raw(get_option('rssnews_rss1')); }
			if ( $number == 2 ) { $uri=esc_url_raw(get_option('rssnews_rss2')); }
			if ( $number == 3 ) { $uri=esc_url_raw(get_option('rssnews_rss3')); }
			if ( $number == 4 ) { $uri=esc_url_raw(get_option('rssnews_rss4')); }
			if ( $number == 5 ) { $uri=esc_url_raw(get_option('rssnews_rss5')); }
            $feed = fetch_feed($uri);
        }

        if($feed) {
            foreach ($feed->get_items() as $item){
                $titlepost = $item->get_title();
                $content = $item->get_content();
                $description = $item->get_description();
                // $description = str_replace( 'https://ssl.pbcs.de/dcounter/journal','http://localhost:888/worpdress/wp_content/uploads/bilder', $description );
                $itemdate = $item->get_date();
				$cat_terms = array();
				$categorien = $item->get_categories();
				foreach ($categorien as $cat) {
					$cat_terms[] = $cat->get_term();
				}
                $media_group = $item->get_item_tags('', 'enclosure');
                $img = $media_group[0]['attribs']['']['url'];
                $width = $media_group[0]['attribs']['']['width'];           
                // $latestItemDate = $feed->get_item()->get_date();

                // --- if the date is < than the date we have in database, get out of the loop
                if( $itemdate <= $time) break;

                // prepare values for inserting
				$catid = wp_create_category( $cat_terms[0] );
				$post_information = array(
                    'post_title' => $titlepost,
                    'post_content' => $description,
                    'post_type' => 'post',
					'post_author' => 1,
                    'post_status' => 'publish',
					'post_category' => array($catid),
					'post_date' => date('Y-m-d H:i:s'),
                );
                wp_insert_post( $post_information );    
            }
        }
        // update the new date in database to the date of the first item in the loop        
        update_option( 'pbrss-latestpostdate'.$number, $feed->get_item()->get_date() );
}


// Admin update option for default value
function rssnews_admin_options() {
	?>
	<div class="wrap">
	<div class="form-wrap">
	<div id="icon-plugins" class="icon32 icon32-posts-post"></div>
	<?php	
	$rssnews_rss1 = get_option('rssnews_rss1');
	$rssnews_rss2 = get_option('rssnews_rss2');
	$rssnews_rss3 = get_option('rssnews_rss3');
	$rssnews_rss4 = get_option('rssnews_rss4');
	$rssnews_rss5 = get_option('rssnews_rss5');
	$rssnews_direction1 = get_option('rssnews_direction1');
	$rssnews_direction2 = get_option('rssnews_direction2');
	$rssnews_direction3 = get_option('rssnews_direction3');
	$rssnews_direction4 = get_option('rssnews_direction4');
	$rssnews_direction5 = get_option('rssnews_direction5');
	if (isset($_POST['rssnews_submit'])) {
		check_admin_referer('rssnews_form_setting');
		$rssnews_rss1 		= esc_url_raw($_POST['rssnews_rss1']);
		$rssnews_rss2 		= esc_url_raw($_POST['rssnews_rss2']);
		$rssnews_rss3	 	= esc_url_raw($_POST['rssnews_rss3']);
		$rssnews_rss4 		= esc_url_raw($_POST['rssnews_rss4']);
		$rssnews_rss5 		= esc_url_raw($_POST['rssnews_rss5']);
		$rssnews_direction1 = sanitize_text_field($_POST['rssnews_direction1']);
		$rssnews_direction2 = sanitize_text_field($_POST['rssnews_direction2']);
		$rssnews_direction3 = sanitize_text_field($_POST['rssnews_direction3']);
		$rssnews_direction4 = sanitize_text_field($_POST['rssnews_direction4']);
		$rssnews_direction5 = sanitize_text_field($_POST['rssnews_direction5']);
		// Set default value for direction (schedule)
		$rssnews_direction1 = val_default_direction($rssnews_direction1);
		$rssnews_direction2 = val_default_direction($rssnews_direction2);
		$rssnews_direction3 = val_default_direction($rssnews_direction3);
		$rssnews_direction4 = val_default_direction($rssnews_direction4);
		$rssnews_direction5 = val_default_direction($rssnews_direction5);
		update_option('rssnews_rss1', $rssnews_rss1 );
		update_option('rssnews_rss2', $rssnews_rss2 );
		update_option('rssnews_rss3', $rssnews_rss3 );
		update_option('rssnews_rss4', $rssnews_rss4 );
		update_option('rssnews_rss5', $rssnews_rss5 );
		update_option('rssnews_direction1', $rssnews_direction1 );
		update_option('rssnews_direction2', $rssnews_direction2 );
		update_option('rssnews_direction3', $rssnews_direction3 );
		update_option('rssnews_direction4', $rssnews_direction4 );
		update_option('rssnews_direction5', $rssnews_direction5 );
		?>
		<div class="updated fade">
			<p><strong><?php _e('Details successfully updated.','foldergallery'); ?></strong></p>
		</div>
		<?php
	}
	?>
	<h2><?php _e('FG RssToPosts','foldergallery'); ?></h2>
	<form name="rssnews_form" method="post" action="">
	<div class="postbox" style="padding:10px">
	<p>Import up to five rss feeds and create posts from the feeds. date of the newest rss entry will be set to avoid duplicates.<br>
	When set to "on" a daily schedule is created on wp-cron. toggle off&save/on&save will run the task now</p>
	<label for="tag-title"><?php _e('Rss link and schedule','foldergallery'); ?> 1
	<?php echo ' newest '. get_option('pbrss-latestpostdate1').' vor ' . human_time_diff( strtotime(get_option('pbrss-latestpostdate1')),current_time( 'timestamp' )); ?></label>
	<input name="rssnews_rss1" type="text" id="rssnews_rss1" value="<?php echo $rssnews_rss1; ?>" size="100" maxlength="1000" />
	<select name="rssnews_direction1" id="rssnews_direction1">
		<option value='Off' <?php if($rssnews_direction1 == 'Off') { echo 'selected' ; } ?>>Off</option>
		<option value='On' <?php if($rssnews_direction1 == 'On') { echo 'selected' ; } ?>>On</option>
    </select>
	
	<label for="tag-title"><?php _e('Rss link and schedule','foldergallery'); ?> 2
	<?php echo ' newest '. get_option('pbrss-latestpostdate2').' vor ' . human_time_diff( strtotime(get_option('pbrss-latestpostdate2')),current_time( 'timestamp' )); ?></label>
	<input name="rssnews_rss2" type="text" id="rssnews_rss2" value="<?php echo $rssnews_rss2; ?>" size="100" maxlength="1000" />
	<select name="rssnews_direction2" id="rssnews_direction2">
		<option value='Off' <?php if($rssnews_direction2 == 'Off') { echo 'selected' ; } ?>>Off</option>
		<option value='On' <?php if($rssnews_direction2 == 'On') { echo 'selected' ; } ?>>On</option>
    </select>
	
	<label for="tag-title"><?php _e('Rss link and schedule','foldergallery'); ?> 3
	<?php echo ' newest '. get_option('pbrss-latestpostdate3').' vor ' . human_time_diff( strtotime(get_option('pbrss-latestpostdate3')),current_time( 'timestamp' )); ?></label>
	<input name="rssnews_rss3" type="text" id="rssnews_rss3" value="<?php echo $rssnews_rss3; ?>" size="100" maxlength="1000" />
	<select name="rssnews_direction3" id="rssnews_direction3">
		<option value='Off' <?php if($rssnews_direction3 == 'Off') { echo 'selected' ; } ?>>Off</option>
		<option value='On' <?php if($rssnews_direction3 == 'On') { echo 'selected' ; } ?>>On</option>
    </select>
	
	<label for="tag-title"><?php _e('Rss link and schedule','foldergallery'); ?> 4
	<?php echo ' newest '. get_option('pbrss-latestpostdate4').' vor ' . human_time_diff( strtotime(get_option('pbrss-latestpostdate4')),current_time( 'timestamp' )); ?></label>
	<input name="rssnews_rss4" type="text" id="rssnews_rss4" value="<?php echo $rssnews_rss4; ?>" size="100" maxlength="1000" />
	<select name="rssnews_direction4" id="rssnews_direction4">
		<option value='Off' <?php if($rssnews_direction4 == 'Off') { echo 'selected' ; } ?>>Off</option>
		<option value='On' <?php if($rssnews_direction4 == 'On') { echo 'selected' ; } ?>>On</option>
    </select>
	
	<label for="tag-title"><?php _e('Rss link and schedule','foldergallery'); ?> 5
	<?php echo ' newest '. get_option('pbrss-latestpostdate5').' vor ' . human_time_diff( strtotime(get_option('pbrss-latestpostdate5')),current_time( 'timestamp' )); ?></label>
	<input name="rssnews_rss5" type="text" id="rssnews_rss5" value="<?php echo $rssnews_rss5; ?>" size="100" maxlength="1000" />
	<select name="rssnews_direction5" id="rssnews_direction5">
		<option value='Off' <?php if($rssnews_direction5 == 'Off') { echo 'selected' ; } ?>>Off</option>
		<option value='On' <?php if($rssnews_direction5 == 'On') { echo 'selected' ; } ?>>On</option>
    </select>
	</div>
	
	<div style="height:10px;"></div>
	<input type="hidden" name="rssnews_form_submit" value="yes"/>
	<input name="rssnews_submit" id="rssnews_submit" class="button-primary" value="<?php _e('Update RSS import settings','foldergallery'); ?>" type="submit" />
	<?php wp_nonce_field('rssnews_form_setting'); ?>
	</form>
	</div>
	</div>
    <?php
}

// Function to call at the time of deactivation
function rssnews_deactivation() {
	// No action
}

// Plugin hook
register_activation_hook(__FILE__, 'rssnews_install');
register_deactivation_hook(__FILE__, 'rssnews_deactivation');
?>