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

class K2ElementExtraFields extends K2Element
{

	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$query = "
			SELECT f.id, f.name AS fname, f.group, f.type, f.published, g.name AS gname
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON g.id = f.group
			WHERE f.published = 1
			AND f.type <> 'csv'
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
		//adding custom style for setting form in Joomla 3.x
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(JURI::base(true).'/components/com_jak2filter/elements/style_3x.css');
		}
		if( JFile::exists(JPATH_ROOT.'/components/com_k2/k2.php')){
			// Initialize variables.
			$html = array();

			// Initialize some field attributes.
			$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

			// Start the checkbox field output.
			$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

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
				if ($k == 0)
				{
					$html[] = '<h4 class="jagroup">'.JText::_('JAK2FILTER_EXTRA_FIELDS_GROUP').$g.'</h4>';
				}
				else
				{
					$html[] = '<br/><h4 class="jagroup">'.JText::_('JAK2FILTER_EXTRA_FIELDS_GROUP').$g.'</h4>';
				}

				foreach($options as $i => $option)
				{
					if($option->group == $key)
					{
						// Initialize some option attributes.
						$checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
						$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
						$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
						// Initialize some JavaScript option attributes.
						$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

						$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
							. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
						$html[] = '<label ' . $class . ' for="' . $this->id . $i . '" style="cursor: pointer;">&nbsp;' . JText::_($option->text) . '</label><br />';
					}
				}

				$k++;
			}

			// End the checkbox field output.
			$html[] = '</fieldset>';

			return implode($html);
		}
		return;

	}

}

class JFormFieldExtraFields extends K2ElementExtraFields
{
    var $type = 'extrafields';
}

class JAElementExtraFields extends K2ElementExtraFields
{
    var $_name = 'extrafields';
}
