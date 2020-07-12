=== Folder Gallery and Slider with documentlist ===
Contributors: vjalby,PBMod
Tags: gallery, folder, lightbox, Folder Slider, bxslider
Requires at least: 4.0
Tested up to: 5.4.2
Stable tag: 9.7.5.31
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin generates file listings, picture galleries and Sliders from a folder using three types of shortcodes.
The pictures folder must be uploaded (using FTP) somewhere on the server (e.g. wp-content/upload). It must be writable (chmod 777).
Folder Gallery Plugin does not include any lightbox JS engine anymore. You have to install one or use a compatible lightbox plugin. See FAQ.

To show a documents list for download pdf files use shortcode

	[folderdir folder="wp-content/uploads/bilder/" protect=1 sort=size_desc]

To show a photo slider from a gallery:

	[folderslider folder="wp-content/upload/MyPictures" width=500 mode=fade speed=2.5 captions=smartfilename controls=false]

To include a gallery in a post or a page, you have to use the following shortcode :

	[foldergallery folder="path" title="title" columns=1 width=150 height=90 border=1 padding=2 margin=10 thumbnails=single]
 
Sort options for Folderdir are: (default taken from fg Settings and can be overridden with shortcode)
			case 'size' :
			case 'size_desc' :
			case 'date' :
			case 'date_desc' :
			case 'name_desc' :
			default: 'name' :
Folder Gallery	Sort options are the same, except size paramaeters.

For each gallery, a subfolder cache_[width]x[height] is created inside the pictures folder when the page is accessed for the first time. 
An Options page allow to set the default paramaters of the galleries :

* Lightbox JS Engine: Lightbox 2 (if installed), Fancybox 2 (if installed), Fancybox 3 (if installed), Lightview 3 (if installed), Easy Fancybox Plugin (if available), Responsive Lightbox Plugin (if available) or none (default)
* Display Thumbnails (thumbnails): all = standard Gallery, single = displays a single thumbnail linked to the lightbox gallery, none = displays a link to the lightbox gallery
* Sort pictures by (sort) : pictures are sorted by filename (filename) or in reverse order (filename_desc) or by modification date (date or date_desc) or randomly (random)
* Number of images per row (columns)
* Thumbnails width and height (width & height)
* Picture border (border)
* Padding and Margin (padding & margin)
* Caption Format (caption): default (title + picture number), filename, filenamewithoutextension, smartfilename (filename with underscores and front numbers removed), modificationdate, modificationdateandtime, modificationdater (RFC 2822), modificationdatec (ISO 8601), none
* Show Thumbnail Captions (show_thumbnail_captions): yes (true) or no (false). Display (or not) the caption under the picture thumbnail.
* Fancybox 2 Caption Style: Inside, Outside, Over, Float, None. Available with Fancybox 2 engine only (if installed).
* Fancybox 2 Transition: Elastic, Fade. Available with Fancybox 2 engine only (if installed).
* Fancybox 2 Autoplay Speed: Slideshow speed in seconds. 0 to turn autoplay off. Available with Fancybox 2 engine only (if installed).
* Fancybox 3 Options : Loop, Toolbar, Infobar, Arrows, Fullscreen, Slideshow, Slideshow speed. Available with Fancybox 3 engine only (if installed).
* Misc settings - Permissions: force 777 permissions on cache folder. Leave it uncheck unless you really know what you do!
* Misc settings - Orientation: Correct picture orientation according to EXIF tag. Rotated pictures will be overwritten. (Require EXIF library in PHP.) Delete the cache folder to apply to existing galleries.
Most of these settings can be overridden using the corresponding shortcode


== Installation ==

1. Unzip the archive foldergallery.zip
2. Upload the directory 'foldergallery' to the '/wp-content/plugins/' directory
3. Download and upload to '/wp-content/' directory at least on lightbox JS engine (see FAQ)
4. To use Lightbox engines, see FAQ
5. Activate the plugin through the 'Plugins' menu in WordPress
6. Go to Settings / Folder Gallery to change the default settings
7. Upload a folder of pictures to 'wp-content/upload/MyPictures'
8. Insert the following short code in post or page :

	[foldergallery folder="wp-content/upload/MyPictures" title="My Picture Gallery"]
  	  or
	[folderdir folder="wp-content/upload/MyPictures" protect=1]
	

== Frequently Asked Questions ==

= How to install Lightbox 2 JS engine? =

1. Download Lightbox 2 from http://lokeshdhakar.com/projects/lightbox2/
2. Unzip the archive
3. Upload the directory 'dist' to '/wp-content' and rename it to 'lightbox'
4. Go To Wordpress > Settings > Folder Gallery and select Lightbox 2 as Gallery Engine.
5. Done!

= How to install Fancybox 3? =

1. Download FancyBox 3 from http://fancyapps.com/fancybox/3/
2. Unzip the archive then rename the directory to 'fancybox3'.
3. Upload the directory 'fancybox3' to '/wp-content'.
4. Go To Wordpress > Settings > Folder Gallery and select Fancybox 3 as Gallery Engine.
5. Save Changes and set Fancybox options.
6. Done!

= How to install Lightview 3? =

1. Download lightview from http://projects.nickstakenburg.com/lightview/download
2. Unzip the archive then rename the directory to 'lightview' (i.e., remove version number).
3. Upload the directory 'lightview' to '/wp-content'.
4. Go To Settings / Folder Gallery and select Lightview as Gallery Engine.
5. Done!

You can specify lightview options with the shortcode attribute 'options':

	[foldergallery folder="path" title="My Gallery"	options="controls: { slider: false }, skin: 'mac'"]
	
You can set default options in Folder Gallery Options Page. 

See http://projects.nickstakenburg.com/lightview/documentation for details about Lightview options.

= Can I use Folder Gallery along with another Lightbox plugin? =

Folder Gallery has built-in support for "Easy Fancybox" plugin by RavanH, "Responsive Lightbox" plugin by dFactory, and "Slenderbox" plugin by Matthew Petroff. After activating the plugin, select it in Folder Gallery Settings (Gallery Engine).

Otherwise, if your Lightbox plugin automatically handles images, you may set the lightbox engine to 'None' in Folder Gallery Options.
This should work with

* jQuery Colorbox 4.6+ by Arne Franken
* Lightview Plus 3.1.3+ by Puzich
* Maybe other

= Can I use Easy Fancybox plugin along with Folder Gallery? =

Yes! First install and activate Easy Fancybox plugin. In Wordpress > Settings > Media > Fancybox > Images > Gallery, Disabled Autogllery. Then, in Wordpress > Settings > Folder Gallery, select "Easy Fancybox (plugin)" as Gallery Engine.

= I'd like to display a single thumbnail instead of the full thumbnails list =

Add the attribute `thumbnails` in the shortcode with value `single` to display only the first thumbnail.

	[foldergallery folder="path" title="My Gallery" thumbnails="single"]

If you want to use a different picture (than the first) as the single thumbnail for the gallery, add a picture with name !!! (e.g., `!!!.jpg`) to your gallery. This picture will be used as thumbnail, but won't be included in the (lightbox) gallery. Another option is to use the shortcode attribute `thumbnails=-n` where `n`is the picture number (in the gallery) you want to use as single thumbnail. 

To hide gallery title under the thumbnail, add `title=""`. You then should set `caption' to something else than `default`, e.g., `caption="filename"`.

= I'd like to display only the n first thumbnails instead of the full thumbnails list =

Add the attribute `thumbnails` in the shortcode with value `n` to display only the n first thumbnails.

	[foldergallery folder="path" title="My Gallery" thumbnails=3]

= I'd like to display a (sub)title under each thumbnail =

You have to set show_thumbnail_captions to 1 (or change the global option in Folder Gallery Settings) using 

	[foldergallery folder="path" title="My Gallery" show_thumbnail_captions=true]

The caption format is set with the attribute `caption`. It can be set to `filename`, `filenamewithoutextension` or `smartfilename` which displays the filename without extension, front number removed and underscores (_) replaced with spaces.

	[foldergallery folder="path" title="My Gallery" show_thumbnail_captions=1 caption='smartfilename']

========================================= Slider =========================================================================================

To include a slider in a post or a page, you have to use the following shortcode :

	[folderslider folder="local_path_to_folder"]

An Options page allow to set the default paramaters of the sliders :

* Transition Mode (mode): horizontal, vertical, fade
* Caption Format (captions): none, filename, filenamewithoutextension, smartfilename (filename with underscores, extension and front numbers removed)
* CSS (css): change the frame around slider: 'noborder', 'shadow', 'shadownoborder', 'black-border', 'white-border', 'gray-border'
* Width and Height of the slider (width and height)
* Speed (speed):  time between slides in seconds
* Previous/Next Buttons (controls): true or false
* Play/Pause Button (playcontrol): true or false
* Start Slider Automatically (autostart): true or false
* Pager (pager): true or false

Default slider width is the width of the first picture unless the attribute width is set to a non-zero value. The height is calculate for each picture (to keep ratio) unless the attribute height is set to a non-zero value.

Most of theses settings can be overridden using the corresponding shortcode attribute:

	[folderslider folder="wp-content/upload/MyPictures" width=500 mode=fade speed=2.5 captions=smartfilename controls=false]
 
This plugin uses bxSlider 4.2.5 by Steven Wanderski - http://bxslider.com 



== Changelog ==

= 9.7.5.30
[folderdir] gets option protect=1. If given, files in folder can only be downloaded. Download links are only valid for the rest of the day
 (one-day-pass) to prevent sharing deeplinks. Folder content is protected by .htaccess set to deny

= 9.7.5.27
Folderdir shortcode gets parameter sort= to be sorted. This can also be done by adding url paramater ?sort=file_desc (file size size_desc date date_desc)
A Select box is displayed on top of the table to invoke sorting from the frontend

= 9.7.5.25
* Add [folderdir] shortcode which displays a file list of filetypes pdf, zip, exe, pptx, docx, xlsx for document downloading or for tech datasheets

= 9.7.5.12
* Pagination navigation mit Seitenzahlen, lädt nur Bild-Thumb-Inhalt der aktuellen Page
* Bildunterschrift
* Fancybox3 CSS Anpassungen, Anzeige toolbar/infobar auch wenn adminbar zu sehen ist.

= 1.7.4 [2017-11-18] =
* FancyBox 3 support (see FAQ)
* WPML fix option
* Misc bugs fixed

= 1.7.3 [2017-08-12] =
* PHP7 compatibility

= 1.7.2 [2014-12-21] =
* Support for Slenderbox plugin

= 1.7.1 [2014-11-28] =
* Compatibility with WPML plugin
* Date and time caption formats

= 1.7 [2014-09-04] =
* Option to correct picture orientation according to EXIF tag. Require PHP with EXIF library. Rotated pictures will be overwritten.
* Support for Nivo Lightbox in Responsive Lightbox Plugin

= 1.6 [2014-07-17] =
* Because of license issue, Lightbox 2 is not included in Folder Gallery anymore. You have to install it yourself (see FAQ)
* Folder Gallery doesn't include any JS lightbox engine anymore. You have to install one yourself (see FAQ)
* Reorganize Settings page
* Add an option to force 777 permissions to cache folders
* Workaround for unactive glob function

= 1.5b2 [2014-02-22] =
* Because of license compatibility, Fancybox 2 is not included in Folder Gallery anymore. You have to install it yourself or use a Fancybox plugin (see FAQ)
* Change the location of Lightview 3 installation (see FAQ)
* Support for Responsive Lightbox Plugin
* Support for Easy Fancybox Plugin
* Global option to set transition effect when Fancybox 2 engine is selected
* Option (and attribute) to sort pictures randomly
* Rewrite directory scan : support for gif, bmp pictures ; sort by date option

= 1.4 [2013-08-31] =
* Global option to display the caption under the picture thumbnail.
* Several changes in layout and CSS. Hopefully it breaks nothing!
* New 'smartfilename' option for caption style
* Option (and attribute) to sort pictures by filename in reverse order
* Improved captions support
* Compatibility with PhotoSwipe WP plugin
* Minor bug fixes

= 1.3 [2013-08-05] =
* Update Lightbox 2 JS to 2.6 (JQuery/Wordpress 3.6 compatibility)
* Update Fancybox JS to 2.1.5
* Global option to set picture's caption style (default, filename, filenamewithoutextension, none)
* Global option to set autoplay speed when Fancy Box engine is selected.
* Option to display the thumbnails of the first pictures only. Read the FAQ!
* Several changes and improvements related to single-thumbnail-gallery (thumbnails="single"). Read the FAQ!
* Misc bug corrections

= 1.2 [2013-03-16] =
* Pictures are now sorted alphabetically
* Global option to change title style when fancybox engine is selected
* Misc changes to support a forthcoming plugin

= 1.1 [2013-02-18] =
* Add a 'thumbnails' option/attribute to set how many thumbnails should be displayed in the gallery : all (default), single or none (display a link instead).
* Improved error messages
* Update FAQ

= 1.0 [2013-02-03] =
* Fix a problem with case of file extension of thumbnails.
* Update Fancybox to 2.1.4

= 0.97 [2013-01-16] =
* Scripts are only loaded on pages with galleries
* Add support for fancybox (included)
* Add an option to change gallery 'engine' (Lightbox, Fancybox, Lightview when installed or None)
* Misc changes 

= 0.95 [2013-01-10] =
* Internationalization (English, French)
* Support for Lightview 3 (see FAQ)
* Code cleaning
* Small improvements

= 0.92 [2013-01-05] =
* Add a 0-column option (When 'Images per Row' is set to 0, the number of columns is set automatically.)
* Misc changes

= 0.90 [2013-01-05] =
* First released version
