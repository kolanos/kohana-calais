<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Calais Configuration
 */
return array
(
	// The Calais API URL
	'api_url' => 'http://api1.opencalais.com/enlighten/rest/',

	// Your Calais API Key, get one here: http://www.opencalais.com/user/register
	'api_key' => '',

	// Indicates whether the extracted metadata can be distributed by Calais
	'allow_distribution' => FALSE,

	// Indicates whether future searches can be performed on metadata through the Calais API
	'allow_search' => FALSE,

	// Allows you to set an ID for the content to pass on to Calais when it’s submitted for analysis
	'external_id' => '',

	// Allows you to set an identifier for the content submitter
	'submitter' => 'Open Calais Tags',

	// Allows you to specify the type of content you’re submitting. Can be text/xml, text/txt or text/html
	'content_type' => 'text/html',

	// Allows you to specify the format of the returned results (currently only supports xml/rdf)
	'output_format' => 'xml/rdf',

	// Determines if the keys of the return array will be prettified or in the raw format returned by Calais
	'pretty_types' => TRUE,
);
