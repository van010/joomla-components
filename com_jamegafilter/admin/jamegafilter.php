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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\BaseController;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!Factory::getUser()->authorise('core.manage', 'com_jamegafilter'))
{
    throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('JaMegafilterHelper', __DIR__.'/helper.php');
JLoader::register('JFormFieldJaMegafilter_FilterFields', __DIR__.'/models/fields/jamegafilter_filterfields.php');

$app = Factory::getApplication();
$input = $app->input;
$params = ComponentHelper::getParams('com_jamegafilter');
$cronToken = $params->get('crontoken');
$view = $input->get('view');

if (!$cronToken) {
    $app->enqueueMessage(Text::_('COM_JAMEGAFILTER_NEED_CRON_BEFORE_USE'), 'error');
    if ($view !== 'cron') {
        $app->redirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
    }
}

$controller = BaseController::getInstance('JaMegafilter');
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
