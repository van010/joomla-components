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
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
require_once JPATH_BASE.'/components/com_k2/helpers/route.php';
require_once JPATH_BASE.'/components/com_k2/helpers/permissions.php';
require_once JPATH_BASE.'/components/com_k2/helpers/utilities.php';
require_once JPATH_BASE.'/components/com_k2/models/item.php';
require_once JPATH_BASE.'/components/com_k2/models/itemlist.php';
class JAK2FilterControllerItemlist extends JAK2FilterController{

    function display($cachable = false, $urlparams = false) {
    	error_reporting(E_ALL ^ E_NOTICE);
		
		$jinput = JFactory::getApplication()->input;
        $jinput->set('task', 'search');
        $jinput->set('view', 'itemlist');
		
        $model=$this->getModel('Itemlist','JAK2FilterModel');
        
        $modelitems = new K2ModelItem();
       
        $modelitems->getData();
        
        $document = JFactory::getDocument();
        
        $viewType = $document->getType();
      
        $view = $this->getView('itemlist', $viewType,'JAK2FilterView');
      
        $view->setModel($model);
        
        $view->setModel($modelitems);
        
        $user = JFactory::getUser();
        
        $cache = false;
        
        parent::display($cache);
    }
	function search(){
		$url = $this->buildUrlOfResultsPage();
		$this->setRedirect(JRoute::_($url, false));
	}
	
	function shareurl(){
		$url = $this->buildUrlOfResultsPage();
		echo JURI::base().substr(JRoute::_($url, false), strlen(JURI::base(true)) + 1);
		exit();
	}
	
	function buildUrlOfResultsPage(){
		$jinput = JFactory::getApplication()->input;
		$post = $jinput->get->post->getArray();
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_jak2fiter&view=search');

		if(isset($items[0])) {
			$post['Itemid'] = $items[0]->id;
		} elseif ($jinput->getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
			$post['Itemid'] = $jinput->getInt('Itemid');
		}
		unset($post['task']);
		unset($post['btnSubmit']);
		$uri = JURI::getInstance();
		//$uri->setQuery($post);
		$uri->setVar('option', 'com_jak2filter');
		$uri->setVar('view', 'itemlist');

		$swr = isset($post['swr']) ? (int) $post['swr'] : 0;//Search in whole slider range
		unset($post['swr']);

		foreach($post AS $key=>$value) {
			if(strpos($key, '_jacheck') !== false) {
				continue;
			}
			if(!$swr) {
				if(isset($post[$key.'_jacheck']) && $post[$key.'_jacheck'] == $value) {
					//for checking slider range field is selected or not
					continue;
				}
			}
			if($key == 'catMode' && (!isset($post['category_id']) || empty($post['category_id']))) {
				continue;
			}
			if(!empty($value)) {
				$uri->setVar($key, $value);
			}
		}
		$url = 'index.php'.$uri->toString(array('query', 'fragment'));
		$url = str_replace(array('<', '>'), array('',''), $url);
		
		return $url;
	}

	function extraFields()
	{
		$k2Path = JPATH_ADMINISTRATOR . '/components/com_k2/';

		JTable::addIncludePath($k2Path.'tables');
		require_once $k2Path . 'models/model.php';
		require_once $k2Path . 'models/category.php';
		require_once $k2Path . 'models/extrafield.php';

		$mainframe = JFactory::getApplication();
		$itemID = $mainframe->input->getInt('id', NULL);
		//$categoryModel = $this->getModel('category');
		$categoryModel = new K2ModelCategory();
		$category = $categoryModel->getData();
		//$extraFieldModel = $this->getModel('extraField');
		$extraFieldModel = new K2ModelExtraField();
		$extraFields = $extraFieldModel->getExtraFieldsByGroup($category->extraFieldsGroup);

		$output = '<table class="admintable" id="extraFields">';
		$counter = 0;
		if (count($extraFields))
		{
			foreach ($extraFields as $extraField)
			{

				if ($extraField->type == 'header')
				{
					$output .= '<tr><td colspan="2" ><h4 class="k2ExtraFieldHeader">'.$extraField->name.'</h4></td></tr>';
				}
				else
				{
					$output .= '<tr><td align="right" class="key"><label for="K2ExtraField_'.$extraField->id.'">'.$extraField->name.'</label></td>';
					$html = $extraFieldModel->renderExtraField($extraField, $itemID);
					if($extraField->type == 'textfield') {
						$html2 = $this->renderField($itemID, $extraField);
						if($html2) {
							$html = $html2;
						}
					}
					$output .= '<td>'.$html.'</td></tr>';
				}
				$counter++;
			}
		}
		$output .= '</table>';

		if ($counter == 0)
			$output = JText::_('K2_THIS_CATEGORY_DOESNT_HAVE_ASSIGNED_EXTRA_FIELDS');

		echo $output;

		$mainframe->close();
	}

	protected function renderField($itemID, $extraField, $config = array('filter.published' => array(0, 1))) {
		$extension = 'com_jak2filter';
		$defaultValues = json_decode($extraField->value);
		$default = $defaultValues[0];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.lft, a.rgt')
			->from('#__categories AS a')
			->where('a.parent_id = 1')
			->where('extension = ' . $db->quote($extension))
			->where($db->quoteName('alias').' = ' . $db->quote($default->alias));
		$db->setQuery($query);
		$cat = $db->loadObject();
		if($cat) {
			$config = (array) $config;
			$items = $this->getOptions($extension, $cat, $config);

			$options = array();
			foreach ($items as $item) {
				$options[] = JHtml::_('select.option', $item->id, $item->title);
			}
			$active = $default->value;

			if (!is_null($itemID)) {
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($itemID);
				if($item) {
					$currentValues = json_decode($item->extra_fields);
					if (count($currentValues))
					{
						foreach ($currentValues as $value)
						{
							if ($value->id == $extraField->id)
							{
								$active = $value->value;
							}
						}
					}
				}
			}
			return JHtml::_('select.genericlist', $options, 'K2ExtraField_'.$extraField->id, '', 'value', 'text', $active, 'K2ExtraField_'.$extraField->id);
		} else {
			return false;
		}
	}

	public function getOptions($extension, $parent = null, $config = array('filter.published' => array(0, 1)))
	{
		$config = (array) $config;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level')
			->from('#__categories AS a')
			->where('a.parent_id > 0');

		// Filter on extension.
		$query->where('extension = ' . $db->quote($extension));

		// Filter on parent.
		if ($parent)
		{
			$query->where('a.lft > ' . (int) $parent->lft);
			$query->where('a.rgt < ' . (int) $parent->rgt);
		}
		// Filter on the published state
		if (isset($config['filter.published']))
		{
			if (is_numeric($config['filter.published']))
			{
				$query->where('a.published = ' . (int) $config['filter.published']);
			}
			elseif (is_array($config['filter.published']))
			{
				JArrayHelper::toInteger($config['filter.published']);
				$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
			}
		}

		// Filter on the language
		if (isset($config['filter.language']))
		{
			if (is_string($config['filter.language']))
			{
				$query->where('a.language = ' . $db->quote($config['filter.language']));
			}
			elseif (is_array($config['filter.language']))
			{
				foreach ($config['filter.language'] as &$language)
				{
					$language = $db->quote($language);
				}

				$query->where('a.language IN (' . implode(',', $config['filter.language']) . ')');
			}
		}

		$query->order('a.lft');

		$db->setQuery($query);
		$items = $db->loadObjectList();
		foreach ($items as &$item)
		{
			$repeat = ($item->level - 2 >= 0) ? $item->level - 2 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;
		}

		return $items;
	}
}
