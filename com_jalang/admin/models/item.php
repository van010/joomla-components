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
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
/**
 * Methods supporting a list of article records.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
class JalangModelItem extends AdminModel
{
	public function getItem($pk = null)
	{
		$input = Factory::getApplication()->input;
		$id = $input->getInt('id');
		if(!$id) return false;

		$adapter = JalangHelper::getHelperContent();
		if(!$adapter) return false;

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__'.$adapter->table)->where($db->quoteName($adapter->primarykey).'='.$id);

		$db->setQuery($query);
		$row = $db->loadObject();

		//get reference value
		if(count($adapter->reference_fields)) {
			foreach($adapter->reference_fields as $field => $table) {
				$adapter2 = JalangHelperContent::getInstance($table);
				if($adapter2) {
					$query = $db->getQuery(true);
					$query->select('*')->from('#__'.$adapter2->table)->where($db->quoteName($adapter2->primarykey).'='.$row->{$field});
					$db->setQuery($query);
					$row2 = $db->loadObject();
					if($row2) {
						$row->{$field.'_ref'} = $row2->{$adapter2->title_field};
					}
				}
			}
		}

		return $row;
	}

	public function getForm($data = array(), $loadData = true)
	{
		//@to_do generate form
	}
}
