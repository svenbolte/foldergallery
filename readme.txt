=== Folder Gallery, Slider, documentlist, csv from file/url table, RSSDisplay, RSStoPosts, Adventcalendar ===
Contributors: wibergsweb,vjalby,PBMod
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Version: 9.7.6.26
Stable tag: 9.7.6.26
Requires at least: 5.1
Tested up to: 5.6
Requires PHP: 7.2

This plugin generates file listings, picture galleries and Sliders from a folder using three types of shortcodes.
The pictures folder must be uploaded (using FTP) somewhere on the server (e.g. wp-content/upload). It must be writable (chmod 777).
Folder Gallery Plugin does not include any lightbox JS engine anymore. You have to install one or use a compatible lightbox plugin. See FAQ.
Descriptions can be uploaded in a text file with filename.jpg,descritiontext. Once found in the folder desriptions will be displayed in dirlist an in gallery.
You can display rss feeds at shortcode position 'rssdisplay' in shortened or full display

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

=
============================================================================ Slider =========================================================================================
=

To include a slider in a post or a page, you have to use the following shortcode :

	[folderslider folder="local_path_to_folder"]

An Options page allow to set the default paramaters of the sliders :

* Transition Mode (mode): horizontal, vertical, fade
* Caption Format (captions): none, filename, filenamewithoutextension,
		'smartfilename' : (filename with underscores, extension and front numbers removed), 
		'filenamesize': filename and file size
		'filenamesizedate': filename, filesize, picture index number, regional date (l18n Format)
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
 
This plugin uses bxSlider 4.2.5 by Steven Wanderski - https://bxslider.com 


=
========================================= Shortcode csv to html =========================================================================================
=

== Description ==

CSV to html makes it easy to fetch content from csv-file(s) and put content from that file/those files and display the html(table) on a page with a single shortcode. 
If you created files to use with the Visualizer Plugin, those are formatted in a specific way and if you have saved the csv-file(s) from excel,  the csv looks 
different. CSV to html handles both these types and it's fairly easy to extend the plugin to use other mechanisms to identify a specific type of format of the csv-file(s).
If using more then one file, content from all files are mixed into one single table - rather then creating two tables. It's possible to fetch information from csv files from webservers upload folder (or a subfolder to the uploadsfolder) or 
from an external source (domain).

= Shortcodes =
* [csvtohtml_create] - Create the html table from specified csv-file(s)

= URL parameters =
You can click on the header titles to sort toggling from asc to desc or give url parameters:

?sort=1,3,4 - sorts by given columns (column numbers are columns of unfiltered list
&order=asc,desc,asc - sorts the given columns 1,3,4 1asc 2desc 3asc

&search=fulltextkeyword - performs a full text search and lists only rows containing that keyword

= [csvtohtml_create] attributes =
* title - set title that is shown as text in top left corner of html table (else nothing is shown there)
* html_id - set id of this table
* html_class - set class of this table (besides default csvtohtml - class)
* path - relative path to uploads-folder of the wordpress - installation ( eg. /wp-content/uploads/{path} )
* source_type - what type to use for identifying content in csv-files (valid types are guess and visualizer_plugin).
* fetch_lastheaders - Number of specific headers to retrieve (from end)
* source_files - file(s) to include. If using more than one file - separate them with a semicolon (;). It 's (from v1.0.2) possible to include a full url instead of a filename to fetch external csv files. It's also possible (v1.1.36) to fetch files from a given path (with for example *.csv). 
* csv_delimiter - what delimiter to use in each line of csv (comma, semicolon etc)
* exclude_cols - What columns to exclude in final html table (1,2,3,10-15 would exclude columns 1,2,3,10,11,12,13,14 and 15). If you want to remove the last column, then simply type last instead of entering a number.
* include_cols - What columns to include in final html table (1,2,3,10-15 would display column 1,2,3,10,11,12,13,14 and 15). If include_cols is given, then exclude_cols is ignored.
* eol_detection - CR = Carriage return, LF = Line feed, CR/LF = Carriage line and line feed, auto = autodetect. Only useful on external files. Other files are automatically autodeteced.
* convert_encoding_from - When converting character encoding, define what current characterencoding that csv file has. (Not required, but gives best result)
* convert_encoding_to - When converting character encoding, define what characterencoding that csv should be encoded to. (Best result of encoding is when you define both encoding from and encoding both)
* sort_cols - Which column(s) to sort on in format nr,nr och nr-nr (example 1,2,4 or 1-2,4)
* sort_cols_order - Which order to sort columns on (asc/desc). If you have 3 columns, you can define these with different sorting like asc,desc,asc      
* add_ext_auto - Add fileextension .csv to file (if it's not specified in the source_files). Set to no if you don't file extension to be added automatically.
* float_divider - If fetching float values from csv use this character to display another "float-divider chacter" than dot (e.g. 6,3 instead of 6.3, 1,2 instead of 1.2 etc)

= Default values =
* [csvtohtml_create title="{none}" html_id="{none}" html_class="{none}" source_type="guess" path="{none}" fetch_lastheaders="0" source_files="{none}" csv_delimiter="," exclude_cols="{none} include_cols="{none}" eol_detection="cr/lf" convert_encoding_from="{none}" convert_encoding_to="{to}" sort_cols="{none}" sort_cols_order="asc" add_ext_auto="yes" float_divider="." debug_mode="no"]

== Example of usage == = shortcodes in post(s)/page(s) =
* [csvtohtml_create source_type="visualizer_plugin" path="lan" source_files="skane.csv;smaland.csv;lappland.csv"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1.csv;excel2.csv"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="*.csv"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1;excel2" debug_mode="yes"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1.csv;excel2.csv" debug_mode="yes" fetch_lastheaders="3"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1;excel2;https://mysite.domain/map/camouflage.csv" debug_mode="yes"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1;excel2;https://mysite.domain/map/camouflage.csv" include_cols="5,6,7,12-14" eol_detection="auto" debug_mode="yes"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="excel1;excel2;https://mysite.domain/map/camouflage.csv" exclude_cols="3" debug_mode="yes" eol_detection="CR/LF"]
* [csvtohtml_create html_class="tablesorter" source_type="guess" path="excelfolder" source_files="excel1;excel2;http://wibergsweb.se/map/sweden.csv" exclude_cols="3,7,9,11-13"]
* [csvtohtml_create source_type="guess" path="excelfolder" source_files="whatever.csv" csv_delimiter=";"]
* [csvtohtml_create source_type="guess" source_files="https://mysite.domain/map/camouflage.csv" convert_encoding_from="Windows-1252" convert_encoding_to="UTF-8"]
* [csvtohtml_create source_type="guess" source_files="https://mysite.domain/map/camouflage.csv" convert_encoding_to="UTF-8"]
* [csvtohtml_create source_type="guess" source_files="https://mysite.domain/map/camouflage.csv" sort_cols="1,2" sort_cols_order="desc,asc"]

== Example css ==
* .csvtohtml th.colset-1 {background:#ff0000 !important;}
* .csvtohtml .even{background:#ddd;}

=
========================================= Shortcode rssdisplay =========================================================================================
=

[rssdisplay url="https://ssl.pbcs.de/dcounter/shopadd.asp?mode=rss&amp;items=30&amp;shopid=" excerpt="1" wordcount="20" ]

            'url' => 'https://ssl.pbcs.de/dcounter/shopadd.asp?mode=rss&items=30&shopid=',
			'excerpt' => '0',

=
========================================= iCalendar Shortcode ics event =================================================================================
=

adds an "ics-shortcode" shortcode that allows you to import events from an iCalendar file and view them as list or in month calendar.

== Usage and Shortcode parameters ==
* Add the [ics_events url="https://ssl.pbcs.de/dcounter/calendar-ics.asp?action=history" items="8" sumonly="1"] shortcode to page or post or html widget
* Shortcode Parameters:
	'title' => '',   // Set a title if you want to. it will be displayed as table header
	'url'  => '',  // URL with the ics file - required parameter
	'items'  => 5, // set a number to limit number of items displayed in listings/calendars
	'sumonly' => 0, // set to "1" if you do not want to list description and location
	'showold' => 0, // set to "1" to list older entries (happened before today)
	'view' => 'list', // list or calendar display or list,calendar for both
	'noeventsmessage' => '',  //if no events found nothing or this text will be displayed

=
============================================================================================================================================================
=

RSStoPosts is a simple plug-in to import up to 5 rss feeds into posts, scheduled daily with on/off toggle
* Goto Admin and Settings Folder Gallery, enter wanted rss feeds and thew will be imported to posts once a day if set to "on" 
* Cron jobs will be created and removed on on/off toggle and newer posts will be imported daily 


=
============================================================================================================================================================
=

## PBAdvent Description ##

PB Advent stellt einen Shortcode für Wordpress bereit, der einen Adventskalender in Seiten oder Beiträgen darstellt.
SEO optimiert: Dabei wird minimaler Code verwendet und optimiertes minimales css, das nur geladen wird, wenn der Shortcode verwendet wird
Als Hintergrundbild wird jeweils eins von 10 Motiven
Mit responsiver Darstellung auch auf Mobilgeräten. Die Lösung ist rein HTML / PHP und CSS3 basiert.
Zuordnung der aufrufbaren Seiten nach Zufallsprinzip oder linear (auf den Tag genau)
Öffnen des Türchens nur am jeweiligen Tag oder danach möglich - und nur im Dezember
aufrufbaren Seiten können normale, vorhandene oder neu zu erstellende Posts sein. 
Um zu verhindern, dass diese Posts zu früh aufgerufen werden, gibt man ihnen ein Veröffentlichungsdatum vom gewünschten Tag

## Usage ##

[pbadventskalender pages="adventsgeschichte,sonderangebot,uhrzeit-stellen,23,422,11,20344"]

* pages können bis zu 24 komma-getrennte Werte sein:
  * Ist der Wert eine Zahl (integer), so wird der Beitrag mit der ID dieser Zahl aufgerufen (Shortlink: ?p= wird automatisch ergänzt)
  * Alternativ kann der Wert der relative Permalink (slug) zur Seite/Beitrag/Produkt oder custom post type sein (z. B. staubsauger-w600)
  Werden weniger als 24 Werte übergeben, erfolgt die Zuordnung zu den Seiten nach Zufallsprinzip
* debug=1 added to show calendar in other months than dec, default=0 it omitted
* folder="wp-content/plugins/foldergallery/images"  findet alle JPG PNG Bilder im Ordner und ermittelöt Zufallszahl background image zur Anzeige. Defaults to plugins image folder


## Background images (advanced users) ##

Enthaltene Bilder sind von mir erstellt und CC0-lizensiert, frei verwendbar. Im Ordner /images unter dem Plugin zu finden.
Für eigene Motive bitte in einen Ordner hochladen und folder= Pfad angeben


=
============================================================================================================================================================
=


== Changelog ==

= 9.7.6.26 =
localisationm, german translations, description updated

= 9.7.6.24 =
Display message of advent calender appearing only in november, else empty string

= 9.7.6.23 =
RSS to Post import uploads external image(s) in rss feed to wordpress upload folder uploads/pbrss and replaces their url to the new one
before creating the post.

= 9.7.6.22 =
advent calendar: shortcode attribute debug=1 added to show calendar in other months than dec
shortcode-attr folder="wp-content/plugins/foldergallery/images"  finds all JPG PNG images in the folder and chooses a random background image to display

= 9.7.6.21 =
labels on advent calendar doors get theme accent color (penguin mod) else set to red

= 9.7.6.20 =
added pbadventskalender Shortcode that will display an advent calendar with urls given as shortcode parameter and 12 background pictures

= 9.7.6.17 =
ICS Import now takes X-ALT-DESC;FMTTYPE=text/html from ical files and displays the link in calendar and list views.
for list views sumonly needs to be set to 0, on calendar view summary will be replaced by link whenever field is not empty
= 9.7.6.15 =
folder gallery - add human date to caption
expand caption size to 3 lines and remove html tags from title tag of thumbnail div

= 9.7.6.14 =
Folder Slider: additional captions selectable (file, size, date with human date), Styling caption centered
Translations updated

= 9.7.6.13 =
ics - calendar display fixes, sanitizing

= 9.7.6.12 =
style wordbreaks ics table fixed 

= 9.7.6.11 =
* RSSToposts function with wp schedule added
RSStoposts Admin area modern style
RSStoposts integrate wp cron schedule daily and on/off toggle
RSStoposts 5 rss feeds can be given and wp integrated simplepie is used to fetch them
RSStoposts They are imported as posts, categories are created, if not present
RSStoposts *** Todo: Picture upload if external url links to pictures are in the rss feeds

= 9.7.6.10 = 
Add dummy background icon (news-tablet) for recent post slider (if no category icon is set)

= 9.7.6.9 = 
New version of bxslider 4.2.14 added
style optimizations

= 9.7.6.8 = 
sorting bugfixes and optimizations csv listing

= 9.7.6.7 = 
ICS Shortcode plugin merged to the foldergallery plugin shortcodes toolbox

= 9.7.6.6 = 
ICS download will be cached for 6 hrs now

= 9.7.6.5 = 
ics shortcode - bugfixes
ics shortcode - view calendar and or list option, calendar lists all events in the ics file limited to items value

= 9.7.6.4 = 
ics shortcode - parameter: view choose to display current months events as calendar.
ics shortcode - coding for event array to display in calandar still under construction

= 9.7.6.3 = 
ics shortcode - showold parameter lists entries from ics that are in the past

= 9.7.6.2 = PBMod
ics shortcode - Update ics parser files from project
ics shortcode - Add url check and error bypass
ics shortcode - Display results as table
ics shortcode - use in html widget (if theme supports this)
ics shortcode - modified and added shortcode parameters
ics shortcode - set timezone and location to europe/berlin / de_DE
ics shortcode - fix date display localized

= 9.7.5.48
sorting bug fixed
you can set html_class="small" to display table in smaller font 0.8em

= 9.7.5.42-47
Translations updated (de-de and de-de-formal

= 9.7.5.42-45
Shortcode rssdisplay added pulls rss feed and displays full or shortened link and content
Bugfixes. Pagination did not display first page cos array counting from 0
sanitizing Text Information
declare empty variables if not set

= 9.7.5.41
CSVtotable downloads from url will be cached for 1 hour now

= 9.7.5.40
Variable declaration notices und adjustments php 7.4.9

= 9.7.5.39
missing variable declaration bugfix

= 9.7.5.36
Bugfixes regarding csv imports. autoadd csv disabled by default (cause most of my csv exporting sites use a webservice in url and not a filename
Declared some variables causing php notices if not set

= 9.7.5.35
Merged csv to html class:
Add functionality and shortcode to display a csv file from url or from wordpress folder on a page or post

= 9.7.5.32
a file "descriptions-vorlage.txt" with all filenames of the folder will be put and updated automatically now. Just add a description after the comma and rename to descriptions.txt and you have file
and image descriptions. Added the file tpye .GIF to Folderslider (former only png and jpg was allowed)

= 9.7.5.31
Folderdir: if a text file descriptions.txt with filename.pdf,detailed description text is in folder, a description is listed with each filename found
Foldergallery: descriptions.txt aswell working for galleries. caption type can be selected in settings or via shortcode param caption=...

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
