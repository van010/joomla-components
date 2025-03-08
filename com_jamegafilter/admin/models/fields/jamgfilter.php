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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

if (version_compare(JVERSION, '4.0', '>=')){
	class JFormFieldJamgfilter extends ListField
	{
		protected $type = 'Jamgfilter';
		
		protected function getOptions() {
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array('title as text', 'id as value'))
				->from($db->quoteName('#__jamegafilter'))
				->where('published=1');
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		
	}
}else{
	class JFormFieldJamgfilter extends JFormFieldList
	{
		protected $type = 'Jamgfilter';
		
		protected function getOptions() {
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array('title as text', 'id as value'))
				->from($db->quoteName('#__jamegafilter'))
				->where('published=1');
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		
	}
}