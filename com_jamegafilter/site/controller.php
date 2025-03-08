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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;

class JaMegaFilterController extends BaseController {
  function getAdmin(){
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select(array('u.username', 'u.password'))
      ->from($db->quoteName('#__user_usergroup_map', 'ug'))
      ->join('INNER', $db->quoteName('#__users', 'u') .' ON '. $db->quoteName('u.id') .'='. $db->quoteName('ug.user_id'))
      ->where($db->quoteName('ug.group_id') .'=8');
    $db->setQuery($query);
    return $db->loadAssoc();
  }

	function cron() {
    // handle login to get posts had access level
    $user = $this->getAdmin();
    PluginHelper::importPlugin('user');
    $info = [
      'username' => $user['username'],
      'password' => '',
    ];
    $options = [
      'action' => 'core.login.site'
    ];
    Factory::getApplication()->triggerEvent('onUserLogin', array($info, $options));
    
		$input = $this->input;
		$token = $input->get('token');
		$params = ComponentHelper::getParams('com_jamegafilter');
		$ctoken = $params->get('crontoken');

		if ($token !== $ctoken) {
			die('token error');
		}
		
		JLoader::register('BaseFilterHelper', JPATH_ADMINISTRATOR.'/components/com_jamegafilter/base.php');
		JLoader::register('JaMegaFilterModelDefault', JPATH_ADMINISTRATOR . '/components/com_jamegafilter/models/default.php');
		$model = BaseDatabaseModel::getInstance('JaMegaFilterModelDefault');

		$proxy = $input->get('proxy');
		$id = $input->getInt('id');
		if ($proxy && $id) {
			$model->exportByID($id);
			$result = array(
				'success' => 'Export done. ID: ' . $id
			);

			die(json_encode($result));
		}

		$fids = $params->get('fids', array());
		$last_cron = $params->get('last_cron', 0);
		$next_cron = $last_cron + $params->get('time', 0);
		if ($fids && $next_cron < time()) {
			$params->set('last_cron', time());
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__extensions')
				->set($db->quoteName('params') . '=' . $db->quote($params->toString()))
				->where($db->quoteName('element') . '=' . $db->quote('com_jamegafilter'));
			$db->setQuery($query);
			$db->execute();
			
			foreach ($fids as $fid) {
				$model->exportByID($fid);
			}
		}
		
		$result = array(
			'success' => 'cron done'
		);

		die(json_encode($result));
	}
}