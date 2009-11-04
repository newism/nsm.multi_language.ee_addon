<?php
/**
* Plugin File for NSM Multi Language
*
* This file must be placed in the
* /system/expressionengine/plugins/ folder in your ExpressionEngine 2 installation.
*
* @package NsmMultiLanguage
* @version 2.0.0
* @author Leevi Graham <http://newism.com.au>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-better-meta/
* @copyright Copyright (c) 2007-2009 Leevi Graham
* @license http://leevigraham.com/cms-customisation/commercial-license-agreement
*/

/**
* Plugin information used by ExpressionEngine
* @global array $plugin_info
*/
$plugin_info = array(
	'pi_name'			=> 'NSM Multi Language',
	'pi_version'		=> '2.0.0',
	'pi_author'			=> 'Leevi Graham',
	'pi_author_url'		=> 'http://leevigraham.com/',
	'pi_description'	=> 'Translates phrases to the chosen language',
	'pi_usage'			=> Nsm_multilanguage::usage()
);

/**
* Translates phrases to the chosen language
*
* @package NsmMultiLanguage
* @version 2.0.0
* @author Leevi Graham <http://newism.com.au>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-better-meta/
* @copyright Copyright (c) 2007-2009 Leevi Graham
* @license http://leevigraham.com/cms-customisation/commercial-license-agreement
*/
class Nsm_multilanguage
{

	/**
	* Returned string
	* @var array
	*/
	public $return_data = "";

	/**
	* Plugin version
	* @var array
	*/
	public $version = "2.0a1";

	/**
	* settings
	* @var array
	*/
	public $settings = array();

	/**
	* PHP 5 Constructor
	*/
	public function __construct($settings='')
	{
		$this->EE =& get_instance();
	}

	public function translate()
	{
		$translation_key = $this->EE->TMPL->fetch_param('key');
		$languages_cache = isset($this->EE->session->cache['nsm']['multilanguage']['languages']) ? $this->EE->session->cache['nsm']['multilanguage']['languages'] : FALSE;
		$tag_requested_language = $this->EE->TMPL->fetch_param('language');
		$ext_requested_language = isset($this->EE->config->_global_vars['nsm_lang']) ? $this->EE->config->_global_vars['nsm_lang'] : FALSE;
		$requested_language_id = ($tag_requested_language !== FALSE) ? $tag_requested_language : $ext_requested_language;

		if ($requested_language_id !== FALSE AND $languages_cache !== FALSE)
		{
			$requested_language_dictionary = isset($languages_cache[$requested_language_id]) ? $languages_cache[$requested_language_id] : FALSE;

			if ($requested_language_dictionary === FALSE)
			{
				// Load the dictionary from disk
				// Load the translation from disk
				$language_path = isset($this->EE->config->_global_vars['nsm_lang_path']) ? $this->EE->config->_global_vars['nsm_lang_path'] : FALSE;


				if ($language_path !== FALSE)
				{
					$requested_language_file_path = $language_path . '/' . $requested_language_id . '.php';

					if (file_exists($requested_language_file_path) !== FALSE)
					{
						include_once($requested_language_file_path);
						if (isset($L))
						{
							$languages_cache[$requested_language_id] = $L;
							$requested_language_dictionary = $languages_cache[$requested_language_id];
							unset($language_info);
							unset($L);
						}
					}
				}
			}

			// and there is a language file with the translation
			if (isset($requested_language_dictionary[$translation_key]))
			{
				// return the translation
				return $requested_language_dictionary[$translation_key];
			}
		}

		// no lang file
		// return the phrase un translated
		$default_translation_value = $this->EE->TMPL->fetch_param('default');

		return ($default_translation_value !== FALSE) ? $default_translation_value : $translation_key;
	}

	/**
	 * Plugin usage documentation
	 *
	 * @return	string Plugin usage instructions
	 */
	public function usage()
	{
		return "For usage visit: http://leevigraham.com/cms-customisation/expressionengine/addon/lg-multi-language/";
	}
	// END
}

?>