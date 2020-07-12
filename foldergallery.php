<?php
/*
Plugin Name: Folder Gallery Slider
Plugin URI: https://github.com/svenbolte/foldergallery
Version: 9.7.5.27
Author: PBMod
Description: This plugin creates picture galleries and sliders from a folder or from recent posts. The gallery is automatically generated in a post or page with a shortcode. Usage: [foldergallery folder="local_path_to_folder" title="Gallery title"]. For each gallery, a subfolder cache_[width]x[height] is created inside the pictures folder when the page is accessed for the first time. The picture folder must be writable (chmod 777).
Tags: gallery, folder, lightbox, lightview, bxslider, slideshow, image sliders
Tested up to: 5.4.2
Requires at least: 4.0
Requires PHP: 5.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: foldergallery
Domain Path: /languages
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
		if ($_GET['code'] == md5( $_GET[ 'dlid' ] . intval(date('Y-m-d H:i:s')/24*60*60))) { // if it match it is legit
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
		wp_enqueue_style( 'fg-style', plugins_url( '/css/style.css', __FILE__ ) );
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
			return $size_in_mb . 'MB';
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


	// Verzeichnisliste ausgeben mit Erstelldatum und Moddatum [folderdir folder="wp-content/uploads/bilder/]" 
	public function meinedirliste( $atts ) {  // generate document/file download list

		extract( shortcode_atts( array(
			'folder'  => 'wp-content/uploads/pdf/',
			'protect'  => 0,
			'sort'	  => $fg_options['sort'],
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
						$mtime = date("Y.m.d H:i:s", filemtime($directory ."/". $file));
						$mtimed = date("d.m.y, H:i:s", filemtime($directory ."/". $file));
						$ctime = date("Y.m.d H:i:s", filectime($directory ."/". $file));
						$ctimed = date("d.m.Y, H:i:s", filectime($directory ."/". $file));
						$content = '<tr><td><img style="width:50px;height:auto;" src="' . $fileicon . '"></td><td style="vertical-align:middle">';
						if ( 1 == $protect ) {
							global $wp;
							$hashwert = md5($folder ."/". $file . intval(date('Y-m-d H:i:s')/24*60*60));
							$dllink = '<a style="font-size:1.2em" title="'.$ext.'&#10;herunter laden" href="'. add_query_arg( array('dlid' => $folder ."/". $file,'code' => $hashwert) , home_url() ) . '">'.$file.'</a>';
						} else {
							$dllink = '<a style="font-size:1.2em" title="'.$ext.' anzeigen oder&#10;herunter laden" href="'. home_url() . "/". $folder ."/". $file.'">' . $file . ' </a>';
						}
						$content .= $dllink . '  &nbsp; '.$dateigroesse.'  &nbsp; '.$creatext.' '. $ctimed . ' &nbsp; '.$modtext.' ' . $mtimed . '</td></tr>';
						$file_object = array(
							'name' => $file,
							'size' => filesize($directory ."/". $file),
							'mtime' => $mtime,
							'ctime' => $ctime,
							'content' => $content
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
		
		// ausgeben
		$gallery_code= '<div style="text-align:right"><form name="sorter" method="get"> <select name="sort">';
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
		$gallery_code.='<table>';
		foreach( $files as $fout ) {
			$gallery_code.= $fout['content'];
		}	
		$gallery_code.='</table>';
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
				default :
					$filesizer = $this->file_size(filesize( $folder . '/' . $pictures[ $idx ] ));
					$thecaption = $this->filename_without_extension( $pictures[ $idx ]) ;
						// $title ;
					if ( 'lightbox2' != $fg_options['engine'] ) {
						$moddate = date("d.m.Y H:i:s", filemtime( $folder . '/' . $pictures[ $idx ] ) + get_option( 'gmt_offset' ) * 3600);
						// $moddate = date("d.m.Y H:i:s", filemtime( $folder . ' / ' . $pictures[ $idx ] ) );
						$thecaption .= ' &nbsp;(' . ($idx+1) . '/' . ($NoP) . ') ' . $filesizer . ' &nbsp;' . $moddate;
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
		$gallery_code .= "\n\n<div class='nav-links'>";
		if ( intval($thumbpagination) > 1 ) {
			for ( $plink = 0 ; $plink < $NoP ; $plink++ ) {
				if ( ($plink/intval($thumbpagination) + 1) <> intval($seite) ) { $klasse="page-numbers"; } else { $klasse="page-numbers current"; }
				if ($plink % intval($thumbpagination) == 0 ) { $gallery_code .= " &nbsp;<a title='Fotos ".($plink + 1)."-".($plink + intval($thumbpagination))."' class='".$klasse."' href='".add_query_arg( array(), $wp->request )."?seite=".($plink/intval($thumbpagination) + 1) ."&sort=".$sort ."'>". ($plink/intval($thumbpagination) + 1) ."</a>"; }
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
		if ( ! in_array( $input['caption'], array( 'default','none','filename','filenamewithoutextension','smartfilename','modificationdater','modificationdatec','modificationdate','modificationdateandtime'  ) ) ) $input['caption'] = 'default';
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
	
	public function fg_settings()
	{
		$fg_options = get_option( 'FolderGallery' );
		echo '<div class="wrap">' . "\n";
		echo '<h2>' . __( 'Folder Gallery Settings', 'foldergallery' ) . "</h2>\n";
		echo '<p><code>[foldergallery folder="wp-content/uploads/../bilder/" title="Foto-Galerie" columns=auto width=280 height=200 thumbnails="all" show_thumbnail_captions=1 border=0 padding=0 margin=0]</code>  Shortcode für die Galerie</p>';
		echo '<p><code>[folderdir folder="wp-content/uploads/bilder/" protect=1]</code> Dokumenten-Liste eines Verzeichnisses ausgeben mit Erstelldatum und Dateiänderungsdatum, protect=1 schützt Ordner, ohne Parameter öffentlicher Zugriff auf die Deeplinks</p>';
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
				echo '>'. __('Default (Title + Picture Number)', 'foldergallery') . '</option>' . "\n";
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
		submit_button();
		echo "</form></div>\n";
	}
		
} //End Of Class

//
//   Jetzt Folder Slider Classes

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
		wp_enqueue_style( 'bxslider-style', plugins_url( 'jquery.bxslider/jquery.bxslider.css', __FILE__ ) );
		wp_enqueue_style( 'fsd-style', plugins_url( 'style.css', __FILE__ ) );
	}

	public function fsd_scripts( $param, $num ) {
		wp_enqueue_script( 'bxslider-script', plugins_url( 'jquery.bxslider/jquery.bxslider.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'fsd_slider-script', plugins_url( 'slider.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'fsd_slider-script', 'FSDparam' . $num , $param );
	}

	public function file_array( $directory ) { // List all JPG & PNG files in $directory
		$files = array();
		if( $handle = opendir( $directory ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( 'jpg' == $ext || 'png' == $ext ) {
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
			query_posts( $args );
			$slider_code = '<div class="bx-wrapper-noborder" style="font-size:1.6em;text-align:center">'. "\n";
			$slider_code .= '<ul class="bxslider bxslider' . $this->slider_no . '">';
			while (have_posts()) : the_post();
				$category = get_the_category(); 
				$cuttext = get_the_title();
				// $cuttext .= ' - '. ago(get_the_modified_time( 'U, d.m.Y H:i:s', false, $post, true ));
				if ( class_exists('ZCategoriesImages') && z_taxonomy_image_url($category[0]->term_id) != NULL ) {
					$cuttitle = '<img  style="max-height:280px;min-height:190px" title="'.$cuttext.'" src="' . z_taxonomy_image_url($category[0]->term_id) . '" ' .$picture_size.'>';
				} else {
					$cuttitle = '';
				}
				$slider_code .= '<li><a href="'.get_the_permalink().'">'. $cuttitle.'</a></li>';
				// $slider_code .= '<li><a href="'.the_permalink().'" rel="bookmark" title="'.the_title().'&#10;Aktualisiert:'. get_the_modified_time( 'l, d.m.Y H:i:s', false, $post, true ) . '&#10;' . ago(get_the_modified_time( 'U, d.m.Y H:i:s', false, $post, true )).'Beitrag aufrufen">'. $cuttitle.'</a></li>';
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
					$title = $this->smartfilename( $pictures[ $idx ] );
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
		if ( ! in_array( $input['captions'], array( 'none','filename','filenamewithoutextension','smartfilename' ) ) ) $input['captions'] = 'none';
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
		echo '<form method="post" action="options.php">' . "\n";
		settings_fields( 'FolderSlider' );
		echo "\n" . '<table class="form-table"><tbody>' . "\n";
		
		echo '<tr valign="top"><td colspan=2>' . "\n";
		echo "Shortcode Carousel:  [folderslider folder='wp-content/uploads/bilder/' width=400 height=0 speed=2.5 autostart=true captions=smartfilename controls=true pager=false playcontrol=false adaptiveheight=false maxslides=4 minslides=1 moveslides=1]";
		echo "<br>folder=recentposts zeigt letzte 8 Posts als Slider an\n";
		echo "</td>\n</tr>\n";		

		// Transition Mode
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="mode">' . __( 'Transition Mode', 'foldergallery' ) . "</label></th>\n";
		echo '<td><select name="FolderSlider[mode]" id="FolderSlider[mode]">' . "\n";		
			echo "\t" .	'<option value="horizontal"';
				if ( 'horizontal' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Horizontal', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="vertical"';
				if ( 'vertical' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Vertical', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="fade"';
				if ( 'fade' == $fsd_options['mode'] ) echo ' selected="selected"';
				echo '>' . __('Fade', 'folderslider') . "</option>\n";
		echo "</select>\n";
		echo "</td>\n</tr>\n";

		// Captions
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="captions">' . __( 'Caption Format', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderSlider[captions]" id="FolderSlider[captions]">' . "\n";		
			echo "\t" .	'<option value="none"';
				if ( 'none' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __( 'None', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="filename"';
				if ( 'filename' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="filenamewithoutextension"';
				if ( 'filenamewithoutextension' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Filename without extension', 'folderslider') . "</option>\n";	
			echo "\t" .	'<option value="smartfilename"';
				if ( 'smartfilename' == $fsd_options['captions'] ) echo ' selected="selected"';
				echo '>' . __('Smart Filename', 'folderslider') . "</option>\n";	
		echo "</select>\n";
		echo "</td>\n</tr>\n";
		
		
		// CSS
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row"><label for="css">' . __( 'CSS', 'foldergallery' ) . '</label></th>' . "\n";
		echo '<td><select name="FolderSlider[css]" id="FolderSlider[css]">' . "\n";		
			echo "\t" .	'<option value="noborder"';
				if ( 'noborder' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __( 'No border', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="shadow"';
				if ( 'shadow' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Border with shadow', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="shadownoborder"';
				if ( 'shadownoborder' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Shadow without border', 'folderslider') . "</option>\n";
			echo "\t" .	'<option value="black-border"';
				if ( 'black-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Black border', 'folderslider') . "</option>\n";	
			echo "\t" .	'<option value="white-border"';
				if ( 'white-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('White border', 'folderslider') . "</option>\n";	
			echo "\t" .	'<option value="gray-border"';
				if ( 'gray-border' == $fsd_options['css'] ) echo ' selected="selected"';
				echo '>' . __('Gray border', 'folderslider') . "</option>\n";	
		echo "</select>\n";
		echo "</td>\n</tr>\n";		

		$this->fsd_option_field( 'width', __( 'Width', 'foldergallery' ) , ' px ' . __( '(0 = auto)', 'foldergallery' ) );
		$this->fsd_option_field( 'height', __( 'Height', 'foldergallery' ), ' px ' . __( '(0 = auto)', 'foldergallery' ) );
		$this->fsd_option_field( 'speed', __( 'Speed', 'foldergallery' ), ' ' . __('seconds', 'folderslider') );
		$this->fsd_option_field( 'maxslides', __( 'Carousel', 'foldergallery' ), ' ' . __('Bild(er) nebeneinander', 'folderslider') );

		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'Controls', 'foldergallery' ) . "</th>\n";
		echo "<td><fieldset>\n";
		echo '<label for="controls">';
			echo '<input name="FolderSlider[controls]" type="checkbox" id="FolderSlider[controls]" value="1"';
			if ( $fsd_options['controls'] ) echo ' checked="checked"';
			echo '> ' . __('Show Previous/Next Buttons', 'folderslider') . "</label><br />\n";
		echo '<label for="controls">';
			echo '<input name="FolderSlider[activeheight]" type="checkbox" id="FolderSlider[activeheight]" value="1"';
			if ( $fsd_options['activeheight'] ) echo ' checked="checked"';
			echo '> ' . __('Auto Adjust Height', 'folderslider') . "</label><br />\n";
		echo '<label for="playcontrol">';
			echo '<input name="FolderSlider[playcontrol]" type="checkbox" id="FolderSlider[playcontrol]" value="1"';
			if ( $fsd_options['playcontrol'] ) echo ' checked="checked"';
			echo '> ' . __('Show Play/Pause Button', 'folderslider') . "</label><br />\n";
		echo '<label for="autostart">';
			echo '<input name="FolderSlider[autostart]" type="checkbox" id="FolderSlider[autostart]" value="1"';
			if ( $fsd_options['autostart'] ) echo ' checked="checked"';
			echo '> ' . __('Start Slider Automatically', 'folderslider') . "</label><br />\n";
		echo '<label for="pager">';
			echo '<input name="FolderSlider[pager]" type="checkbox" id="FolderSlider[pager]" value="1"';
			if ( $fsd_options['pager'] ) echo ' checked="checked"';
			echo '> ' . __('Show Pager', 'folderslider') . "</label>\n";
		echo "</fieldset>\n";
		echo "</td>\n</tr>\n";		

		// WPML
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'WPML', 'foldergallery' ) . '</th>' . "\n";
		echo '<td><label for="wpml">';
			echo '<input name="FolderSlider[wpml]" type="checkbox" id="FolderSlider[wpml]" value="1"';
			if ( 1 == $fsd_options['wpml'] ) echo ' checked="checked"';
			echo '> ' . __('Fix WPML Paths', 'folderslider') . "</label><br />\n";
		echo "</td>\n</tr>\n";

		// Doubleklick auf Bild öffnet Link zum Darstellen in einer Lightbox wie Fancybox3 mit a href Zuordnung (wie in Theme penguin)
		echo '<tr valign="top">' . "\n";
		echo '<th scope="row">' . __( 'Link zum Bild', 'foldergallery' ) . '</th>' . "\n";
		echo '<td><label for="lightboxlink">';
			echo '<input name="FolderSlider[lightboxlink]" type="checkbox" id="FolderSlider[lightboxlink]" value="1"';
			if ( 1 == $fsd_options['lightboxlink'] ) echo ' checked="checked"';
			echo '> ' . __('Zoom Link zum Bild für Lightbox wie Fancybox aktivieren', 'folderslider') . "</label><br />\n";
		echo "</td>\n</tr>\n";
		echo "</tbody></table>\n";
		submit_button();
		echo "</form></div>\n";

	}
		
} //End Of Class


?>