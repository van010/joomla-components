<?php
/**
 * Microsoft Limited Public License
 * 
 * This license governs use of code marked as "sample" or "example" available on this web site without a license agreement,
 * as provided under the section above titled "NOTICE SPECIFIC TO SOFTWARE AVAILABLE ON THIS WEB SITE."
 * If you use such code (the "software"), you accept this license. If you do not accept the license, do not use the software.
 * 
 * Definitions
 * 
 * The terms "reproduce," "reproduction," "derivative works," and "distribution" have the same meaning here as under U.S. copyright law.
 * 
 * A "contribution" is the original software, or any additions or changes to the software.
 * 
 * A "contributor" is any person that distributes its contribution under this license.
 * 
 * "Licensed patents" are a contributor's patent claims that read directly on its contribution.
 * Grant of Rights
 * 	Copyright Grant - Subject to the terms of this license, including the license conditions and limitations in section 3,
 * 		each contributor grants you a non-exclusive, worldwide, royalty-free copyright license to reproduce its contribution,
 * 		prepare derivative works of its contribution, and distribute its contribution or any derivative works that you create.
 * 	Patent Grant - Subject to the terms of this license, including the license conditions and limitations in section 3,
 * 		each contributor grants you a non-exclusive, worldwide, royalty-free license under its licensed patents to make,
 * 		have made, use, sell, offer for sale, import, and/or otherwise dispose of its contribution in the software or derivative works of the contribution in the software.
 * Conditions and Limitations
 * 	No Trademark License- This license does not grant you rights to use any contributors' name, logo, or trademarks.
 * 	If you bring a patent claim against any contributor over patents that you claim are infringed by the software, your patent license from such contributor to the software ends automatically.
 * 	If you distribute any portion of the software, you must retain all copyright, patent, trademark, and attribution notices that are present in the software.
 * 	If you distribute any portion of the software in source code form, you may do so only under this license by including a complete copy of this license with your distribution.  If you distribute any portion of the software in compiled or object code form, you may only do so under a license that complies with this license.
 * 	The software is licensed "as-is." You bear the risk of using it. The contributors give no express warranties, guarantees or conditions.  You may have additional consumer rights under your local laws which this license cannot change. To the extent permitted under your local laws, the contributors exclude the implied warranties of merchantability, fitness for a particular purpose and non-infringement.
 * 	Platform Limitation - The licenses granted in sections 2(A) and 2(B) extend only to the software or derivative works that you create that run on a Microsoft Windows operating system product.
 * ------------------------------------------------------------------------
 * 
 * ------------------------------------------------------------------------
 * Copyright (C) All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author:
 * Websites: 
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

if (!function_exists('com_create_guid')) {
  function com_create_guid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}

class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $azure_key    Subscription key for Text Translation API.
     *
     * @return string.
     */
    function getToken($azure_key)
    {
        $url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
        $ch = curl_init();
        $data_string = json_encode('{body}');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
                'Ocp-Apim-Subscription-Key: ' . $azure_key
            )
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $strResponse = curl_exec($ch);
        curl_close($ch);
        return $strResponse;
    }
}

/*
 * Class:HTTPTranslator
 *
 * Processing the translator request.
 */
Class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */
    function curlRequest($url, $authHeader) {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
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
}
/*
try {
    //Client Secret key of the application.

    //Create the AccessTokenAuthentication object.
    $authObj      = new AccessTokenAuthentication();
    //Get the Access token.
    $accessToken  = $authObj->getToken($clientSecret);
    //Create the authorization Header string.
    $authHeader = "Authorization: Bearer ". $accessToken;

    //Set the params.//
    $fromLanguage = "en";
    $toLanguage   = "de";
    $inputStr     = 'html';
    $contentType  = 'text/plain';
    $category     = 'general';
    
    $params = "text=".urlencode($inputStr)."&to=".$toLanguage."&from=".$fromLanguage;
    $translateUrl = "https://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
    
    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();
    
    //Get the curlResponse.
    $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);
    
    //Interprets a string of XML into an object.
    $xmlObj = simplexml_load_string($curlResponse);
    foreach((array)$xmlObj[0] as $val){
        $translatedStr = $val;
    }
    echo "<table border=2px>";
    echo "<tr>";
    echo "<td><b>From $fromLanguage</b></td><td><b>To $toLanguage</b></td>";
    echo "</tr>";
    echo "<tr><td>".$inputStr."</td><td>".$translatedStr."</td></tr>";
    echo "</table>";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}
*/

/*
 * Create and execute the HTTP CURL request.
 * 
 * @param string $url        HTTP Url.
 * @param string $authHeader Authorization Header string.
 * @param string $postData   Data to post.
 *
 * @return string.
 *
 */
function curlRequest($url, $authHeader, $postData=''){
    //Initialize the Curl Session.
    $ch = curl_init();
    //Set the Curl url.
    curl_setopt ($ch, CURLOPT_URL, $url);
    //Set the HTTP HEADER Fields.
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
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
