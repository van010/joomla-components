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


defined('_JEXEC') or die;

/**
 * Suggestions JSON controller for Extral Fields with format is textfield..
 *
 * @subpackage  com_jak2filter
 * @since       2.5
 */
class JAK2FilterControllerSuggestions extends JAK2FilterController
{
	/**
	 * Method to find search query suggestions.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$return = array();
		
		// Get the suggestions.
		$model=$this->getModel('Suggestions','JAK2FilterModel');
		$return = $model->getData();
		
		// Check the data.
		if (empty($return))
		{
			$return = array();
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
}