<?php
/**
 * ------------------------------------------------------------------------
 * JA Multilingual J2x-J3x.
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class JalangHelperTranslatorGoogle extends JalangHelperTranslator
{
    // this is the API endpoint, as specified by Google
    const ENDPOINT = 'https://www.googleapis.com/language/translate/v2';

    // holder for you API key, specified when an instance is created
    protected $_apiKey;

    // constructor, accepts Google API key as its only argument
    public function __construct($parent, $db, $options = array()) {
        //$this->contentType = 'text/html';//text/plain
        parent::__construct();
        $this->_apiKey = $this->params->get('google_browser_api_key', '');
    }

    // translate the text/html in $data. Translates to the language
    // in $target. Can optionally specify the source language
    public function translate($data)
    {
        // this is the form data to be included with the request
        $values = array(
            'key'    => $this->_apiKey,
            'target' => $this->to,
            'source' => $this->from,
            'q'      => $data
        );

        // turn the form data array into raw format so it can be used with cURL
        $formData = http_build_query($values);
        $formData = preg_replace('/%5B[0-9]+%5D/', '', $formData);

        $json = $this->curlRequest(self::ENDPOINT, $formData);

        // decode the response data
        $data = json_decode($json, true);

        // ensure the returned data is valid
        if (!is_array($data) || !array_key_exists('data', $data)) {
            throw new Exception($data['error']['message']);
        }

        // ensure the returned data is valid
        if (!array_key_exists('translations', $data['data'])) {
            throw new Exception('Unable to find translations key');
        }

        if (!is_array($data['data']['translations'])) {
            throw new Exception('Expected array for translations');
        }

        // loop over the translations and return the first one.
        // if you wanted to handle multiple translations in a single call
        // you would need to modify how this returns data
        $return = array();
        foreach ($data['data']['translations'] as $translation) {
            $return[] = $translation['translatedText'];
        }
        if(!empty($return)){
            return $return;
        }
        // assume failure since success would've returned just above
        throw new Exception('Translation failed');
    }

    function curlRequest($url, $postData=''){
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        if($postData) {
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }

    public function translateArray($sentences, $fields) {
        if(!is_array($sentences)){
            $sentences = array($sentences);
        }

		if(! $this->_apiKey){
			jexit('<span class="failed">' . Text::_('ALERT_COMPONENT_SETTING') . '</span>');
		}
        try
        {
            $data = $this->translate($sentences);
            return $data;
        } catch (Exception $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }
}
?>