# Folder Gallery Slider and Folderdir

This plugin generates file listings, picture galleries and Sliders from a folder using three types of shortcodes.
The pictures folder must be uploaded (using FTP) somewhere on the server (e.g. wp-content/upload). It must be writable (chmod 777).
Folder Gallery Slider Plugin does not include any lightbox JS engine anymore. You have to install one or use a compatible lightbox plugin. See FAQ.
Now descriptions can be uploaded in a text file with filename.jpg,descritiontext. Once found in the folder desriptions will be displayed in dirlist an in gallery.
A sorted file "descriptions-vorlage.txt" with all current filenames will be created automatically and can be used as template.
For details and changelog see readme.txt

To show a documents list for download pdf files use shortcode

	[folderdir folder="wp-content/uploads/pdf/" protect=1 sort=size_desc]

To show a photo slider from a gallery:

	[folderslider folder="wp-content/uploads/bilder" width=500 mode=fade speed=2.5 captions=smartfilename controls=false]

To include a gallery in a post or a page, you have to use the following shortcode :

	[foldergallery folder="wp-content/uploads/bilder" title="Meine Galerie" columns=1 width=150 height=90 border=1 padding=2 margin=10 thumbnails=single]

For each gallery, a subfolder cache_[width]x[height] is created inside the pictures folder when the page is accessed for the first time. 
