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

defined('_JEXEC') or die;


require_once(dirname(__FILE__) . '/bing/microsoft.php');

class JalangHelperTranslatorBing extends JalangHelperTranslator
{
	/**
	 * constructor
	 *
	 * @param string $from - translate from
	 * @param string $to - translate to
	 * 
	 * @desc Full of Translator Language Codes can be found here
	 * http://msdn.microsoft.com/en-us/library/hh456380.aspx
	 */
	public function __construct($parent, $db, $options = array()) {
		parent::__construct();
	}

    public function _translate ($host, $path, $key, $params, $content) {
        $location = $this->params->get('azure_location', '');
        $headers = [
            'Content-type: application/json',
            "Content-length: " . strlen($content),
            "Ocp-Apim-Subscription-Key: ".$key,
            'Ocp-Apim-Subscription-Region:' . trim($location),
            "X-ClientTraceId: " . com_create_guid(),
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        ];

        $options = [
            CURLOPT_URL             => $host . $path . $params,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $content,
            CURLOPT_HTTPHEADER      => $headers,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $results = curl_exec($curl);
        // $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $results;
    }

	public function translateMethod($sentence) {
		try
		{
            // Replace the subscriptionKey string value with your valid subscription key.
            $key = $this->params->get('bing_client_id', '');

            $host = "https://api.cognitive.microsofttranslator.com";
            $path = "/translate?api-version=3.0";

            // Translate to German and Italian.
            $params = "&to=".$this->to;
            $params .= "&from=".$this->from;
            $params .= "&textType=html";
            $text = $sentence;

            $requestBody = array (
                array (
                    'Text' => $text,
                ),
            );
            $content = json_encode($requestBody);
            $result = $this->_translate($host, $path, $key, $params, $content);
            $translate = json_decode($result);
            if (!empty($translate->error)) {
	            Factory::getApplication()->enqueueMessage($translate->error->message, 'error');
                return false;
            }
            return $translate[0]->translations[0]->text;
		} catch (Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

	}

	public function translate($sentence) {
		try
		{
			$translatedStr = $this->translateMethod($sentence);
			return $translatedStr;
		} catch (Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}
	}
	
	public function translateArray($sentences, $fields) {
		$inputStrArr = $sentences;
		try
		{
			foreach ($inputStrArr AS $inputStr) {
				if (trim($inputStr) == '') {
					$translatedStr[] = $inputStr;
					continue;
				}
				$transresult = $this->translateMethod($inputStr);
				if ($transresult != false)
					$translatedStr[] = $transresult;
				else
					return $transresult;
			}
			return $translatedStr;
		} catch (Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}
	}
}