<?php
return apply_filters( 'ninja_forms_uploads_settings_dropbox', array(
	'dropbox_connect'              => array(
		'id'               => 'dropbox_connect',
		'type'             => 'callback',
		'label'            => 'Connect to Dropbox',
		'desc'             => '',
		'display_function' => array( $this, 'connect_url' )
	),
	'dropbox_file_path'            => array(
		'id'      => 'dropbox_file_path',
		'type'    => 'textbox',
		'label'   => 'File Path',
		'desc'    => 'Custom directory for the files to be uploaded to in your Dropbox /Apps/Ninja Forms Uploads/ folder',
		'default' => '',
	)
) );