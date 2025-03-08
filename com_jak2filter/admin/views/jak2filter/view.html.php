<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_jak2filter
 * @since		1.5
 */
class Jak2filterViewJak2filter extends JAK2FilterView
{

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		// Check for errors.
		$errors = $this->get('Errors');
		if (!empty($errors)) {
			$app->enqueueMessages(implode("\n", $errors), 'message');
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	2.5
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JA_K2_FILTER_ADMINISTRATOR'), 'search.png');
		JToolBarHelper::preferences('com_jak2filter');
		JToolBarHelper::custom('reindexing', 'refresh', 'refresh', JText::_('UPDATE_INDEXING'), false);

		$bar = JToolBar::getInstance('toolbar');

		$uri = (string) JUri::getInstance();
		$return = urlencode($uri);

		// Add a button linking to config for component.
		$bar->appendButton(
			'Link',
			'list',
			JText::_('MANAGE_MULTI_LEVEL_FIELDS_DATA'),
			'index.php?option=com_categories&amp;extension=com_jak2filter'
		);
	}
}
