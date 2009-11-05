<?php
/**
 * Extension File for NSM Multi Language
 *
 * @package Nsm_multi_language
 * @version 2.0.0
 * @since 1.0.0
 * @author Leevi Graham & Tony Arnold <http://newism.com.au>
 * @copyright Copyright (c) 2007-2009 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 * 
 **/

/**
 * NSM Multi Language Extension
 * @abstract NSM Multi Language extension for ExpressionEngine
 *
 * @package Nsm_multi_language
 * @version 2.0.0
 * @since 1.0.0
 * @author Leevi Graham & Tony Arnold <http://newism.com.au>
 * @copyright Copyright (c) 2007-2009 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 * 
 **/
class Nsm_multi_language_ext {
	/**
	 * Display name for this extension.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $addon_name = 'NSM Multi Language';
	
	/**
	 * Name for this extension.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $name = 'NSM Multi Language';
	
	/**
	 * Version number of this extension. Should be in the format "x.x.x", with only integers used.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $version = '2.0.0';
	
	/**
	 * Link to documentation for this extension.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $docs_url = 'http://newism.com.au/nsm-multi-language/';
	
	/**
	 * The XML auto-update URL for LG Auto Updater.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $versions_xml = 'http://newism.com.au/nsm-multi-language/versions.xml';

	/**
	 * Defines the ExpressionEngine hooks that this extension will intercept.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @var			mixed	an array of strings that name defined hooks
	 * @see			http://codeigniter.com/user_guide/general/hooks.html
	 **/
	private $hooks = array('sessions_start');

	/**
	 * Defines whether the extension has user-configurable settings.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $settings_exist = 'y';
	
	/**
	 * Defines the default settings for an initial installation of this extension.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @var			array an array of keys and values
	 **/
	private $default_settings = array(
		'default_language' => 'en-US',
		'check_for_updates' => TRUE,
	);

	
	/**
	 * PHP5 constructor function.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @param		array	$settings	an array of settings used to construct a new instance of this class.
	 * @return 		void
	 **/
	public function __construct($settings='')
	{
		$this->EE =& get_instance();

		// define a constant for the current site_id rather than calling $PREFS->ini() all the time
		if(defined('SITE_ID') == FALSE)
		{
			define('SITE_ID', $this->EE->config->item('site_id'));
		}

		$this->settings = ($settings == FALSE) ? $this->get_settings() : $this->save_settings_to_session($settings);
	}

	/**
	 * Called by ExpressionEngine when the user activates the extension.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return		void
	 **/
	public function activate_extension()
	{ 
		$this->create_hooks();
	}
	
	/**
	 * Called by ExpressionEngine when the user disables the extension.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return		void
	 **/
	public function disable_extension()
	{
		$this->delete_hooks();
	}
	
	/**
	 * Called by ExpressionEngine when the user updates to a newer version of the extension.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return		void
	 **/
	public function update_extension()
	{
		
	}

	/**
	 * Prepares and loads the settings form for display in the ExpressionEngine control panel.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return		void
	 **/
	public function settings_form()
	{

		$DB =& $this->EE->db;
		$this->EE->lang->loadfile('nsm_multi_language');
		$this->EE->load->library('form_validation');

		$vars['settings'] = $this->settings;
		$vars['message'] = FALSE;

		if($new_settings = $this->EE->input->post(__CLASS__))
		{
			$vars['settings'] = $new_settings;
			$this->save_settings_to_db($new_settings);
			$vars['message'] = $this->EE->lang->line('extension_settings_saved_success');
		}

		$vars['addon_name'] = $this->addon_name;
		$vars['languages'] = $this->get_languages_from_disk();

		return $this->EE->load->view('form_settings', $vars, TRUE);
	}

	/**
	 * Returns the settings from the session. If the settings are not currently in the session, they are loaded from the database.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @param		boolean	$refresh	if this is set to TRUE, the settings stored in the session will be cleared, and reloaded from the database. Defaults to TRUE.
	 * @return		array		current settings for this extension
	 **/
	private function get_settings($refresh = FALSE)
	{
		$settings = FALSE;
		if (isset($SESS->cache[$this->addon_name][__CLASS__]['settings']) === FALSE OR $refresh === TRUE)
		{
			$settings_query = $this->EE->db->query("SELECT `settings`
													FROM `exp_extensions`
													WHERE `enabled` = 'y'
													AND `class` = '".__CLASS__."'
													LIMIT 1"
												);
			if ($settings_query->num_rows())
			{
				$settings = unserialize($settings_query->row()->settings);
				$this->save_settings_to_session($settings);
			}
		}
		else
		{
			$settings = $this->EE->session->cache[$this->addon_name][__CLASS__]['settings'];
		}
		return $settings;
	}

	/**
	 * Saves the specified settings array to the database.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		protected
	 * @param		array	$settings	an array of settings to save to the database.
	 * @return		void
	 **/
	protected function save_settings_to_db($settings)
	{
		$DB =& $this->EE->db;
		$DB->query($DB->update_string('exp_extensions', array('settings' => serialize($settings)), array('class' => __CLASS__)));
	}

	/**
	 * Saves the specified settings array to the session.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		protected
	 * @param		array	$settings	an array of settings to save to the session.
	 * @return		array		the provided settings array
	 **/
	protected function save_settings_to_session($settings)
	{
		$this->EE->session->cache[$this->addon_name][__CLASS__]['settings'] = $settings;
		return $settings;
	}

	/*
    <addons>
        <addon id='NSM Multi Language' docs_url='http://newism.com.au/' download_url="">
			<version number="2.0.0" created_at="1218852797">
				<notes><![CDATA[
					<ul>
						<li>Initial update to support ExpressionEngine 2.0.</li>
					</ul>
				]]>
				</notes>
			</version>
		</addon>
    </addons>
	*/

	/**
	 * This function is called by ExpressionEngine whenever the "sessions_start" hook is executed. It checks the current hostname to see if the first segment matches one of the languages stored in the user's language directory. If it doesn't find a matching host domain segment, it checks the URL to see if the first segment matches one of the languages stored in the user's language directory. If either of the preceding conditions are true, the language, language display name and the user-defined path to the languages directory are all set as global variables. These variables are accessed by the Nsm_multi_language plugin.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @param		object	&$sess	an object reference to the current session that the hook was called from.
	 * @return		void
	 * @see 		http://codeigniter.com/user_guide/general/hooks.html
	 **/
	public function sessions_start(&$sess)
	{
		// Setup blank global variables
		$this->EE->input->_global_vars['nsm_lang'] = '';
		$this->EE->input->_global_vars['nsm_lang_title'] = '';
		$this->EE->input->_global_vars['nsm_lang_path'] = '';

		// Create an empty array for the language files
		$sess->cache['nsm']['multi_language']['languages'] = array();

		// Is this a page request?
		if (REQ == 'PAGE')
		{
			$requested_language_id = FALSE;
		
			$subdomain_language_file_exists = FALSE;
			$uri_used_first_component = TRUE;
			$uri_language_file_exists = FALSE;
			$default_language_file_exists = FALSE;

			$http_host = $this->EE->input->server('HTTP_HOST');
			$http_host_url_parts = parse_url($http_host);
			$current_subdomain_parts = explode(".", $http_host_url_parts['path']);
			$current_subdomain = $current_subdomain_parts[0];

			// Check if the file exists for the subdomain
			$file_path = $this->settings['languages_path'] . '/' . $current_subdomain . '.php';
			$subdomain_language_file_exists = file_exists($file_path);

			// If not, check for the URI component file
			if ($subdomain_language_file_exists !== TRUE)
			{
				$first_uri_component = $this->EE->uri->segment(1);
				$file_path = $this->settings['languages_path'] . '/' . $first_uri_component . '.php';
				$subdomain_language_file_exists = FALSE;
				$uri_language_file_exists = file_exists($file_path);
				$requested_language_id = $first_uri_component;
			
				// If the first URL component doesn't exist, try the last
				if ($uri_language_file_exists !== TRUE)
				{
					$uri_used_first_component = FALSE;
					$last_uri_component = $this->EE->uri->segment($this->EE->uri->total_segments());
					$file_path = $this->settings['languages_path'] . '/' . $first_uri_component . '.php';
					$subdomain_language_file_exists = FALSE;
					$uri_language_file_exists = file_exists($file_path);
					$requested_language_id = $first_uri_component;
				}
			}

			if ($uri_language_file_exists !== TRUE AND $subdomain_language_file_exists !== TRUE)
			{
				// We don't have a language file for either the URI, or the subdomain - short circuit and set to the user-defined default
				$default_language_id = $this->settings['default_language'];
				$file_path = $this->settings['languages_path'] . '/' . $default_language_id . '.php';
				$default_language_file_exists = file_exists($file_path);
			
				if ($default_language_file_exists !== TRUE)
				{
					// Everything failed - there is no default language file available - return the request unaffected.
					return;
				}
			
				$requested_language_id = $default_language_id;
			}

			if($subdomain_language_file_exists !== TRUE)
			{
				// The following code modifies the incoming URI that the user has requested to remove the first path segment
				//	(which we've already identified as matching one of our specified languages).
				$uri_segments = $this->EE->uri->segment_array();
				if ($uri_used_first_component === TRUE)
				{
					array_shift($uri_segments);
				}
				else
				{
					array_pop($uri_segments);
				}
				$this->EE->uri->segments = $uri_segments;

				$this->EE->router->_validate_request($uri_segments);

				// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
				if ($this->EE->uri->uri_string == '')
				{
					return $this->EE->router->_set_default_controller();
				}

				// Do we need to remove the URL suffix?
				$this->EE->uri->_remove_url_suffix();

				// Compile the segments into an array
				$this->EE->uri->_explode_segments();

				// Parse any custom routing that may exist
				$this->EE->router->_parse_routes();

				// Re-index the segment array so that it starts with 1 rather than 0
				$this->EE->uri->_reindex_segments();
			}

			// Load the language file from disk and set in the session
			include_once($file_path);
			$this->EE->config->_global_vars['nsm_lang'] = $requested_language_id;
			$this->EE->config->_global_vars['nsm_lang_title'] = isset($language_info['name']) ? $language_info['name'] : $requested_language_id;
			$this->EE->config->_global_vars['nsm_lang_path'] = $this->settings['languages_path'];
			$sess->cache['nsm']['multi_language']['languages'][$requested_language_id] = $LANG;
		}
	}

	/**
	 * Sets up and subscribes to the hooks specified by the $hooks array.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @param		array	$hooks	a flat array containing the names of any hooks that this extension subscribes to. By default, this parameter is set to FALSE.
	 * @return		void
	 * @see 		http://codeigniter.com/user_guide/general/hooks.html
	 **/
	private function create_hooks($hooks = FALSE){

		global $DB;

		if(!$hooks)
			$hooks = $this->hooks;

		$hook_template = array(
			'class'    => __CLASS__,
			'settings' => $this->default_settings,
			'version'  => $this->version,
		);

		// Setup our default path
		$hook_template['settings']['languages_path'] = APPPATH . 'language/nsm_multi_language';

		foreach($hooks as $key => $hook)
		{
			if(is_array($hook))
			{
				$data['hook'] = $key;
				$data['method'] = (isset($hook['method']) === TRUE) ? $hook['method'] : $key;
				$data = array_merge($data, $hook);
			}
			else
			{
				$data['hook'] = $data['method'] = $hook;
			}
			$hook = array_merge($hook_template, $data);
			$hook['settings'] = serialize($hook['settings']);
			$this->EE->db->query($this->EE->db->insert_string('exp_extensions', $hook));
		}
	}

	/**
	 * Removes all subscribed hooks for the current extension.
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @return		void
	 * @see 		http://codeigniter.com/user_guide/general/hooks.html
	 **/
	private function delete_hooks(){
		global $DB;
		$this->EE->db->query("DELETE FROM `exp_extensions` WHERE `class` = '".__CLASS__."'");
	}


	private function write_cache(){}
	private function get_cache(){}

	/**
	 * Retrieves available language files from disk
	 * @version		2.0.0
	 * @since		Version 2.0.0
	 * @access		private
	 * @return		array	keys and values describing the languages found in the user-defined languages directory
	 */
	private function get_languages_from_disk()
	{
		$loaded_languages = array();
		$lang_path = $this->settings['languages_path'];

		if ($dir_handle = opendir($lang_path))
		{
		    while (false !== ($path = readdir($dir_handle)))
			{
				if (is_dir($path) OR (substr($path,0,1) === '.')) continue;

				$lang_id = str_replace('.php', '', $path);

				@include_once($lang_path.'/'.$path);

				if (isset($language_info) && is_array($language_info))
				{
					$loaded_languages[$lang_id] = array(
						'id'			=> $lang_id,
						'name'			=> $language_info['name'],
						'version'		=> $language_info['version'],
						'author'		=> $language_info['author'],
						'author_url'	=> $language_info['author_url']
					);
				}
				else
				{
					log_message('error', "Invalid Plugin Data: {$path}");
				}

				unset($language_info);
		    }

		    closedir($dir_handle);
		} else {
			// Raise an error appropriately
			log_message('error', "Unable to open directory: {$lang_path}");

		}

		return $loaded_languages;
	}

	/**
	 * Get details on the language specified by $lang_id from the session if it's available, otherwise load from disk
	 * @version		2.0.0
	 * @since		Version 2.0.0
	 * @access		private
	 * @param		string	$lang_id	identifier for the language you would like to retrieve
	 * @return		array	keys and values describing the specified language
	 */
	private function get_language_details_from_disk($lang_id)
	{
		if (isset($loaded_languages) !== TRUE) return;

		$lang_path = $this->settings['languages_path'] . $lang_id . '.php';

		if (file_exists($lang_path) === TRUE)
		{
			// Read the info from the file
			@include_once($lang_path.'/'.$path);

			if (isset($language_info) && is_array($language_info))
			{
				$loaded_languages[$lang_id] = array(
					'id'			=> $lang_id,
					'name'			=> $language_info['name'],
					'version'		=> $language_info['version'],
					'author'		=> $language_info['author'],
					'author_url'	=> $language_info['author_url']
				);
			}
			else
			{
				log_message('error', "Invalid Plugin Data: {$path}");
			}

			unset($language_info);
		}


		if ($dir_handle = opendir($lang_path))
		{
		    while (false !== ($path = readdir($dir_handle)))
			{
				if (is_dir($path) OR (substr($path,0,1) === '.')) continue;

				$current_file_is_default_lang = FALSE;
				$lang_id = str_replace('.php', '', $path);

				@include_once($lang_path.'/'.$path);

				if (isset($language_info) && is_array($language_info))
				{
					$loaded_languages[$lang_id] = array(
						'id'			=> $lang_id,
						'name'			=> $language_info['name'],
						'version'		=> $language_info['version'],
						'author'		=> $language_info['author'],
						'author_url'	=> $language_info['author_url']
					);
				}
				else
				{
					log_message('error', "Invalid Plugin Data: {$path}");
				}

				unset($language_info);
		    }

		    closedir($dir_handle);
		} else {
			// Raise an error appropriately
			log_message('error', "Unable to open directory: {$lang_path}");

		}

		return $loaded_languages;
	}

} // END class Nsm_multi_language_ext