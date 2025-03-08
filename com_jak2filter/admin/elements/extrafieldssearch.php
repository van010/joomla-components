<?php
/**
 * @version		$Id: template.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementExtraFieldsSearch extends K2Element
{

	protected function getOptions()
	{
		$subtype = isset($this->element['subtype']) ? $this->element['subtype'] : 'searchmode';

		switch($subtype) {
			case 'searchmode':
				$types = array('multipleSelect', 'labels');
				break;
			case 'datatype':
				$types = array('select', 'multipleSelect', 'radio', 'labels', 'textfield', 'date');
				break;
			default:
				$types = array('select', 'multipleSelect', 'radio', 'labels');
				break;
		}

		$db = JFactory::getDbo();
		$query = "
			SELECT f.id, f.name AS fname, f.group, f.type, f.published, g.name AS gname
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON g.id = f.group
			WHERE f.published = 1
			AND f.type IN ('".implode("','", $types)."')
			ORDER BY f.group, f.ordering
			";
		$db->setQuery($query);
		$list = $db->loadAssocList();
		// Initialize variables.
		$options = array();

		if(count($list)) {
			foreach ($list as $option)
			{
	
				// Create a new option object based on the <option /> element.
				$tmp = JHtml::_(
					'select.option', $option['id'], $option['fname'], 'value', 'text',
					($option['published'] == 0)
				);
	
				// Set some option attributes.
				$tmp->class = '';
	
				// Set some JavaScript option attributes.
				$tmp->onclick 	= '';
				$tmp->id		= $option['id'];
				$tmp->title		= $option['fname'];
				$tmp->type 		= $option['type'];
				$tmp->group 	= $option['group'];
				$tmp->gname 	= $option['gname'];
	
				// Add the option object to the result set.
				$options[] = $tmp;
			}
		}

		reset($options);

		return $options;
	}
	
	protected function fetchElement()
	{
		if( JFile::exists(JPATH_ROOT.'/components/com_k2/k2.php')){
			$subtype = isset($this->element['subtype']) ? $this->element['subtype'] : 'searchmode';
			// Initialize variables.
			$html = array();

			// Initialize some field attributes.
			$class = @$this->element['class'];

			// Start the checkbox field output.
			$html[] = '<ul class="' . $class . '" style="float:left;">';

			// Get the field options.
			$options = $this->getOptions();

			// Build the checkbox field output.
			$group = 0;
			$groups = array();

			foreach ($options as $i => $option)
			{
				if($group != $option->group)
				{
					$group = $option->group;
					$groups[$option->group] = $option->gname;
				}
			}

			$k = 0;
			foreach($groups AS $key=>$g)
			{
				$html[] = '<li class="jagroup"><label>'.JText::_('JAK2FILTER_EXTRA_FIELDS_GROUP').$g.'</label></li>';

				foreach($options as $i => $option)
				{
					if($option->group == $key)
					{
						$searchOptions = array();
						if($subtype == 'sort') {
							if($option->type != 'labels') {
								$searchOptions[] = JHtml::_('select.option', $option->id.':ordering', JText::_('XFIELD_VALUES_ORDER_DEFAULT'));
							}
							$searchOptions[] = JHtml::_('select.option', $option->id.':alpha', JText::_('XFIELD_VALUES_ORDER_ALPHABETICAL'));
							$searchOptions[] = JHtml::_('select.option', $option->id.':ralpha', JText::_('XFIELD_VALUES_ORDER_REVERSE_ALPHABETICAL'));
							$selected = $option->id.':alpha';
						} elseif($subtype == 'datatype') {
							//data type
							$searchOptions[] = JHtml::_('select.option', $option->id.':string', JText::_('XFIELD_DATATYPE_STRING'));
							$searchOptions[] = JHtml::_('select.option', $option->id.':number', JText::_('XFIELD_DATATYPE_NUMBER'));
							$selected = $option->id.':any';
						} else {
							//search mode
							$searchOptions[] = JHtml::_('select.option', $option->id.':any', JText::_('MATCH_ANY'));
							$searchOptions[] = JHtml::_('select.option', $option->id.':all', JText::_('MATCH_ALL'));
							$selected = $option->id.':any';
						}
						if(is_array($this->value) && count($this->value)) {
							foreach($this->value as $value) {
								if(strpos($value, $option->id.':') === 0) {
									$selected = $value;
									break;
								}
							}
						}

						$html[] = '<li>';
						$html[] = '<label style="background:#FFF; border: 0px none;">'.$option->title.': </label>';
						$html[] = JHtml::_('select.genericlist', $searchOptions, $this->name, '', 'value', 'text', $selected, $this->id.$option->id).'<br/>';
						$html[] = '</li>';
					}
				}

				$k++;
			}

			// End the checkbox field output.
			$html[] = '</ul>';

			return implode($html);
		}
		return;
		
	}
		
}

class JFormFieldExtraFieldsSearch extends K2ElementExtraFieldsSearch
{
    var $type = 'extrafieldssearch';
}

class JElementExtraFieldsSearch extends K2ElementExtraFieldsSearch
{
    var $_name = 'extrafieldssearch';
}
