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

$app = JFactory::getApplication();
$jinput = $app->input;
$controller = $jinput->getWord('view', 'itemlist');
$task = $jinput->getWord('task') ? $jinput->getWord('task') : '';

jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');

if (JFile::exists(JPATH_COMPONENT.'/controllers/'.$controller.'.php')) {
	if( JFile::exists(JPATH_BASE.'/components/com_k2/k2.php')) {
		JLoader::register('JAK2FilterController', 	JPATH_COMPONENT_ADMINISTRATOR.'/controllers/controller.php');
		JLoader::register('JAK2FilterModel', 		JPATH_COMPONENT_ADMINISTRATOR.'/models/model.php');
		JLoader::register('JAK2FilterView', 		JPATH_COMPONENT_ADMINISTRATOR.'/views/view.php');
		JLoader::register('JAK2FilterHelper', 		JPATH_COMPONENT.'/helpers/helper.php');
		JLoader::register('JAK2FilterHelperUpgrade', JPATH_COMPONENT.'/helpers/upgrade.php');
		//load language from component k2
		$lang =JFactory::getLanguage();
		$lang->load('com_k2');

		//check upgrade
		$helper = new JAK2FilterHelperUpgrade();
		$helper->checkUpdate();

		require_once (JPATH_COMPONENT.'/controllers/'.$controller.'.php');
		$classname = 'JAK2FilterController'.$controller;
		$controller = new $classname();
		$controller->execute($task);
		$controller->redirect();
		
	}else{
		$app->redirect('index.php',JText::_('COMPONENT_K2_NOT_FOUND'),'error');
		
	}
}
else {
	$app::enqueueMessage(JText::_('COMPONENT_NOT_FOUND'), 'error');
}

