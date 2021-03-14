### Folder Gallery, Slider, documentlist, csv from file/url table, RSSDisplay, RSStoPosts, Adventcalendar, Bookmarkimport ###

This plugin generates file listings, picture galleries and Sliders from a folder using three types of shortcodes.
The pictures folder must be uploaded (using FTP) somewhere on the server (e.g. wp-content/upload). It must be writable (chmod 777).
Folder Gallery Plugin does not include any lightbox JS engine anymore. You have to install one or use a compatible lightbox plugin. See FAQ.
Descriptions can be uploaded in a text file with format: filename.jpg,descritiontext. Once found in the folder desriptions will be displayed in dirlist an in gallery.
shortcode to display csv files from external url or from the uploads folder
shortcode to display RSS feeds from external URL as list in long or shortened excerpt form
Shortcode to display ICS and icalendar events from the web in lists
Wp schedule function to import up to 5 external rss feeds to posts.

## How to ... ##

To import rss feeds to posts Goto Admin and Settings FG Rss2Posts, enter wanted rss feeds and thew will be imported to posts once a day if set to "on" 

To show a documents list for download pdf files use shortcode

	[folderdir folder="wp-content/uploads/pdf/" protect=1 sort=size_desc]

To show a photo slider from a gallery:

	[folderslider folder="wp-content/uploads/bilder" width=500 mode=fade speed=2.5 captions=smartfilename controls=false]

To include a gallery in a post or a page, you have to use the following shortcode :

	[foldergallery folder="wp-content/uploads/bilder" title="Meine Galerie" columns=1 width=150 height=90 border=1 padding=2 margin=10 thumbnails=single]

For each gallery, a subfolder cache_[width]x[height] is created inside the pictures folder when the page is accessed for the first time. 

To read csv from url and display as a sortable and searchable table on post/page use this shortcode:

	[csvtohtml_create source_files="https://domain.de/sweden.csv"]
	
To display csv from file(s) located in wordpress upload/mapfiles dir: (content will be cached 1 hrs)

	[csvtohtml_create path="mapfiles" source_files="sweden.csv;norway.csv;iceland.csv"]
	
To display an RSS Feed from given URL use the following shortcode:	
	
	[rssdisplay excerpt="1" wordcount=25 url="https://domain.de/rss.xml" ]
	
"ics-shortcode" shortcode that allows you to import events from an iCalendar file.

	[ics_events url="https://ssl.pbcs.de/dcounter/calendar-ics.asp?action=history" items="8" sumonly="1"]
	
To display the advent calendar in december (or a to come message on other months use shortcode:

	[pbadventskalender pages="adventsgeschichte,sonderangebot,uhrzeit-stellen,23,422,11,20344"]


# Import Bookmarks
Import browser bookmarks into WordPress.
Export bookmarks from your browser and import them as a list in a WordPress post. Supports Custom Post Types and Post Formats.

## Usage
In WP Admin, head over to *Import > Import Bookmarks*, select a bookmarks file (in **HTML format**, as exported from your browser of choice) to upload, and choose the destination post type and status.

### Custom Post Types
Bookmarks can be added as regular Post or instances of a Custom Post Type.
This plugin, on its own, will **not** register a new Custom Post Type (nor modify existing ones).

### Post Statuses
Newly added bookmarks can be marked "Published," "Draft," "Pending review" or "Private."

### Post Formats
Newly added bookmarks can be given any [Post Format](https://developer.wordpress.org/themes/functionality/post-formats/). Note: it's your active theme that decides what this means for your site. If unsure, you'll probably want to use either "Link" or "Standard." Lastly, *only Post Types that support Post Formats are actually affected*.

### Dates and Archives
**Post dates** are set to the date the bookmark was created. Monthly archives, etc., work just fine.
**Duplicated entries** are not (yet) detected.
A **possible use case** would be to periodically "dump" all of your bookmarks into a WordPress-powered "linklog," then clear them all from your browser—rinse, repeat. (However, if you *did* want to import the same link twice, e.g., with a different title, that would be perfectly fine.)

## Advanced: Modifying the Format of Imported Bookmarks
Use the `import_bookmarks_post_content` filter to **customize imported bookmarks' markup** with minimal PHP.
Some examples of what's possible. (These would typically go into your [child] theme's `functions.php`.) Note: core hooks like `publish_{$post->post_type}` allow even more customization, but that's outside the scope of this readme.

```
// Force open links in new tab.
add_filter( 'import_bookmarks_post_content', function( $post_content, $bookmark ) {
    $post_title    = sanitize_text_field( $bookmark['title'] );
    $post_content  = sanitize_text_field( $bookmark['note'] );
    $post_content .= "\n\n<a href='" . esc_url( $bookmark['uri'] ) .
        "' target='_blank' rel='noopener noreferrer'>" . $post_title . '</a>';
    $post_content  = trim( $post_content );
    return $post_content;
}, 10, 2 );
```

```
// Do something else entirely.
add_filter( 'import_bookmarks_post_content', function( $post_content, $bookmark ) {
    $post_content  = sanitize_text_field( $bookmark['title'] );
    $post_content .= "\n\n" . make_clickable( esc_url( $bookmark['uri'] ) );
    $post_content  = trim( $post_content );
    return $post_content;
}, 10, 2 );
```

