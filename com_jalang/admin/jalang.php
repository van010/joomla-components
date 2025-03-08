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
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;


if (!Factory::getUser()->authorise('core.manage', 'com_jalang'))
{
	return Factory::getApplication()->enqueueMessage(Text::_('JAERROR_ALERTNOAUTHOR'), 'error');
}
/*
require_once( __DIR__ . '/helpers/helper.php' );
require_once( __DIR__ . '/helpers/content/content.php' );
require_once( __DIR__ . '/helpers/translator/translator.php' );
*/
require_once( dirname(__FILE__) . '/helpers/helper.php' );
require_once( dirname(__FILE__) . '/helpers/tool.php' );
require_once( dirname(__FILE__) . '/helpers/content/content.php' );
require_once( dirname(__FILE__) . '/helpers/translator/translator.php' );  

$app = Factory::getApplication();
$helper = new JalangHelper();
$helper->update();
$jinput = $app->input;
$itemtype = $jinput->get('itemtype', 'content');
if(!empty($itemtype)) {
	$app->setUserState('com_jalang.itemtype', $itemtype);
}
$mainlanguage = $jinput->get('mainlanguage', JalangHelper::getDefaultLanguage());
if(!empty($mainlanguage)) {
	$app->setUserState('com_jalang.mainlanguage', $mainlanguage);
}

//asset
$document = Factory::getDocument();

if(JalangHelper::isJoomla4x()) {
	$document->addStyleSheet('components/com_jalang/asset/style_4x.css');
} elseif (JalangHelper::isJoomla3x()) {
	$document->addStyleSheet('components/com_jalang/asset/style.css');
} else {
	$document->addStyleSheet('components/com_jalang/asset/style_2x.css');
	$document->addScript('components/com_jalang/asset/jquery.min.js');
	$document->addScript('components/com_jalang/asset/jquery-noconflict.js');
}
$controller = BaseController::getInstance('Jalang');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
