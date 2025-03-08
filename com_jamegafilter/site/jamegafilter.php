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
use Joomla\CMS\MVC\Controller\BaseController;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(dirname(__FILE__) . '/assets/asset.php');
JLoader::register('JaMegafilterHelper', JPATH_ADMINISTRATOR.'/components/com_jamegafilter/helper.php');

$controller = BaseController::getInstance('JaMegaFilter');

// Perform the Request task
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
