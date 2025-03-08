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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class JaMegafilterViewCron extends HtmlView {
	
	function display($tpl = null) {
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		if ($this->item) {
			$this->form->bind($this->item);
		} else {
			$app = Factory::getApplication();
			$app->enqueueMessage( Text::_('COM_JAMEGAFILTER_SAVE_CONFIG_TO_GET_CRON_URL'), 'warning');
		}
		
		$this->addToolBar();
		if (version_compare(JVERSION, '4.0', '>=')){
			$this->sidebar = Sidebar::render();
		}else{
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}
	
	function addToolBar()
	{
		JaMegafilterHelper::addSubmenu('cron');
		
		ToolbarHelper::title('JA Megafilter ' . Text::_('COM_JAMEGAFILTER_CRON'));
		ToolbarHelper::apply('cron.save');
		ToolbarHelper::custom('cron.newCronUrl', 'link','link',Text::_('COM_JAMEGAFILTER_NEW_CRON_URL'), false);
		ToolbarHelper::custom('cron.reset', 'loop','loop',Text::_('COM_JAMEGAFILTER_RESET_LAST_CRON'), false);
	}
}