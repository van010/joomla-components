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


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;

jimport('joomla.application.component.modellist');
class JalangHelperContent extends ListModel
{
	/**
	 * @var string - type of table structure for multilingual design
	 * supported type:
	 * native - Joomla native structure
	 * alias - Use alias key for language detection
	 * table - use separated tables for multilingual content (Virtual Mart Component uses this structure)
	 * table_ml - a Non-text data is stored in one table, and a multilingual data is store in one table . (E.g: Mijoshop)
	 */
	public $table_type = 'native';

	/**
	 * @var string - name of language field
	 */
	public $language_field = 'language';
	/**
	 * @var string - type of value store in language field
	 * supported type:
	 * code - language tag, used in Joomla core components and many 3rd extensions
	 * id - language id, used in Mijoshop
	 */
	public $language_mode = 'code';
	/**
	 * @var string $table - table name
	 */
	public $table = null;

	public $edit_context = null;
	/**
	 * @var string $associate_context - context key of this table for storing in associate table
	 */
	public $associate_context = null;
	/**
	 * @var string $primarykey - name of primary key field
	 */
	public $primarykey = 'id';
	/**
	 * @var string $title_field - name of field that displayed as title
	 */
	public $title_field = 'title';
	/**
	 * @var string $alias_field - name of alias field if any
	 */
	public $alias_field = null;
	/**
	 * @var array $translate_fields - list of Field that will being translated
	 */
	public $translate_fields = array();
	/**
	 * @var array $reference_fields - list of fields what is foreign key of other tables (Item format: field name => table name)
	 */
	public $reference_fields = array();

	public $reference_tables = array();

	/**
	 * @var array $fixed_fields - list of files in new item will be fixed value
	 */
	public $fixed_fields = array();

	public $translate_filters = array();
	/*for only tables that has nested structure (such as categories, menus)*/
	public $nested_field = null;
	public $nested_value = null;

	protected $name = null;
	protected $state = null;
	/**
	 * @var    JalangHelperContent  instance container.
	 * @since  11.3
	 */
	protected static $instance = array();
	protected static $scanned = 0;
	protected static $adapters = array();
	public $mijoshop_type = 'update';

	public function __construct($config = array())
	{
		$this->name = get_class($this);
		$this->state = new CMSObject();
		$this->context = 'com_jalang.items';
		//parent::__construct($config);
		// detect edit link for mijoshop version.
		if (File::exists(JPATH_ADMINISTRATOR .'/components/com_mijoshop/mijoshop.xml')) {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR .'/components/com_mijoshop/mijoshop.xml');
			if (!empty($xml)) {
				$version = (string)$xml->version;
				if((int)$version < (int)'3.0.0')
					$this->mijoshop_type = 'update';
				else
					$this->mijoshop_type = 'edit';
			}
		}
	}

	/**
	 * @param string $itemtype - table name
	 * @return JalangHelperContent|boolean
	 */
	public static function getInstance($itemtype, $prefix = '', $config = array()) {
		$itemtype = strtolower($itemtype);
		
		if (!isset(self::$instance[$itemtype]) || !self::$instance[$itemtype])
		{
			$adapters = self::loadAdapters();
			if(!isset($adapters[$itemtype])) {
				Factory::getApplication()->enqueueMessage(Text::_('ITEM_TYPE_IS_NOT_SUPPORTED'), 'warning');
				return false;
			}

			$adapter = $adapters[$itemtype];

			if(!File::exists($adapter['file'])) {
				Factory::getApplication()->enqueueMessage(Text::_('ITEM_TYPE_IS_NOT_SUPPORTED'), 'warning');
				return false;
			}
			include_once($adapter['file']);

			$classname = str_replace('_', ' ', $itemtype);
			$classname = 'JalangHelperContent'.str_replace(' ', '', ucwords($classname));

			if(!class_exists($classname)) {
				Factory::getApplication()->enqueueMessage(Text::sprintf('CLASS_VAR_IS_NOT_DEFINED', $adapter), 'warning');
				return false;
			}
			
			self::$instance[$itemtype] = new $classname;
		}
		
		return self::$instance[$itemtype];
	}

	/**
	 * @return array - list of sorted adapters by translate ordering
	 */
	public static function getListAdapters () {
		$adapters = self::loadAdapters();
		usort($adapters, array('JalangHelperContent', 'adapterCmp'));
		return $adapters;
	}

	public static function adapterCmp($a, $b)
	{
		if ($a['ordering'] == $b['ordering']) {
			return 0;
		}
		return ($a['ordering'] < $b['ordering']) ? -1 : 1;
	}


	public static function loadAdapters () {
		if(!self::$scanned) {
			$path = dirname(__FILE__). '/adapters/';
			$adapters = Folder::files($path, '\.php$', false, true);
			foreach ($adapters as $adapter) {
				include_once($adapter);
			}
			self::$scanned = 1;
		}

		return self::$adapters;
	}

	/**
	 * @param string $file - path to file where define adapter
	 * @param $name - table name
	 * @param int $ordering - translating order
	 * @param string $title
	 * @param string $description
	 *
	 * @desc
	 * Rule of defining ordering for your adapter
	 * [1-2] is for Joomla component that need translate first
	 * [3-8] is for 3rd components
	 * [9-] is for Joomla component that need translate last (such as modules or menu items)
	 */
	public static function registerAdapter($file, $name, $ordering = 3, $title = '', $description = '') {
		if(!$title) {
			$title = str_replace('.', ' ', $name);
			$title = ucwords($title);
		}

		self::$adapters[$name] = array(
									'name' => $name,
									'file' => $file,
									'ordering' => $ordering,
									'title' => $title,
									'description' => $description
								);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// List state information.
		//parent::populateState('a.title', 'asc');
	}

	/**
	 * @param $id
	 *
	 * return the link to edit page corresponding item type
	 */
	public function getEditLink($id) {
		return false;
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	public function getListQuery2($model)
	{
		if(!$this->table) {
			$this->errorExtendMethod(__METHOD__);
			return false;
		}
		// Create a new query object.
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from ('#__'.$this->table.' AS a');
		
		// Filter on the language.
		$defaultLanguage = JalangHelper::getDefaultLanguage();
		$language = $model->getState('mainlanguage');
		$filter_search = $model->getState('filter.search');
		if($language) {
			if ($this->table_type == 'native' || $this->table_type == 'table_ml') {
				$langField = 'a.'.$db->quoteName($this->language_field);
				if($this->language_mode == 'id') {
					$query->where($langField.' = ' . $db->quote(JalangHelper::getLanguageIdFromCode($language)));
				} else {
					if($language == $defaultLanguage) {
						$query->where('('.$langField.' = ' . $db->quote($language) . ' OR '.$langField.' = ' . $db->quote('*').' OR '.$langField.' = ' . $db->quote('').')');
					} else {
						$query->where($langField.' = ' . $db->quote($language));
					}
				}
			} elseif ($this->table_type == 'table') {
				if($language == '*') $language = $defaultLanguage;
				$params = ComponentHelper::getParams('com_jalang');
				$translator = JalangHelperTranslator::getInstance($params->get('translator_api_active', 'bing'));
				$table = $translator->getLangTable($this->table, $language);
				$query->clear('from');
				$query->from('#__'.$table);

			} elseif($this->table_type == 'alias') {

				if($language != '*') {
					@list($shortlang, $country) = explode('-', $language);
					if($language == $defaultLanguage) {
						$query->where('('.$db->quoteName($this->alias_field) .' LIKE '.$db->quote('%-'.$shortlang).' OR '.$db->quoteName($this->alias_field) .' NOT REGEXP '.$db->quote('\\-[a-z]{2}$').')');
					} else {
						$query->where($db->quoteName($this->alias_field) .' LIKE '.$db->quote('%-'.$shortlang));
					}
				} else {
					$query->where($db->quoteName($this->alias_field) .' NOT REGEXP '.$db->quote('\\-[a-z]{2}$'));
				}
			}
		}

		if(count($this->translate_filters)) {
			$query->where($this->translate_filters);
		}

		if($filter_search) {
			$fields = $this->getDisplayFields();
			if(is_array($fields)) {
				$filterKeyword = array();
				foreach($fields as $field => $ftitle) {
					$filterKeyword[] = $db->quoteName($field).' LIKE '.$db->quote('%'.$filter_search.'%');
				}
				$query->where('('.implode(' OR ', $filterKeyword).')');

			} else {
				$query->where($db->quoteName($this->title_field).' LIKE '.$db->quote('%'.$filter_search.'%'));
			}
		}

		$ordering = $model->getState('list.ordering', 'a.ordering');
		if(in_array($ordering, $this->getSortFields())) {
			$query->order($ordering . ' ' . $model->getState('list.direction', 'ASC'));
		}

		return $query;
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 */
	public function getSortFields()
	{
		return array();
	}
	
	/**
	 * Returns an array of fields will be displayed in the table list
	 */
	public function getDisplayFields()
	{
		$this->errorExtendMethod(__METHOD__);
	}

	public function getRow($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__'.$this->table);
		$query->where($db->quoteName($this->primarykey).'='.$db->quote($id));

		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}

	public function checkout($id) {
		$db = Factory::getDbo();
		$fields = $db->getTableColumns('#__'.$this->table);
		if(isset($fields['checked_out']) && isset($fields['checked_out_time'])) {
			$user = Factory::getUser();
			$row = $this->getRow($id);
			if(!$row) return false;

			if ($row->checked_out > 0 && $row->checked_out != $user->get('id'))
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
				return false;
			}
			$jdate = Date::getInstance();
			// Get the current time in MySQL format.
			$time = $jdate->toSql();

			$query = $db->getQuery(true);
			$query->update('#__'.$this->table);
			$query->set(array('checked_out='.$user->get('id'), 'checked_out_time='.$db->quote($time)));
			$query->where($db->quoteName($this->primarykey).'='.$db->quote($id));
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
	
	protected function errorExtendMethod($method)
	{
		Factory::getApplication()->enqueueMessage(Text::sprintf('THE_VAR_IS_NOT_IMPLEMENTED_FOR_THE_CLASS_VAR', $method, get_class()), 'error');
	}

	/*TRANSLATION METHODS*/
	/**
	 * is called on before save item to database event
	 * @param JalangHelperTranslator $translator
	 * @param int $sourceid - id of source item
	 * @param array $row - data for new item
	 */
	public function beforeSave(&$translator, $sourceid, &$row) { }
	/**
	 * is called on before save item to database event
	 * @param JalangHelperTranslator $translator
	 * @param int $sourceid - id of source item
	 * @param array $row - data for new item
	 */
	public function afterSave(&$translator, $sourceid, &$row) {
		//After translate item, if item is set to display in All language
		//then it will be updated to display in default language

		$defaultLanguage = JalangHelper::getDefaultLanguage();
		if(isset($row['language']) && (($translator->fromLangTag == '*') || ($translator->fromLangTag == $defaultLanguage))) {
			//Update language for item set to All language since the item set to All languages can't be associated.
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__'.$this->table)->set($db->quoteName('language').'='.$db->quote($defaultLanguage));
			$query->where($db->quoteName($this->primarykey).'='.$db->quote($sourceid));
			$db->setQuery($query);
			$db->execute();
		}
	}
	/**
	 * is called on before translate table
	 * @param JalangHelperTranslator $translator
	 */
	public function beforeTranslate(&$translator) { }
	/**
	 * is called on after translate table
	 * @param JalangHelperTranslator $translator
	 */
	public function afterTranslate(&$translator) { }

	/**
	 * Function using for append language code to anyfield we want. duplicate from getnewalias.
	 */
	public function AppendLangToField($alias, $fromLangTag, $toLangTag, $row = null, $generateFrom = '', $makeUnique = false) {
		static $checkedAlias = array();
		@list($from, $fromCountry) = explode('-', $fromLangTag);
		@list($to, $toCountry) = explode('-', $toLangTag);
		$fromCountry = strtolower($fromCountry);
		$toCountry = strtolower($toCountry);
		
		$newAlias = $row[$generateFrom];
		if (version_compare(JVERSION, '4.0', 'ge'))
			$newAlias = ApplicationHelper::stringURLSafe($newAlias, $toLangTag);
		else
			$newAlias = JApplication::stringURLSafe($newAlias);
		$newAlias .= '-'.JalangHelper::getAliasSuffix($toLangTag);
		if($makeUnique) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName($this->alias_field))
				->from('#__'.$this->table);
			$i = (int) (isset($checkedAlias[$newAlias]) ? $checkedAlias[$newAlias] : 0);
			do {
				$testAlias = $i ? $newAlias .'-'.$i : $newAlias;
				$query->clear('where');
				$query->where($db->quoteName($this->alias_field) . '=' . $db->quote($testAlias));
				$db->setQuery($query);
				$test = $db->loadResult();
				$i++;
			} while($test);
			$checkedAlias[$newAlias] = $i;
			$newAlias = $testAlias;
		}

		return $newAlias;
	}

	/**
	 * @param $alias - alias of default item
	 * @param $fromLangTag - source language tag
	 * @param $toLangTag - destination language tag
	 * @param $row - data of new item
	 * @param $generateFrom - how to generate alias
	 * @param $makeUnique - if true, it will test if generate alias is existed and try to create another one.
	 * @return string - new alias
	 */
	public function getNewAlias($alias, $fromLangTag, $toLangTag, $row = null, $generateFrom = '', $makeUnique = false) {
		static $checkedAlias = array();
		@list($from, $fromCountry) = explode('-', $fromLangTag);
		@list($to, $toCountry) = explode('-', $toLangTag);
		$fromCountry = strtolower($fromCountry);
		$toCountry = strtolower($toCountry);

		$hasTitle = (is_array($row) && isset($row[$this->title_field]) && !empty($row[$this->title_field]));
		if(!$generateFrom) {
			if($from == $to) {
				$generateFrom = 'alias';
			} else {
				$params = ComponentHelper::getParams('com_jalang');
				$generateFrom = $params->get('alias_type', 'title');
				if($this->table_type == 'alias') {
					$generateFrom = 'alias';
				}
			}
		}
		if($generateFrom == 'title' && $hasTitle) {
			$newAlias = $row[$this->title_field];
			if (version_compare(JVERSION, '4.0', 'ge'))
				$newAlias = ApplicationHelper::stringURLSafe($newAlias, $toLangTag);
			else
				$newAlias = JApplication::stringURLSafe($newAlias);
			if($newAlias == $alias) {
				$newAlias .= '-'.JalangHelper::getAliasSuffix($toLangTag);
			}
		} else {
			//append language code after alias of item in default language
			$suffix = JalangHelper::getAliasSuffix($fromLangTag);
			$newAlias = preg_replace('/\-('.str_replace('-', '\\-', $suffix).')$/', '', $alias);//remove suffix of other language
			$newAlias .= '-'.JalangHelper::getAliasSuffix($toLangTag);
			if($newAlias == $alias) {
				//source language and destination language have a same language code
			}
		}
		if($makeUnique) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName($this->alias_field))
				->from('#__'.$this->table);
			$i = (int) (isset($checkedAlias[$newAlias]) ? $checkedAlias[$newAlias] : 0);
			do {
				$testAlias = $i ? $newAlias .'-'.$i : $newAlias;
				$query->clear('where');
				$query->where($db->quoteName($this->alias_field) . '=' . $db->quote($testAlias));
				$db->setQuery($query);
				$test = $db->loadResult();
				$i++;
			} while($test);
			$checkedAlias[$newAlias] = $i;
			$newAlias = $testAlias;
		}

		return $newAlias;
	}
}