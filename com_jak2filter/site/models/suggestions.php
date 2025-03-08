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

jimport('joomla.application.component.model');

class JAK2FilterModelSuggestions extends JAK2FilterModel
{
    function getData()
    {
        $db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$jinput = $app->input;

		$xf_id = $jinput->getInt('xf_id', 0);
		$module_id = $jinput->getInt('m_id', 0);
		$xf_value = $jinput->getString('auto_'.$module_id.'_xf_'.$xf_id.'_txt', '');
		
		$data = new stdClass();
		$data->suggestions = array();
		// SELECT FROM TAXONOMY INDEX
		if($xf_id){
			$query = "
					SELECT `labels` 
					FROM #__jak2filter_taxonomy 
					WHERE LOWER(`labels`) LIKE '%".strtolower($xf_value)."%'
					AND `asset_id` = {$xf_id}
					GROUP BY `labels`
					";

			$db->setQuery($query);
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
			   $items = $db->loadColumn(0);
			}
			else
			{
			   $items = $db->loadResultArray(0);
			}
			
			$data->suggestions = array_values($items);
		}
		
        return $data;
    }
	
	function getExtraFieldIds($type='', $id=0)
	{
		$db = JFactory::getDbo();
		$ids = null;
		$query = "SELECT id FROM #__k2_extra_fields WHERE `published` = 1 ";
		if($type){
			$query .= " AND `type` LIKE '".$type."' "; 
		}
		$db->setQuery($query);
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
		   $ids = $db->loadColumn(0);
		}
		else
		{
		   $ids = $db->loadResultArray(0);
		}
		
		return $ids;
	}
}
