<?php

/**
 * ------------------------------------------------------------------------
 * JA Megafilter Component
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Component\ComponentHelper;

class JaMegafilterModelCron extends FormModel {
	
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_jamegafilter.cron', 'cron', array('control' => 'jform', 'load_data' => $loadData));
		return $form;
	}
	
	function save($new_cron = false, $reset_last_cron = false) {
		$app = Factory::getApplication();
		$input = $app->input;
		$data = $input->get('jform', array(), 'registry');
		$data = new Registry($data);
		$time = $data->get('time');
		if (!$time) {
			$app->enqueueMessage(Text::_('COM_JAMEGAFILTER_MISSING_TIME'), 'error');
			return false;
		}
		
		$fids = $data->get('fids', array());

		$crontoken = $data->get('crontoken');
		if (!$crontoken || $new_cron) {
			$crontoken = $this->newCronToken($new_cron);
		}
		
		$params = ComponentHelper::getParams('com_jamegafilter');
		$params->set('time', $time);
		$params->set('fids', $fids);
		$params->set('crontoken', $crontoken);
		if ($reset_last_cron) {
			$params->set('last_cron', 0);
			$app->enqueueMessage(Text::_('COM_JAMEGAFILTER_LAST_CRON_TIME_HAS_BEEN_RESET'), 'notice');
		}
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions')
			->set($db->quoteName('params') . '=' . $db->quote($params->toString()))
			->where($db->quoteName('element') . '=' . $db->quote('com_jamegafilter'));
		$db->setQuery($query);
		if ($db->execute()) {
			$app->enqueueMessage( Text::_('COM_JAMEGAFILTER_SAVE_SUCCESS'));
			return true;
		} else {
			$app->enqueueMessage( Text::_('COM_JAMEGAFILTER_SAVE_FAILED'));
			return false;
		}
	}
	
	function newCronToken() {
		$length = rand(50, 100);
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	function getItem() {
		$params = ComponentHelper::getParams('com_jamegafilter');
		if ($params->get('crontoken')) {
			return $params;
		}
	}

}