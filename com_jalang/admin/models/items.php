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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of article records.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
class JalangModelItems extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$adapter = JalangHelper::getHelperContent();
			if($adapter) {
				$config['filter_fields'] = array_keys($adapter->getSortFields());
			} else {
				$config['filter_fields'] = array();
			}

			$app = Factory::getApplication();
			$assoc = isset($app->item_associations) ? $app->item_associations : 0;
			if ($assoc)
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$language = $app->getUserState('com_jalang.mainlanguage', '*');
		$this->setState('mainlanguage', $language);

		// force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('mainlanguage', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		// force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
		
		// List state information.
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id    A prefix for the store id.
	 *
	 * @return  string  A store id.
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$adapter = JalangHelper::getHelperContent();
		if(!$adapter) return false;
		
		return $adapter->getListQuery2($this);
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$adapter = JalangHelper::getHelperContent();
		$translator = new JalangHelperTranslator();
		$params = ComponentHelper::getParams('com_jalang');

		$language = $app->getUserState('com_jalang.mainlanguage', '*');
		$languages = HTMLHelper::_('contentlanguage.existing', true, true);

		if($adapter->table_type == 'table') {
			if(!$language || $language == '*') {
				$language = JalangHelper::getDefaultLanguage();
			}

			$translator->fromLangTag = $language;
			$from_table = $translator->getLangTable('#__'.$adapter->table, $language);
			$filterById = $db->quoteName('st').'.'.$db->quoteName($adapter->primarykey) . '=%d';
		} else {
			$translator->fromLangTag = $language;
			$from_table = '#__'.$adapter->table;
			$filterById = $db->quoteName('c').'.'.$db->quoteName($adapter->primarykey) . '=%d';
			$translator->loadAssociate($adapter->table, $adapter->associate_context, $adapter->primarykey, false);
		}

		for ($x = 0, $count = count($items); $x < $count; $x++)
		{
			$sourceid = $items[$x]->{$adapter->primarykey};
			if($adapter->table_type == 'table') {
				foreach($languages as $cl) {
          $translator->assocCurrentLang = $language;
					if($cl->value != $language) {
						$translator->toLangTag = $cl->value;
						$translator->loadAssociate($adapter->table, $adapter->associate_context, $adapter->primarykey, true, array(sprintf($filterById, $sourceid)));
					}
				}
			}
			$items[$x]->associations = isset($translator->aAssociation[$adapter->table][$sourceid]) ? $translator->aAssociation[$adapter->table][$sourceid] : array();
		}
		return $items;
	}
}
