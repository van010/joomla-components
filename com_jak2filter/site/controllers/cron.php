<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

class JAK2FilterControllerCron extends JAK2FilterController
{
	public function display($cachable = false, $urlparams = false) {
		$jinput = JFactory::getApplication()->input;
		$params = JComponentHelper::getParams('com_jak2filter');
		$indexing = (int) $params->get('indexing_cron', 1);
		$interval = (int) $params->get('indexing_interval', 900);
		$interval = $interval * 60;
		$cronkey = $params->get('indexing_cron_key', 'indexing');
		
		if(!$indexing) die('system is indexing, please wait...');
		
		$db = JFactory::getDbo();
		$query = "SELECT updatetime FROM `#__jak2filter` WHERE `name` = 'cron'";
		$db->setQuery($query);
		$updatetime = $db->loadResult();
		$updatetime = !$updatetime ? 0 : strtotime($updatetime);
		
		$key = $jinput->get('jakey');
		$run = (($updatetime + $interval < time()) || ($key == $cronkey));
		
		if($run) {
			$now = date('Y-m-d H:i:s');
			$query = "
					INSERT INTO #__jak2filter
					SET 
						`name` = 'cron',
						`updatetime` = ".$db->quote($now).",
						`value` = 1
					ON DUPLICATE KEY UPDATE
						`updatetime` = ".$db->quote($now).",
						`value` = 1
					";
			$db->setQuery($query);
			$db->query();
			//
			$helper = new JAK2FilterHelper();
			$helper->indexingData('cron');
		} else {
			$msg = JText::sprintf('The cron job will be run on %s', date('Y-m-d H:i:s', $updatetime + $interval));
			jexit($msg);
		}
	}
}