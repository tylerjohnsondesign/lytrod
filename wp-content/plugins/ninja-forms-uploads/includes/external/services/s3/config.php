<?php

return apply_filters( 'ninja_forms_uploads_settings_s3', array(
	'amazon_s3_access_key' => array(
		'id'      => 'amazon_s3_access_key',
		'type'    => 'textbox',
		'default' => '',
		'label'   => 'Access Key',
	),
	'amazon_s3_secret_key' => array(
		'id'      => 'amazon_s3_secret_key',
		'type'    => 'textbox',
		'default' => '',
		'label'   => 'Secret Key',
	),
	'amazon_s3_bucket_name' => array(
		'id'      => 'amazon_s3_bucket_name',
		'type'    => 'textbox',
		'default' => '',
		'label'   => 'Bucket Name',
	),
	'amazon_s3_file_path' => array(
		'id'      => 'amazon_s3_file_path',
		'type'    => 'textbox',
		'default' => 'ninja-forms/',
		'label'   => 'File Path',
		'desc'  => 'The default file path in the bucket where the file will be uploaded to.'
	),
) );