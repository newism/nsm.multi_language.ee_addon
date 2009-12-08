<?php
/**
 * Plugin File for NSM Multi Language
 *
 * @package Nsm_multi_language
 * @version 2.0.0
 * @author Leevi Graham <http://newism.com.au>
 * @copyright Copyright (c) 2007-2009 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 * 
 **/

/**
 * Plugin information used by ExpressionEngine
 * @global array information about this plugin
 */
$plugin_info = array(
	'pi_name'			=> 'NSM Multi Language',
	'pi_version'		=> '2.0.0',
	'pi_author'			=> 'Leevi Graham',
	'pi_author_url'		=> 'http://newism.com.au/',
	'pi_description'	=> 'Translates phrases to the chosen language',
	'pi_usage'			=> Nsm_multi_language::usage()
);

/**
 * This ExpressionEngine plugin translates phrases to the chosen language. It requires the NSM Multi Language extension to be installed and enabled to function.
 *
 * @package Nsm_multi_language
 * @version 2.0.0
 * @since 1.0.0
 * @author Leevi Graham <http://newism.com.au>
 * @copyright Copyright (c) 2007-2009 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 * 
 **/
class Nsm_multi_language
{
	/**
	 * Display name for this extension.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	private $addon_name = 'NSM Multi Language';

	/**
	 * Version number of this plugin. Should be in the format "x.x.x", with only integers used.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @var			string
	 **/
	public $version = "2.0.0a1";

	/**
	 * Defines the default settings for an initial installation of this plugin.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @var			array an array of keys and values
	 **/
	public $settings = array();

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
	}

	/**
	 * Translates keys in the content being processed by this plugin.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return 		string	the translated string content
	 **/
	public function translate()
	{
		// load the settings from the cache
		$this->settings =& $this->EE->session->cache[$this->addon_name]['Nsm_multi_language_ext']['settings'];

		// get the translation key
		$translation_key = $this->EE->TMPL->fetch_param('key');

		// figure out the requested language for this transaltion
		$tag_requested_language = $this->EE->TMPL->fetch_param('language');
		$ext_requested_language = $this->EE->config->_global_vars['nsm_lang'];
		$requested_language_id = ($tag_requested_language !== FALSE) ? $tag_requested_language : $ext_requested_language;

		// if there is a requested language 
		if ($requested_language_id !== FALSE)
		{
			// do we have a language dictionary stored in the settings?
			$requested_language_dictionary = isset($this->settings['languages'][$requested_language_id]) ? $this->settings['languages'][$requested_language_id] : FALSE;

			// no?
			if ($requested_language_dictionary === FALSE)
			{
				// Load the dictionary from disk
				if ($this->settings['languages_path'] !== FALSE)
				{
					$requested_language_file_path = $this->settings['languages_path'] . $requested_language_id . '.php';

					if (file_exists($requested_language_file_path) !== FALSE)
					{
						include_once($requested_language_file_path);
						if (isset($LANG))
						{
							$this->settings['languages'][$requested_language_id] = $LANG;
							$requested_language_dictionary = $this->settings['languages'][$requested_language_id];
							unset($language_info);
							unset($LANG);
						}
					}
				}
			}

			// and there is a language file with the translation
			if ($requested_language_dictionary != FALSE && isset($requested_language_dictionary[$translation_key]))
			{
				// return the translation
				return $requested_language_dictionary[$translation_key];
			}
		}

		// grab the default translation key
		$default_translation_value = $this->EE->TMPL->fetch_param('default');

		// No language file was found, so we'll return the "default" value if it's available,
		// otherwise we'll just return the key that the user used in the tag.
		return ($default_translation_value !== FALSE) ? $default_translation_value : $translation_key;
	}

	/**
	 * Usage documentation for this plugin.
	 * @version		2.0.0
	 * @since		Version 1.0.0
	 * @access		public
	 * @return 		string	usage documentation for this plugin
	 **/
	public function usage()
	{
		return "For usage please see the documentation attached to the original download for this plugin.";
	}
	// END
}