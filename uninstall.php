<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

delete_option('FolderGallery');
// for rsstoposts
delete_option('rssnews_rss1');
delete_option('rssnews_direction1');
delete_option('rssnews_rss2');
delete_option('rssnews_direction2');
delete_option('rssnews_rss3');
delete_option('rssnews_direction3');
delete_option('rssnews_rss4');
delete_option('rssnews_direction4');
delete_option('rssnews_rss5');
delete_option('rssnews_direction5');
delete_option('pbrss-latestpostdate1');
delete_option('pbrss-latestpostdate2');
delete_option('pbrss-latestpostdate3');
delete_option('pbrss-latestpostdate4');
delete_option('pbrss-latestpostdate5');
// for site options in Multisite
delete_site_option('rssnews_rss1');
delete_site_option('rssnews_direction1');
delete_site_option('rssnews_rss2');
delete_site_option('rssnews_direction2');
delete_site_option('rssnews_rss3');
delete_site_option('rssnews_direction3');
delete_site_option('rssnews_rss4');
delete_site_option('rssnews_direction4');
delete_site_option('rssnews_rss5');
delete_site_option('rssnews_direction5');
delete_site_option('pbrss-latestpostdate1');
delete_site_option('pbrss-latestpostdate2');
delete_site_option('pbrss-latestpostdate3');
delete_site_option('pbrss-latestpostdate4');
delete_site_option('pbrss-latestpostdate5');

?>