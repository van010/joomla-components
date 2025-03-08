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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

defined('_JEXEC') or die('Restricted access');

class JaMegaFilterModelDefault extends ItemModel
{
	protected $jafields;

  	public function getItem($pk = null){
		$jinput = Factory::getApplication()->input;
    	$id = $jinput->get('id', 1, 'INT');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jamegafilter'));
		$query->where($db->quoteName('id').'='.$id);
		$db->setQuery($query);
		$item = $db->loadAssoc();

		return $item;
	}
}
