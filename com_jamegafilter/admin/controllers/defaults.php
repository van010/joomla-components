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
use Joomla\CMS\MVC\Controller\AdminController;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class JaMegaFilterControllerDefaults extends AdminController
{
	function getModel($name = 'Default', $prefix = 'JaMegaFilterModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	function delete()
	{
		if (!Factory::getUser()->authorise('jamegafilter.delete', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		parent::delete();
	}
	
	function export()
	{
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}

		$app = Factory::getApplication();
		$input = $app->input;
		$cid = $input->post->get('cid', array(), 'array');
		foreach ( $cid as $id)
		{
			$this->getModel()->proxyExport($id);
		}

		$this->setMessage(Text::_('COM_JAMEGAFILTER_EXPORT_SUCCESS'));
		$this->setRedirect('index.php?option=com_jamegafilter');
	}
	
	function export_all()
	{
		if (!Factory::getUser()->authorise('jamegafilter.edit', 'com_jamegafilter'))
		{
			$this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jamegafilter&view=defaults', false));
			$this->redirect();
		}
		
		$app = Factory::getApplication();
		$mode_list = $this->getModel('Defaults','JaMegaFilterModel');
		$items = $mode_list->getItems();
		foreach ( $items as $item)
		{
			$this->getModel()->proxyExport($item->id);
		}

		$this->setMessage(Text::_('COM_JAMEGAFILTER_EXPORT_SUCCESS'));
		$this->setRedirect('index.php?option=com_jamegafilter');
	}
}
