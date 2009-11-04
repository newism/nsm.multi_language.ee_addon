<?php
/**
 * NSM Multi Language extension for ExpressionEngine
 *
 * @package NSM
 * @subpackage Multi Language
 * @author Leevi Graham <http://newism.com.au>
 * @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-multi-language/
 * @copyright Copyright (c) 2007-2009 Newism
 * @license http://leevigraham.com/cms-customisation/commercial-license-agreement
 **/
class Nsm_multilanguage_ext {
	public $addon_name = 'NSM Multi Language';
	public $name = 'NSM Multi Language';
	public $version = '2.0.0';
	public $docs_url = 'http://expressionengine-addons.com/nsm-multilanguage/';
	public $versions_xml = 'http://dev.expressionengine-addons.com/versions.xml';

	private $hooks = array('sessions_start');

	public $settings_exist = 'y';
	private $default_settings = array(
		'enabled' => TRUE,
		'default_language' => 'en-US',
		'check_for_updates' => TRUE,
	);

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

	public function activate_extension()
	{ 
		$this->create_hooks();
	}
	
	public function disable_extension()
	{
		$this->delete_hooks();
	}
	
	public function update_extension()
	{
		
	}

	public function settings_form()
	{

		$DB =& $this->EE->db;
		$this->EE->lang->loadfile('nsm_multilanguage');
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
		$vars['nsm_multilanguage_enabled'] = array_key_exists('Nsm_multilanguage_ext', $this->EE->extensions->version_numbers);

		return $this->EE->load->view('form_settings', $vars, TRUE);
	}

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

	protected function save_settings_to_db($settings)
	{
		$DB =& $this->EE->db;
		$DB->query($DB->update_string('exp_extensions', array('settings' => serialize($settings)), array('class' => __CLASS__)));
	}

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

	/* Checks the url to see if the last segment matches one of the languages defined in the extension settings.
	 */
	public function sessions_start(&$sess)
	{
		// Setup blank global variables
		$this->EE->input->_global_vars['nsm_lang'] = '';
		$this->EE->input->_global_vars['nsm_lang_title'] = '';
		$this->EE->input->_global_vars['nsm_lang_path'] = '';

		// Create an empty array for the language files
		$sess->cache['nsm']['multilanguage']['languages'] = array();

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
			$sess->cache['nsm']['multilanguage']['languages'][$requested_language_id] = $L;
		}
	}



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
		$hook_template['settings']['languages_path'] = APPPATH . 'language/nsm.languages';

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

	private function delete_hooks(){
		global $DB;
		$this->EE->db->query("DELETE FROM `exp_extensions` WHERE `class` = '".__CLASS__."'");
	}

	private function write_cache(){}
	private function get_cache(){}

	/**
	 * Get available language files from disk
	 *
	 *
	 * @access	private
	 * @return	mixed	array of language info
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
	 * Get details on the requested language file from disk
	 *
	 *
	 * @access	private
	 * @return	mixed	array of language info
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

} // END class Nsm_multilanguage_ext