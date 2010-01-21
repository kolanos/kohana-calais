<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Calais v0.1
 *
 * An adaptation of the OpenCalais library
 */

class Calais
{
	// Calais configuration
	protected $config;
	 
	// The entities returned by OpenCalais
	protected $entities;

	/**
	 * Create an instance of Calais.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		return new Calais($config);
	}

	/**
	 * Return a static instance of Calais.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		static $instance;

		// Load the Tags instance
		empty($instance) and $instance = new Calais($config);

		return $instance;
	}

	/**
	 * Loads configuration options.
	 *
	 * @return  void
	 */
	public function __construct($config = array())
	{
		// Append default calais configuration
		$config += Kohana::config('calais');

		// Save the config in the object
		$this->config = $config;

		// Can't continue without an API key
		if (empty($this->config['api_key'])) 
		{
			throw new Kohana_Exception("You must provide an OpenCalais API key to use this library.");
		}

		Kohana::log('debug', 'Calais Library loaded');
	}
	
	/**
	 * Get Entities
	 *
	 * @param	string	content	The content to be analyzed by OpenCalais
	 * @return	array	The entities array
	 */
	public function get_entities($content = '')
	{
		if (empty($content))
		{
			return FALSE;
		}
	
		$response = $this->_call_api($content);
		
		$xml = substr($response, strpos($response, 'c:document'));
		$matches = preg_match_all('#' . preg_quote('<!--', '#') . '(.*?)' . preg_quote('-->', '#') . '#ms', $xml, $rdf, PREG_SET_ORDER);

		foreach ($rdf as $key => $val) 
		{
			if (strpos($val[1], ": ") !== FALSE)
			{
				$parts = split(": ", $val[1]);
				$this->_add_entity($parts[0], $parts[1]);
			}
		}
		
		return $this->entities;
	}
	
	/**
	 * Add Entity
	 *
	 * @param	string	key The key of the parsed entities, usually the entity type
	 * @param	string	val The value of the parsed entities, usually entity
	 * @return	void
	 */
	private function _add_entity($key, $val)
	{
		$entity_types = array('Anniversary' => 'Anniversary',
							 'City' => 'City',
							 'Company' => 'Company',
							 'Continent' => 'Continent',
							 'Country' => 'Country',
							 'Currency' => 'Currency',
							 'Date' => 'Date',
							 'EmailAddress' => 'Email Address',
							 'EntertainmentAwardEvent' => 'EntertainmentAwardEvent',
							 'Facility' => 'Facility',
							 'FaxNumber' => 'Fax Number',
							 'Holiday' => 'Holiday',
							 'IndustryTerm' => 'Industry Term',
							 'MarketIndex' => 'Market Index',
							 'MedicalCondition' => 'Medical Condition',
							 'MedicalTreatment' => 'Medical Treatment',
							 'Movie' => 'Movie',
							 'MusicAlbum' => 'Music Album',
							 'MusicGroup' => 'Music Group',
							 'NaturalDisaster' => 'Natural Disaster',
							 'NaturalFeature' => 'Natural Feature',
							 'OperatingSystem' => 'Operating System',
							 'Organization' => 'Organization',
							 'Person' => 'Person',
							 'PhoneNumber' => 'Phone Number',
							 'Position' => 'Position',
							 'Product' => 'Product',
							 'ProgrammingLanguage' => 'Programming Language',
							 'ProvinceOrState' => 'Province or State',
							 'PublishedMedium' => 'Published Medium',
							 'RadioProgram' => 'Radio Program',
							 'RadioStation' => 'Radio Station',
							 'Region' => 'Region',
							 'SportsEvent' => 'Sports Event',
							 'SportsGame' => 'Sports Game',
							 'SportsLeague' => 'Sports League',
							 'Technology' => 'Technology',
							 'Time' => 'Time',
							 'TVShow' => 'TV Show',
							 'TVStation' => 'TV Station',
							 'URL' => 'URL');
							 
		$key = trim($key);
		$val = rtrim(trim($val),";");
				
		if ( ! array_key_exists($key, $entity_types)) 
		{
			return;
		} 
		else 
		{
			if ($this->config['pretty_types']) 
			{
				$key = $entity_types[$key];
			}
		}
	
		if (isset($this->entities[$key])) 
		{	
			if ( ! in_array($val, $this->entities[$key])) 
			{
				$this->entities[$key][] = $val;
			}	
		}
		else 
		{
			$this->entities[$key][] = $val;		
		}
	}
	
	/**
	 * Call OpenCalais API
	 *
	 * @param	string	content The content to be analyzed by OpenCalais
	 * @return	string	Response data from OpenCalais
	 */
	private function _call_api($content = '', $title = NULL) 
	{
		$post_data['licenseID'] = $this->config['api_key'];
	
		$post_data['paramsXML'] = 
			  '<c:params xmlns:c="http://s.opencalais.com/1/pred/"'
			. ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'
			. '	<c:processingDirectives c:contentType="' . $this->config['content_type']
			. '" c:outputFormat="' . $this->outputFormat . '"></c:processingDirectives>'
			. '	<c:userDirectives c:allowDistribution="' . $this->config['allow_distribution'] 
			. '" c:allowSearch="' . $this->config['allow_search'] . '" c:externalID="' . $this->config['external_id'] 
			. '" c:submitter="' . $this->config['submitter'] . '"></c:userDirectives>'
			. '	<c:externalMetadata></c:externalMetadata>'
			. '</c:params>';
		
		if ( ! empty($content)) 
		{
			$post_data['content'] = $content;
		} 
		else 
		{
			throw new Kohana_Exception("Content to analyze is empty.");
		}
		
		$post_string = $this->urlencode_array($post_data);
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config['api_url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_POST, 1);
		$response = html_entity_decode(curl_exec($ch));
		
		if (strpos($response, "<Exception>") !== FALSE) 
		{
			$text = preg_match("/\<Exception\>(.*)\<\/Exception\>/mu", $response, $matches);
			throw new Kohana_Exception($matches[1]);
		}
		
		return $response;
	}
	
	/**
	 * URL Encode Array
	 *
	 * @param	array	array Array to be URL encoded
	 * @return	array	URL encoded array
	 */
	private function _urlencode_array($array)
	{
		foreach ($array as $key => $val) 
		{
			if ( ! isset($string)) 
			{
				$string = $key . "=" . urlencode($val);
			} 
			else 
			{
				$string .= "&" . $key . "=" . urlencode($val);
			}
		}
		
		return $string;
	}

} // End Calais Library

?>
