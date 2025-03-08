<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.helper');

class JAK2FilterHelperRoute
{
	private static $anyK2Link = null;
	private static $multipleCategoriesMapping = array();
	private static $tree = null;
	private static $model = null;
	private static $cache = array(
		'item' => array(),
		'category' => array(),
		'user' => array(),
		'tag' => array()
	);

	public static function getCategoryRoute($catid, $isc = 1)
	{
		$key = (int)$catid;
		if (isset(self::$cache['category'][$key]))
		{
			return self::$cache['category'][$key];
		}
		$link = 'index.php?option=com_jak2filter&view=itemlist&category_id='.$catid;
		if($isc) $link.= '&isc='.$isc;
		if ($item = JAK2FilterHelperRoute::_findItem((int)$catid))
		{
			$link .= '&Itemid='.$item->id;
		}
		self::$cache['category'][$key] = $link;
		return $link;
	}

	public static function _findItem($catid)
	{
		$component = JComponentHelper::getComponent('com_jak2filter');
		$application = JFactory::getApplication();
		$menus = $application->getMenu('site', array());
		if (K2_JVERSION != '15')
		{
			$items = $menus->getItems('component_id', $component->id);
		}
		else
		{
			$items = $menus->getItems('componentid', $component->id);
		}
		$match = null;

		if (count($items))
		{
			foreach ($items as $item)
			{
				if($catid) {
					if (@$item->query['view'] == 'itemlist' && @$item->query['category_id'] == $catid) {
						$match = $item;
						break;
					}
				} else {
					if (@$item->query['view'] == 'itemlist' && !isset($item->query['category_id'])) {
						$match = $item;
						break;
					}
				}
			}
		}

		if (is_null($match))
		{
			// Try to detect any parent category menu item....
			if (is_null(self::$tree))
			{
				include_once JPATH_ADMINISTRATOR . '/components/com_k2/models/model.php';
				K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
				$model = K2Model::getInstance('Itemlist', 'K2Model');
				self::$model = $model;
				self::$tree = $model->getCategoriesTree();
			}
			$parents = self::$model->getTreePath(self::$tree, $catid);
			if (is_array($parents))
			{
				foreach ($parents as $categoryID)
				{
					if ($categoryID != $catid)
					{
						$match = JAK2FilterHelperRoute::_findItem($categoryID);
						if (!is_null($match))
						{
							break;
						}
					}
				}
			}

			if (is_null($match) && !is_null(self::$anyK2Link))
			{
				$match = self::$anyK2Link;
			}
		}

		return $match;
	}

}
