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
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
if(JFile::exists(JPATH_COMPONENT."/jak2filter.txt") && JFile::exists(JPATH_COMPONENT."/com_jak2fiter.xml")){
	if(JFile::exists(JPATH_COMPONENT."/jak2filter.xml")){
		JFile::delete(JPATH_COMPONENT."/com_jak2fiter.xml");
	}else{
		$oldxmlfile = JPath::clean(JPATH_COMPONENT.'/com_jak2filter.xml');
		$newxmlfile =  JPATH_COMPONENT.'/jak2filter.txt';

		$newxmlfilecontent = JFile::read($newxmlfile);
		JFile::write($oldxmlfile, $newxmlfilecontent);
		rename($oldxmlfile, str_replace('com_jak2filter.xml', 'jak2filter.xml', $oldxmlfile));

	}
}
if(JFile::exists(JPATH_COMPONENT."/jak2filter.txt")){
	//Delete file update
	JFile::delete(JPATH_COMPONENT.'/jak2filter.txt');
}
//Delete file com_jak2fiter.xml
if(JFile::exists(JPATH_COMPONENT."/com_jak2fiter.xml")){
	JFile::delete(JPATH_COMPONENT.'/com_jak2fiter.xml');
}

JLoader::register('JAK2FilterController', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('JAK2FilterView', JPATH_COMPONENT.'/views/view.php');
JLoader::register('JAK2FilterModel', JPATH_COMPONENT.'/models/model.php');

$app = JFactory::getApplication();
$controller	= JAK2FilterController::getInstance('jak2filter');
$controller->execute($app->input->get('task'));
$controller->redirect();
/*
* Make sure the user is authorized to view this page
*/
