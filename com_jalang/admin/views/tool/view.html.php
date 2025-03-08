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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for a list of articles.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_jalang
 * @since       1.6
 */

if (!class_exists('ViewLegacy')) {
	if (version_compare(JVERSION, 4, 'ge')) {
		class ViewLegacy extends HtmlView{}
	} else {
		class ViewLegacy extends JViewLegacy{}
	}
}


class JalangViewTool extends ViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		$this->tabbar = JalangHelper::addSubmenu('tool', $this->getLayout());
		// Check for errors.
		$errors = $this->get('Errors');
		if (!empty($errors) && count($errors))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
			return false;
		}

		$this->addToolbar();

		if(JalangHelper::equalJoomla3x()) {
			$this->sidebar = JHtmlSidebar::render();
		}

        if (JalangHelper::greaterThanJoomla4x()) {
            $this->sidebar = Sidebar::render();
        }

		//
		$this->adapters = JalangHelperContent::getListAdapters();
		
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$bar = ToolBar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('TRANSLATION_MANAGER'), 'article.png');

		ToolbarHelper::preferences('com_jalang');

		if (JalangHelper::isInstalled('com_flexicontent')) {
			ToolbarHelper::custom('tool.bindFLEXI', 'copy', '', Text::_('Transfer to FLEXI'));
		}
		if(JalangHelper::equalJoomla3x()) {
			JHtmlSidebar::setAction('index.php?option=com_jalang&view=tool');
		}
        if (JalangHelper::greaterThanJoomla4x()) {
            Sidebar::setAction('index.php?option=com_jalang&view=tool');
        }
	}
}
