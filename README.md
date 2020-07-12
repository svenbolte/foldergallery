# Folder Gallery and Slider with documentlist

This plugin generates file listings, picture galleries and Sliders from a folder using three types of shortcodes.
The pictures folder must be uploaded (using FTP) somewhere on the server (e.g. wp-content/upload). It must be writable (chmod 777).
Folder Gallery Plugin does not include any lightbox JS engine anymore. You have to install one or use a compatible lightbox plugin. See FAQ.

To show a documents list for download pdf files use shortcode

	[folderdir folder="wp-content/uploads/bilder/" protect=1 sort=size_desc]

To show a photo slider from a gallery:

	[folderslider folder="wp-content/upload/MyPictures" width=500 mode=fade speed=2.5 captions=smartfilename controls=false]

To include a gallery in a post or a page, you have to use the following shortcode :

	[foldergallery folder="local_path_to_folder" title="Gallery title"]

For each gallery, a subfolder cache_[width]x[height] is created inside the pictures folder when the page is accessed for the first time. 
