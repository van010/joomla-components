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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

class JalangControllerTool extends BaseController
{
    public $option = null;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Guess the option as com_NameOfController
		if (empty($this->option))
		{
			$this->option = 'com_' . strtolower($this->getName());
		}

		$this->registerTask('translate_all', 'translateAll');
		$this->registerTask('move_all', 'moveAll');
		$this->registerTask('remove_language', 'removeLanguage');
	}

	/**
	 * Finds new Languages.
	 *
	 * @return  void
	 *
	 * @since   2.5.7
	 */
	public function find()
	{
		require_once(JPATH_ADMINISTRATOR.'/components/com_installer/models/update.php');
		// Purge the updates list
		$config = array();
    $model = new InstallerModelUpdate($config);
    # $model = new UpdateModel($config);
		$model->purge();

		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Get the caching duration
		$params = ComponentHelper::getParams('com_installer');
		$cache_timeout = $params->get('cachetimeout', 6, 'int');
		$cache_timeout = 3600 * $cache_timeout;

		// Find updates
		$updater = Updater::getInstance();

		/*
		 * The following function uses extension_id 600, that is the english language extension id.
		 * In #__update_sites_extensions you should have 600 linked to the Accredited Translations Repo
		 */
		$updater->findUpdates(array(600), $cache_timeout);

		$this->setRedirect(Route::_('index.php?option=com_jalang&view=tool', false));
	}

	/**
	 * return html output console style
	 */
	public function consoleInit() {
		set_time_limit(0);
		@ini_set('memory_limit', '256M');

		if (function_exists('apache_setenv')) {
			apache_setenv('no-gzip', '1');
		}
		@ini_set('zlib.output_compression', 0);
		@ini_set('output_buffering', 'On');
		@ini_set('implicit_flush', 1);

		/*$test = ob_get_status(true);*/
		if(!ob_get_level()) {
			ob_start();
		}
		for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
		@ob_implicit_flush();

		if(!headers_sent()) {
			header( 'Content-type: text/html; charset=utf-8' );
		}
		echo '<link rel="stylesheet" href="'.Uri::root().'/administrator/components/com_jalang/asset/console.css" type="text/css" />';
	}

	public function translate() {
		$this->consoleInit();

		$input = Factory::getApplication()->input;
		$itemtype = $input->get('itemtype');
		//$from = $input->get('translate_from', '*');
		$from = JalangHelper::getDefaultLanguage();
		$to = $input->get('translate_to', '*');

        $params = ComponentHelper::getParams('com_jalang');
        $translator = JalangHelperTranslator::getInstance($params->get('translator_api_active', 'bing'));

		$translator->translateTable($itemtype, $from, $to);
		jexit('Done');
	}

	public function translateAll() {
		$this->consoleInit();
		$input = Factory::getApplication()->input;
		//$from = $input->get('translate_from', '*');
		//$to = $input->get('translate_to', '*');
		$languages = JalangHelper::getListInstalledLanguages();
		$from = JalangHelper::getDefaultLanguage();

		if(!headers_sent()) {
			header( 'Content-type: text/html; charset=utf-8' );
		}
		
		$input = Factory::getApplication()->input;
		$langto = $input->get('langto', '', 'RAW');
        
        if ($langto == "") {
            jexit (Text::_("NO_LANGUAGE_CHOOSE_TO_TRANSLATE"));
        }

		foreach($languages AS $k => $lang) {
			if (!preg_match('/'.$k.'/', $langto)) continue;
			if($lang->element == $from) continue;
            $params = ComponentHelper::getParams('com_jalang');
			$translator = JalangHelperTranslator::getInstance($params->get('translator_api_active', 'bing'));
			$translator->sendOutput('<h3>'.Text::sprintf('START_TO_TRANSLATE_FOR_THE_VAR_LANGUAGE', $lang->name).'</h3>');

			$translator->translateAllTables($from, $lang->element);
			//sleep(2);
			$translator->sendOutput(str_pad('', 50, '-'));
		}
        echo "<p id='translation-completed'>DONE</p>";
		jexit();
	}

	public function moveAll() {
		$this->consoleInit();
		$input = Factory::getApplication()->input;
		$from_language = $input->get('from_language_tag', '');
		if(!$from_language) {
			$from_language = $input->get('from_language', '');
		}
		$to_language = $input->get('to_language', '');

		if(!headers_sent()) {
			header( 'Content-type: text/html; charset=utf-8' );
		}

		$tool = new JalangHelperTool();
		$tool->moveAllTables($from_language, $to_language);

		jexit('Done');
	}

	public function removeLanguage() {
		$this->consoleInit();
		$input = Factory::getApplication()->input;
		$language = $input->get('lang_remove', '');

		if(!headers_sent()) {
			header( 'Content-type: text/html; charset=utf-8' );
		}

		$tool = new JalangHelperTool();
		$tool->removeLanguage($language);

		jexit('Done');
	}
	
	public function bindFLEXI() {
		$db = Factory::getDbo();
		$db->setQuery("SELECT ".$db->quoteName('enabled')." FROM #__extensions WHERE ".$db->quoteName('type')." = 'component' AND ".$db->quoteName('element')." = 'com_flexicontent'");
		$is_enabled = $db->loadResult();
		if ($is_enabled == 1) {
			require_once (JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_flexicontent'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'flexicontent.helper.php');
			JLoader::import('joomla.application.component.model');
			JLoader::import( 'items', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_flexicontent' . DIRECTORY_SEPARATOR . 'models' );
			
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$model = BaseDatabaseModel::getInstance( 'items', 'flexicontentModel' );
			}
			else if (version_compare(JVERSION, '2.5', 'ge'))
			{
				$model = JModel::getInstance( 'items', 'flexicontentModel' );
			}
			else
			{
				$model = JModel::getInstance( 'items', 'flexicontentModel' );
			}
			$rows  = $model->getUnboundedItems(2500, $count_only=false, $checkNoExtData=true, $checkInvalidCat=false, $noCache=true);
			$model->bindExtData($rows);
			JalangHelperTool::fixFLEXIAssLanguage($rows);
		}
		jexit('DONE');
	}

}
