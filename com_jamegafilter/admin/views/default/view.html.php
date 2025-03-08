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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

class JaMegaFilterViewDefault extends HtmlView
{

	protected $form = null;


	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$this->type = $input->get('type');
		PluginHelper::importPlugin('jamegafilter');
		// Get the Data
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		if ($this->form && $this->item->params) {
			$this->form->bind($this->item->params);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessages(implode('<br />', $errors), 'message');

			return false;
		}

		// Set the toolbar
		$this->addToolBar();
		
		$this->title = $input->get('title', '', 'raw');
		$this->published = $input->get('published', 0);
		if (!empty($this->type))
			$this->item->type = $this->type;
		if ($this->item->type == NULL) $this->item->type = 'blank';
		if (!empty($this->published))
			$this->item->published = $this->published;
		if (!empty($this->title))
			$this->item->title = $this->title;
		
		$this->typeLists = JaMegafilterHelper::getSupportedComponentList();
		$this->checkComponent = JaMegafilterHelper::getComponentStatus('com_'.$this->item->type);
		
		$this->id = $input->get('id');
		if (!$this->checkComponent && $this->id) {
			$app->enqueueMessage(Text::sprintf('COM_JAMEGAFILTER_COMPONENT_NOT_FOUND', ucfirst($this->item->type)), 'error');
		}
		
		if (!$this->form && $this->id) {
			$app->enqueueMessage(Text::_('COM_JAMEGAFILTER_FORM_NOT_FOUND'), 'error');
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
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$title = Text::_('COM_JAMEGAFILTER_NEW');
		}
		else
		{
			$title = Text::_('COM_JAMEGAFILTER_EDIT');
		}

		ToolbarHelper::title($title, 'default');
		ToolbarHelper::apply('default.jaapply');
		ToolbarHelper::save('default.jasave');
		ToolbarHelper::cancel(
			'default.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}
}