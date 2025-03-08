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
use Joomla\CMS\Form\FormField;

jimport('joomla.form.formfield');

abstract class JFormFieldJamegafilter_filterfields extends FormField {

	protected $type = 'jamegafilter_filterfields';
	protected $catOrdering = false;
  
	function setWidth(){
		if (version_compare(JVERSION, '4', 'ge')){
		return 'style="width:180px"';
		}
		return '';
	}
  
	//===============================
	// Page options
	//===============================
	function getLayoutInput() {
		$jinput = Factory::getApplication()->input;
		$id = $jinput->get('id', 0, 'INT');
		$type = $jinput->get('type', '', 'STRING');
		$plg = array('content', 'k2', 'virtuemart', 'docman');
		if (!empty($id)) {
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				  ->from($db->quoteName('#__jamegafilter'))
				  ->where('published=1 AND id ='.$id);
			$db->setQuery($query);
			$item = $db->loadObject();
			$type = !empty($item->type) ? $item->type : NULL;
		}

		$html = '';
		$filterfields = $this->getFilterFields();
		$left = '';
		$right = '';
		$layout_addition = !empty($this->value['layout_addition']) ? explode(',', $this->value['layout_addition']) : false;
		$columns = !empty($this->value['jacolumn']) ?$this->value['jacolumn'] : false;
		// custom layout input. left column
		if (empty($layout_addition) || !in_array('thumb', $layout_addition)) {
			$filterfields['basefield'][] = array(
				"field"=> "thumb",
				"title"=> Text::_("COM_JAMEGAFILTER_THUMB"),
			);
		}
		if (in_array($type, $plg)) {
			if (empty($layout_addition) || !in_array('desc', $layout_addition)) {
				$filterfields['basefield'][] = array(
					"field"=> "desc",
					"title"=> Text::_("COM_JAMEGAFILTER_DESC"),
				);
			}
		}

		if (in_array($type, ['virtuemart'])) { // support base price only for VM
			if (empty($layout_addition) || !in_array('baseprice', $layout_addition)) {
				$filterfields['basefield'][] = array(
					"field"=> "baseprice",
					"title"=> Text::_("COM_JAMEGAFILTER_BASE_PRICE"),
				);
			}
		}

		foreach ($filterfields as $key => $fieldgroups) {
			if (!empty($fieldgroups)) {
				foreach ($fieldgroups as $field) {
					$fi = str_replace('.', '_', $field['field']);
					$val = empty($columns[$field['field']]) ? 0 : 1;
					if (empty($columns))
						$val=1; // default
					if (empty($field['showoff']))
						$left .= $this->liline($key, $fi, $field, $val);
					if (!$layout_addition && !empty($field['showoff'])) // in case first time runner.
						$right .= $this->liline($key, $fi, $field, $val);
				}
			}
		}
		
		// custom layout input. right column
		if (!empty($layout_addition) && in_array('thumb', $layout_addition)) {
			$filterfields['basefield'][] = array(
				"field"=> "thumb",
				"title"=> Text::_("COM_JAMEGAFILTER_THUMB"),
			);
		}
		if (in_array($type, $plg)) {
			if (!empty($layout_addition) && in_array('desc', $layout_addition)) {
				$filterfields['basefield'][] = array(
					"field"=> "desc",
					"title"=> Text::_("COM_JAMEGAFILTER_DESC"),
				);
			}
		}
		if (in_array($type, ['virtuemart'])) { // support base price only for VM
			if (!empty($layout_addition) && in_array('baseprice', $layout_addition)) {
				$filterfields['basefield'][] = array(
					"field"=> "baseprice",
					"title"=> Text::_("COM_JAMEGAFILTER_BASE_PRICE"),
				);
			}
		}

		if (!empty($layout_addition)) // after save runner.
			foreach ($layout_addition AS $la) {
				foreach ($filterfields as $key => $fieldgroups) {
					if (!empty($fieldgroups)) {
						foreach ($fieldgroups as $field) {
							if ($la === $field['field']) {
								$fi = str_replace('.', '_', $field['field']);
								$val = empty($columns[$field['field']]) ? 0 : 1;
								if (empty($columns))
									$val=1; // default
								$right .= $this->liline($key, $fi, $field, $val);
							}
						}
					}
				}
			}

		$html .= '<div class="left">';
		$html .= '<div class="block-inner">';
			$html .= '<div class="block-title">'.Text::_('COM_MEGAFILTER_DEACTIVE_LIST').'</div>';
			$html .= '<ul id="sortable1" class="connectedSortable">';
			$html .= $left;
			$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="right">';
		$html .= '<div class="block-inner">';
			$html .= '<div class="block-title">'.Text::_('COM_MEGAFILTER_ACTIVE_LIST').'</div>';
			$html .= '<ul id="sortable2" class="connectedSortable">';
			$html .= $right;
			$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	
	function liline($key, $fi, $field, $val) {
		return '<li class="ui-state-default" data-jfield="'.$field['field'].'"><div class="field-title"><span>'
      .Text::_('COM_JAMEGAFILTER_'.strtoupper($key)).'</span> '.($field['title'])
      .'</div> <div class="field-title-option">'.Text::_('COM_MEGAFILTER_SHOW_TITLE').' '
      .$this->layoutFieldset($fi, $field['field'], $val).'</div>';
	}

	function layoutFieldset($el, $name, $val) {
		return '<fieldset id="jform_params_'.$el.'">
			<div class="btn-group btn-group-yesno radio">
        <input class="btn-check" type="radio" id="jform_params_'.$el.'0" name="jform[filterfields][jacolumn]['.$name.']" value="1" '.($val==1 ? ' checked="checked" ' : '').'>
				<label for="jform_params_'.$el.'0" class="btn btn-outline-success">'.Text::_('JYES').'</label>
        <input class="btn-check" type="radio" id="jform_params_'.$el.'1" name="jform[filterfields][jacolumn]['.$name.']" value="0" '.($val==0 ? ' checked="checked" ' : '').'>
				<label for="jform_params_'.$el.'1" class="btn btn-outline-danger">'.Text::_('JNO').'</label>
      	</div>
		</fieldset>';
	}

	protected function getInput() {
		$filterfields = $this->getFilterFields();
		$html = '';
		$html .= '<div id="tabs">';
		$html .= '<ul>';
		foreach ($filterfields as $key => $fieldgroups) {
			if (!empty($fieldgroups)) {
				$html .= '<li><a class="filter-field" href="#' . $key . '"><span class="icon-menu"></span>' . Text::_("COM_JAMEGAFILTER_" . strtoupper($key)) . '</a></li>';
			}
		}
		$html .= '<li><a href="#filterconfig"><span class="icon-stop"></span>'.Text::_('COM_MEGAFILTER_FILTER_CONFIG').'</a></li>';
		$html .= '<li><a href="#layoutconfig"><span class="icon-pause"></span>'.Text::_('COM_MEGAFILTER_LAYOUT_CONFIG').'</a></li>';
		$html .= '</ul>';
		$t = 0;
		$u = 0;

		// add sort by radio. BEWARE fields set sort by and on change sort by radio.
		$sort_by = !empty($this->value['sort_by']) ? $this->value['sort_by'] : 'desc';
		$layout_addition = !empty($this->value['layout_addition']) ? $this->value['layout_addition'] : '';
		
		$html .= '
		<input type="radio" id="jform_params_sort_by0" class="sort_by_input hidden" name="jform[filterfields][sort_by]" value="asc" '.($sort_by == 'asc' ? ' checked="checked" ' : '').'>
		<input type="radio" id="jform_params_sort_by1" class="sort_by_input hidden" name="jform[filterfields][sort_by]" value="desc" '.($sort_by == 'desc' ? ' checked="checked" ' : '').'>
		<input type="hidden" id="jform_params_layout_addition" class="" name="jform[filterfields][layout_addition]" value="'.$layout_addition.'" />

		<!-- Custom layout input 1 column field-->
		<input type="hidden" class="layout-addition" data-jfield="name" name="jform[filterfields][basefield]['.(count($filterfields['basefield'])).'][showoff]" value="1">
		<input type="hidden" name="jform[filterfields][basefield]['.(count($filterfields['basefield'])).'][field]" value="thumb">

		<!-- Custom layout input 2 columns field -->
		<input type="hidden" class="layout-addition" data-jfield="name" name="jform[filterfields][basefield]['.(count($filterfields['basefield'])+1).'][showoff]" value="1">
		<input type="hidden" name="jform[filterfields][basefield]['.(count($filterfields['basefield'])+1).'][field]" value="desc">
		';

		foreach ($filterfields as $key => $fieldgroups) {
			if (!empty($fieldgroups)) {
				$html .= '<div style="display:none" id="' . $key . '">';
				$html .= '<table class="table">';
				$html .= '<thead>
							<tr>
								<th>' . Text::_('COM_JAMEGAFILTER_PUBLISHED') . '</th>
								<th>' . Text::_('COM_JAMEGAFILTER_NAME') . '</th>
								<th>' . Text::_('COM_JAMEGAFILTER_TITLE') . '</th>
								<th>' . Text::_('COM_JAMEGAFILTER_FILTER_TYPE') . '</th>
								<th>' . Text::_('COM_JAMEGAFILTER_SORT_BY') . '</th>
								<th class="sortby">
									<fieldset class="btn-group btn-group-yesno radio">
										<label title="'.Text::_('COM_JAMEGAFILTER_DEFAULT_SORT_BY').'" for="jform_params_sort_by0" class="btn btn-asc '.($sort_by == 'asc' ? ' active btn-success ' : '').'">ASC</label>
										<label title="'.Text::_('COM_JAMEGAFILTER_DEFAULT_SORT_BY').'" for="jform_params_sort_by1" class="btn btn-desc '.($sort_by == 'desc' ? ' active btn-success ' : '').'">DESC</label>
									</fieldset>
								</th>
							</tr>
						</thead>';
				$html .= '<tbody>';
				foreach ($fieldgroups as $field) {
					$html .= '<tr>';

					$html .= '<td>';
					$publish = $field['published'] ? 'publish' : 'unpublish';
					$html .= '<a class="btn btn-micro" input="" href="javascript:void(0);" onclick="return publish_item(this)"><span data-jfield="'.$field['field'].'" class="icon-' . $publish . '"></span></a>';
					$html .= '<input type="hidden" name="jform[filterfields][' . $key . '][' . $t . '][published]" value="' . $field['published'] . '">';
					$html .= '</td>';

					$html .= '<input type="hidden" class="layout-addition" data-jfield="'.$field['field'].'" name="jform[filterfields][' . $key . '][' . $t . '][showoff]" value="' . ((!empty($field['showoff'])) ? $field['showoff'] : 0) . '">';
					$html .= '<input type="hidden" name="jform[filterfields][' . $key . '][' . $t . '][field]" value="' . $field['field'] . '">';

					$html .= '<td><label for="filterfield_' . $key . '_' . str_replace('.', '_', $field['field']) . '" >' . $field['name'] . '</label>';
					$html .= '<input type="hidden" id="filterfield_' . $key . '_' . preg_replace('/[^\w]+/', '_', $field['title'])
            			. '" class="" name="jform[filterfields][' . $key . '][' . $t . '][raw_name]" type="text" value="' . $field['name'] . '">';
					$html .= '</td>';

					$html .= '<td><input id="filterfield_' . $key . '_' . str_replace('.', '_', $field['field'])
            			. '" class="inputbox form-control" name="jform[filterfields][' . $key . '][' . $t . '][title]" type="text" value="' . $field['title'] . '" required></td>';
					$html .= '<td>';
					$html .= '<select class="form-select form-select-color-state form-select-success valid form-control-success" '
            			. $this->setWidth() .' name="jform[filterfields][' . $key . '][' . $t . '][type]">';

					foreach ($field['filter_type'] as $type) {
						$selectd = !empty($field['type']) && $field['type'] === $type ? 'selected' : '';
						$html .= '<option value="' . $type . '" ' . $selectd . '>' . ucfirst($type) . '</option>';
					}

					$html .= '</select>';
					$html .= '</td>';

					$html .= '<td>';
					$sort = $field['sort'] ? 'publish' : 'unpublish';
					$html .= '<a class="btn btn-micro" input="" href="javascript:void(0);" onclick="return publish_item(this)"><span class="icon-' . $sort . '"></span></a>';
					$html .= '<input type="hidden" name="jform[filterfields][' . $key . '][' . $t . '][sort]" value="' . $field['sort'] . '">';
					$html .= '</td>';

					$html .= '<td>';
					// got warning message because we do not define in plugin field type. change this code if we add to plugin.
					$sort_default = 'delete';
					$value=0;
					if (!empty($field['sort_default']))  {
						$sort_default = 'publish';
						$value = $field['sort_default'];
					}
					
					$html .= '<a class="btn btn-micro btn-sort_default" title="'.Text::_('COM_JAMEGAFILTER_DEFAULT_SORT').'" input="" href="javascript:void(0);" onclick="return default_sort(this)"><span class="icon-'.$sort_default.'"></span><input type="hidden" class="sort_default-input" name="jform[filterfields][' . $key . '][' . $t . '][sort_default]" value="' . $value . '"></a>';
					$html .= '</td>';
					$html .= '</tr>';
					$t++;
				}

				$html .= '</tbody>';
				$html .= '</table>';
				$html .= '</div>';
				$u++;
			}
		}
  
		$html .= '<div style="display:none" id="layoutconfig">';
		$html .= $this->getLayoutInput();
		$html .= '</div>';
    
		//===============================
		// Filter config
		//===============================
		$html .= '<div style="display:none" id="filterconfig">';
		$html .= '<p>'.Text::_('COM_JAMEGAFILTER_FILTER_CONFIG_DESCRIPTION').'</p>';
		$html .= $this->getFilterInput($filterfields);
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	function getFilterInput($filterfields) {
		$filter_order = array();
		if (!empty($this->value['filter_order']))
			$filter_order = $this->value['filter_order'];
		$html = '';
		$t = 0;
		$html .= '<table class="table">';
		$html .= '<thead>
					<tr>
						<th><span class="icon-menu-2"></span></th>
						<th>' . Text::_('COM_JAMEGAFILTER_NAME') . '</th>
						<th>' . Text::_('COM_JAMEGAFILTER_OPTION_FILTER') . '</th>
					</tr>
				</thead>';
		$html .= '<tbody>';
		$html .= $this->filterOrderLayout($filterfields, $filter_order);
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div>';

		return $html;
	}

	function filterOrderLayout($filterfields, $filter_order=array()) {
		$html = '';
		$filter_options = [
			'name_asc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_NAME_ASC'), 
			'name_desc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_NAME_DESC'), 
			'number_asc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_NUMBER_ASC'),
			'number_desc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_NUMBER_DESC'),
			'ordering_asc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_ORDERING_ASC'),
			'ordering_desc' => Text::_('COM_JAMEGAFILTER_FILTER_OPTIONS_ORDERING_DESC')
		];
		$listField = array();
		foreach ($filterfields as $key => $group) {
			foreach ($group as &$item) {
				$item['group'] = $key;
				$listField[] = $item;
			}
		}

		$sorting = isset($filter_order['sort']) ? $filter_order['sort'] : array();
		$ordering = isset($filter_order['order']) ? $filter_order['order'] : array();

		usort($listField, function($a, $b) use ($sorting) {
			$keyA = array_search($a['field'], $sorting);
			$keyB = array_search($b['field'], $sorting);

			if ($keyA === $keyB) {
				return 0;
			}

			return $keyA < $keyB ? -1 : 1;
		});

		$html = '';
		foreach ($listField as $field) {
			$html .= '<tr data-jfield="'.$field['field'].'">';
			$html .= '<td><span class="icon-menu large-icon"> </span></td>';

			$html .= '<input type="hidden" name="jform[filterfields][filter_order][sort][]" value="' . $field['field'] . '">';

			$html .= '<td><div class="field-title"><span>'.Text::_('COM_JAMEGAFILTER_'.strtoupper($field['group'])).'</span> '.($field['title']).'</div></td>';

			$html .= '<td>';
			$disabled = '';

			if (!in_array('single', $field['filter_type']) 
				&& !in_array('dropdown', $field['filter_type'])
				&& !in_array('color', $field['filter_type'])
				&& !in_array('multiple', $field['filter_type'])
				|| ($field['field'] === 'attr.cat.value' && !$this->catOrdering))
				$disabled = 'disabled';

			$html .= '<select class="form-select form-select-color-state form-select-success valid form-control-success" '
        . $this->setWidth() .$disabled.' name="jform[filterfields][filter_order][order]['.$field['field'].']">';

			$options = $filter_options;
			if (!$this->hasCustomOrdering($field) || $field['field'] === 'attr.featured.value') {
				unset($options['ordering_asc']);
				unset($options['ordering_desc']);
			}

			if ($field['field'] === 'attr.cat.value') {
				unset($options['number_asc']);
				unset($options['number_desc']);
			}

			foreach ($options AS $k => $type) {
				$selected = '';
			
				if (!empty($ordering[$field['field']]) && $ordering[$field['field']] === $k) {
					$selected = 'selected';
				}

				$html .= '<option value="' . $k . '" ' . $selected . '>' . ucfirst($type) . '</option>';
			}

			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
		}

		return $html;
	}

	function getFilterFields() {
		$fieldgroups = $this->getFieldGroups();

		$filterfields = array();

		if (empty($this->value)) {

			$filterfields = $fieldgroups;
		} else {

			foreach ($this->value as $key => $group_value) {

				if (!empty($fieldgroups[$key])) {

					$filterfields[$key] = array();
					foreach ($group_value as $gv) {

						foreach ($fieldgroups[$key] as $k_fg => $fg) {

							if ($gv['field'] == $fg['field']) {

								$field = $gv + $fg;
								array_push($filterfields[$key], $field);

								unset($fieldgroups[$key][$k_fg]);

								break;
							}
						}
					}

					$filterfields[$key] = array_merge($filterfields[$key], $fieldgroups[$key]);

					// unset the same field group
					unset($fieldgroups[$key]);
				}
			}

			$filterfields = array_merge($filterfields, $fieldgroups);
		}

		return $filterfields;
	}

	function hasCustomOrdering($field) {
		return false;
	}

	/**
	 *  @return array
	 */
	abstract function getFieldGroups();
}
