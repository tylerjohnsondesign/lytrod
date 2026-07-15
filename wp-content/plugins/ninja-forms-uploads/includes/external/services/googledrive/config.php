<?php
return apply_filters( 'ninja_forms_uploads_settings_google_drive', array(
	'google_drive_connect'   => array(
		'id'               => 'google_drive_connect',
		'type'             => 'callback',
		'label'            => 'Connect to Google Drive',
		'desc'             => '',
		'display_function' => array( $this, 'connect_url' ),
	),
	'google_drive_file_path' => array(
		'id'      => 'google_drive_file_path',
		'type'    => 'textbox',
		'label'   => 'File Path',
		'desc'    => 'Custom directory for the files to be uploaded to in Google Drive',
		'default' => '',
	),
) );