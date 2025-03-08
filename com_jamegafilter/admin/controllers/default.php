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
use Joomla\CMS\MVC\Controller\FormController;

class JaMegaFilterControllerDefault extends FormController
{

	function add()
	{
		if (!Factory::getUser()->authorise('jamegafilter.create', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		parent::add();
	}

	function edit($key = NULL, $urlVar = NULL)
	{
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		parent::edit($key = NULL, $urlVar = NULL);
	}

	function saveobj() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();

		}

		$model = $this->getModel();
		return $model->saveobj();
	}
	
	function jaapply() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		$obj = $this->saveobj();
		$this->setMessage(Text::_('COM_JAMEGAFILTER_SAVE_SUCCESS'));
		$this->setRedirect('index.php?option=com_jamegafilter&view=default&layout=edit&id='.$obj->id);
	}
	
	function jasave() {
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		$this->saveobj();
		$app = Factory::getApplication();
		$app->enqueueMessage(Text::_('COM_JAMEGAFILTER_SAVE_SUCCESS'));
		$this->setRedirect('index.php?option=com_jamegafilter');
	}
}
