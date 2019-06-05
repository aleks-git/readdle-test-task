<?php

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';

$config = [

	/* Url for image serving in final download */
    IMAGE_URL => $protocol.$_SERVER['HTTP_HOST']."/",

	/* Base Url for accessing Mosaco */
	BASE_URL => $protocol.$_SERVER['HTTP_HOST']."/",
	
	/* local file system base path to where image directories are located */
	BASE_DIR => $_SERVER['DOCUMENT_ROOT']."/",
	
	/* url to the uploads folder (relative to BASE_URL) */
	UPLOADS_URL => "uploads/",
	
	/* local file system path to the uploads folder (relative to BASE_DIR) */
	UPLOADS_DIR => "uploads/",
	
	/* url to the static images folder (relative to SERVE_URL) */
	//STATIC_URL => "media/newsletter/static/",
	STATIC_URL => "uploads/static/",

	/* local file system path to the static images folder (relative to BASE_DIR) */
	STATIC_DIR => "uploads/static/",
	
	/* url to the thumbnail images folder (relative to BASE_URL */
	THUMBNAILS_URL => "uploads/thumbnail/",
	
	/* local file system path to the thumbnail images folder (relative to BASE_DIR) */
	THUMBNAILS_DIR => "uploads/thumbnail/",
	
	/* width and height of generated thumbnails */
	THUMBNAIL_WIDTH => 90,
	THUMBNAIL_HEIGHT => 90
];
