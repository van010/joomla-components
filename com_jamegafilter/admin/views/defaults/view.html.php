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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class JaMegaFilterViewDefaults extends HtmlView
{
	function display($tpl = null)
	{
		$app = Factory::getApplication();
		// Get data from the model
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode('<br />', $errors), 'message');

			return false;
		}

		// Set the toolbar
		$this->addToolBar();
		if (version_compare(JVERSION, '4.0', '>=')){
			$this->sidebar = Sidebar::render();
		}else{
			$this->sidebar = JHtmlSidebar::render();
		}
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		JaMegafilterHelper::addSubmenu('defaults');
		
		ToolbarHelper::title(Text::_('COM_JAMEGAFILTER_MANAGER_DEFAULTS'));
		ToolbarHelper::addNew('default.add');
		ToolbarHelper::editList('default.edit');
		ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'defaults.delete');
 		ToolbarHelper::custom('defaults.export', 'database', '', Text::_('COM_JAMEGAFILTER_EXPORT'));
		ToolbarHelper::custom('defaults.export_all', 'pending', '', Text::_('COM_JAMEGAFILTER_EXPORT_ALL'), false);

		$user = Factory::getUser();
		if ($user->authorise('core.admin', 'com_jamegafilter') || $user->authorise('core.options', 'com_jamegafilter'))
		{
			ToolbarHelper::preferences('com_jamegafilter');
		}
	}
}