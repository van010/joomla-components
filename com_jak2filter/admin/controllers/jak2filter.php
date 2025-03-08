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

jimport('joomla.application.controller');

/**
 * Methods supporting a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_jak2filter
 * @since		1.6
 */
class JAK2FilterControllerJak2filter extends JAK2FilterController
{

	function extraFields()
	{
		$k2Path = JPATH_ADMINISTRATOR . '/components/com_k2/';

		JTable::addIncludePath($k2Path.'tables');
		require_once $k2Path . 'models/model.php';
		require_once $k2Path . 'models/category.php';
		require_once $k2Path . 'models/extrafield.php';

		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		$itemID = $jinput->getInt('id', NULL);
		//$categoryModel = $this->getModel('category');
		$categoryModel = new K2ModelCategory();
		$category = $categoryModel->getData();
		//$extraFieldModel = $this->getModel('extraField');
		$extraFieldModel = new K2ModelExtraField();
		$extraFields = $extraFieldModel->getExtraFieldsByGroup($category->extraFieldsGroup);

		$output = '<div id="extraFields">';
		$counter = 0;
		if (count($extraFields))
		{
			foreach ($extraFields as $extraField)
			{

				if ($extraField->type == 'header')
				{
					$output .= '<div class="itemAdditionalField">
							<div class="k2Right k2FLeft itemAdditionalValue"> </div>
							<div class="itemAdditionalData"><h4 class="k2ExtraFieldHeader">'.$extraField->name.'</h4></div>
						</div>';
				}
				else
				{
					$output .= '<div class="itemAdditionalField"><div class="k2Right k2FLeft itemAdditionalValue">
									<label for="K2ExtraField_'.$extraField->id.'">'.$extraField->name.'</label>
								</div>';
					$html = $extraFieldModel->renderExtraField($extraField, $itemID);
					if($extraField->type == 'textfield') {
						$html2 = $this->renderField($itemID, $extraField);
						if($html2) {
							$html = $html2;
						}
					}
					$output .= '<div class="itemAdditionalData">
									'.$html.'
								</div></div>';
				}
				$counter++;
			}
		}
		$output .= '</div>';

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
