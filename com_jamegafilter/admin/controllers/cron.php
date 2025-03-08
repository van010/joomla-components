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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class JaMegafilterControllerCron extends BaseController {
	
	function getModel($name = 'Cron', $prefix = '', $config = array()) {
		return parent::getModel($name, $prefix, $config);
	}
	
	function save() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
			$this->redirect();
		}

		$model = $this->getModel();
		$model->save();
		$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
	}
	
	function newCronUrl() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
			$this->redirect();
		}

		$model = $this->getModel();
		$model->save(true);
		$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
	}
	
	function reset() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
			$this->redirect();
		}

		$model = $this->getModel();
		$model->save(false, true);
		$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=cron', false));
	}
	
}