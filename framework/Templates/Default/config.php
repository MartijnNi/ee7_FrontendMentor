<?php

//-----------------------------------------------------------------------------
// 	# Site Configuration
//-----------------------------------------------------------------------------
//
//	This file contains the configuration for Expression Engine.
// 	You can define your own configuration in the environment
//	configuration settings section for local, staging and production
//

$protocol							= ($_SERVER['HTTPS'] ?? '') === 'on' ? 'https://' : 'http://';

$domain								= $_SERVER['HTTP_HOST'] ?? 'localhost';

$current_url						= $protocol . $domain . '/';

$current_path						= $_SERVER['DOCUMENT_ROOT'] . '/';

global $assign_to_config;

$assign_to_config['global_vars'] 	= $assign_to_config['global_vars'] ?? [];

//-----------------------------------------------------------------------------
// ## Define the environment
//-----------------------------------------------------------------------------
//
// 	Define the domains for each environment by substrings.
//	If no match is found the default is assumed to be production
// 

$environment_domains = [
	
	'local' 	=> 		['localhost', '127.0.0.1', '.dev', '.test', '.local', '192.168.', '.ddev.site'],

	'staging'   => 		['staging.', '.portalserver.nl', '.duniqueserver.nl'],

];

$env = array_reduce(array_keys($environment_domains), 
	fn($carry, $key) => (!$carry && array_filter($environment_domains[$key], 
	fn($value) => strpos($domain, $value) !== false)) ? $key : $carry);

$env = $env ?? 'production';
if (isset($config)) {
	// ----------------------------------------------------------------
	// Environment configuration settings
	// ----------------------------------------------------------------

	switch ($env) {
		case 'local':
			// Local specific settings
			$local_config = [
				'gzip_output' 				=> 'n',
				'cookie_secure' 			=> 	'n',
				'password_lockout_interval'	=> 	'0',
				'max_page_loads' 			=> 	'300',
				'time_interval' 			=> 	'0',
				'debug'								=> '2',
				'show_profiler'				=> 'y',
			];
			
			// Local database credentials 
			$local_config['database']['expressionengine'] = [
				'hostname' => $config['database']['expressionengine']['hostname'],
				'database' => $config['database']['expressionengine']['database'],
				'username' => $config['database']['expressionengine']['username'],
				'password' => $config['database']['expressionengine']['password'],
			];
			break;
		case 'staging':
			// Staging specific settings
			$staging_config = [
				'gzip_output' 				=> 'n',
				'password_lockout_interval'	=> '0',
				'max_page_loads' 			=> '300',
				'time_interval' 			=> '0',
			];
			// Staging database credentials
			$staging_config['database']['expressionengine'] = [
				'hostname' => '',
				'database' => '',
				'username' => '',
				'password' => '',
			];
			break;
		default:
			// Define production settings
			$production_config = [];
			// Production database credentials
			$production_config['database']['expressionengine'] = [
				'hostname' => '',
				'database' => '',
				'username' => '',
				'password' => '',
			];
			break;
	}
	// ----------------------------------------------------------------
	// Define the base configuration settings
	// ----------------------------------------------------------------
	$base_config = [
		// ----------------------------------------------------------------
		// GENERAL SETTINGS
		// ----------------------------------------------------------------
		'new_version_check'				=> 	'n',
		'show_ee_news'						=> 	'n',
		'default_site_timezone' 	=> 	"Europe/Amsterdam",
		'date_format'           	=> 	'%j-%n-%Y',
		'time_format'           	=> 	'24',
		'include_seconds'					=> 	'y',
		// ----------------------------------------------------------------
		// URL AND PATH SETTIGNS
		// ----------------------------------------------------------------
		'base_url'							=> 	$current_url,
		'base_path'							=> 	$current_path,
		'site_index'						=> 	'',
		'site_url'							=> 	'{base_url}',
		'cp_url'								=> 	'{base_url}cms.php',
		'theme_folder_url'			=> 	'{base_url}themes/',
		'theme_folder_path'			=> 	'{base_path}themes/',
		'use_category_name'			=> 	'y',
		'word_separator'				=> 	'dash',
		// ----------------------------------------------------------------
		// OUTGOING EMAIL
		// ----------------------------------------------------------------
		'webmaster_name'			=> 'Webmaster ' . $domain,
		'email_charset'				=> 'utf-8',
		'email_newline'				=> '\n',
		'mail_format'					=> 'html',
		// ----------------------------------------------------------------
		// DEBUGGING AND OUTPUT
		// ----------------------------------------------------------------
		'debug'									=> '0',
		'show_profiler'					=> 'n',
		'gzip_output'						=> 'y',
		'force_query_string'		=> 'n',
		'redirect_method'				=> 'redirect',
		'cache_driver'					=> 'file',
		'cache_driver_backup'		=> 'file',
		// ----------------------------------------------------------------
		// Content & Design Settings
		// ----------------------------------------------------------------
		'new_posts_clear_caches'	=> 	'y',
		'enable_sql_caching'			=> 	'n',
		'enable_entry_cloning'		=> 	'y',
		'image_resize_protocol'		=> 	'gd2',
		'enable_emoticons'				=> 	'y',
		'emoticon_url'						=> '{base_url}images/smileys/',
		// ----------------------------------------------------------------
		// TEMPLATE SETTIGNS
		// ----------------------------------------------------------------
		'strict_urls'						=> 	'y',
		'template_group'				=> 	'_site',
		'template'							=> 	'home',
		'save_tmpl_revisions'		=> 	'y',
		'max_tmpl_revisions'		=> 	'15',
		'save_tmpl_files'				=> 	'y',
		'site_404'							=> 	'_site/404',
		// ----------------------------------------------------------------
		// SECURITY & PRIVACY SETTIGNS
		// ----------------------------------------------------------------
		'cp_session_type'								=> 	'c',
		'website_session_type' 					=> 	'c',
		'cookie_secure'									=> 	'y',
		'password_lockout_interval'			=> 	'5',
		'max_page_loads'								=> 	'30',
		'time_interval'									=> 	'10',
		// ----------------------------------------------------------------
		// FILES UPLOAD PREFERENCES
		// ----------------------------------------------------------------
		'upload_preferences' => [
				5 => [
						'name'                  => 'Images',
						'server_path'           => '{base_path}assets/uploads/images/',
						'url'                   => '{base_url}assets/uploads/images/',
						'allowed_types'         => 'img',
						'default_modal_view'    => 'thumb',
						'max_size'              => '256000',
						'max_width'             => '5000',
						'max_height'            => '5000',
				],
				6 => [
						'name'                  => 'Files',
						'server_path'           => '{base_path}assets/uploads/files/',
						'url'                   => '{base_url}assets/uploads/files/',
						'allowed_types'         => 'all',
						'default_modal_view'    => 'list',
						'max_size'              => '256000',
						'max_width'             => '5000',
						'max_height'            => '5000',
				]
		]
	];
	// DEBUGGING & OUTPUT
	
	$config = array_merge($config, $base_config, ${$env.'_config'});
	
	// Disable debug logging on ajax requests
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		$config['show_profiler'] = 'n';
		$config['template_debugging'] = 'n';
	}

	// ----------------------------------------------------------------
	// Set global variables
	// ----------------------------------------------------------------
	
	preg_match("/[^\.\/]+\.[^\.\/]+$/", $domain, $hostname);

	$global_variables = [
		// URL
		'base_url'										=> 	'{site_url}',
		'resource_url'    						=> 	'{base_url}',
		'assets_url'	                =>  '{base_url}assets/',
		'themes_url'									=> 	$config['theme_folder_url'] ?? '',
		// Environment
		'global:env'    							=> 	$env,
		'global:hostname' 						=> 	$hostname[0]??'localhost',
		// Parameters
		'global:param_disable_all'      	=> 	'disable="categories|custom_fields|member_data|pagination"',
		'global:param_disable_overview' 	=> 	'disable="member_data"',
		'global:param_disable_page' 	    => 	'disable="pagination|member_data"',
		'global:param_cache'  						=> 	'cache="yes" refresh="30"',
		// Date and time format
		'global:date_time'								=> 	'%H:%i',
		'global:date_short'         			=> 	'%d-%m-%Y',
		'global:date_full'          			=> 	'%d-%m-%Y %H:%i'
	];
	
	$assign_to_config['global_vars'] = array_merge($assign_to_config['global_vars'], $global_variables);

}
// EOF
