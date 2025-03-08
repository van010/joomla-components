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
//No direct to access this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;


jimport('joomla.filesystem.folder');

class JaMegafilterHelper
{
	static function getSupportedComponentList() {
		if (Folder::exists(JPATH_PLUGINS.'/jamegafilter/')) {
			$path = JPATH_PLUGINS.'/jamegafilter/';
			$folders = Folder::folders($path);
			return $folders;
		}
		
		return array();
	}
	
	static function getComponentStatus($component)
	{
		$db = Factory::getDbo();
		$q = 'select enabled from #__extensions where type="component" and element = "'.$component.'"';
		$db->setQuery($q);
		$status = $db->loadResult();
		if($status) {
			return true;
		} else {
			return false;
		}
	}
	
	static function hasMegafilterModule() {
		$template = Factory::getApplication()->getTemplate();
		$file = JPATH_SITE . '/templates/' . $template . '/templateDetails.xml';
		$xml = simplexml_load_file($file); 	
		$positions = array();
		foreach	($xml->positions->children() as $p) {
			$positions[] = (string) $p;
		}
		
		$modules = ModuleHelper::getModuleList();
		$i = 0;
		foreach ($modules as $module) {
			if ($module->module === 'mod_jamegafilter' && $module->menuid > 0 ) {
				$i++;
			}
		}
		return $i;
	}
	
	static function addSubmenu($vName)
	{
		if (version_compare(JVERSION, '4.0', '>=')){
			Sidebar::addEntry(
				Text::_('COM_JAMEGAFILTER_FILTERS'),
				'index.php?option=com_jamegafilter&view=defaults',
				$vName == 'defaults'
			);
			Sidebar::addEntry(
				Text::_('COM_JAMEGAFILTER_CRON'),
				'index.php?option=com_jamegafilter&view=cron',
				$vName == 'cron'
			);
		}else{
			self::submenuJ3($vName);
		}
	}
	
	public static function submenuJ3($vName){
		JHtmlSidebar::addEntry(
			JText::_('COM_JAMEGAFILTER_FILTERS'),
			'index.php?option=com_jamegafilter&view=defaults',
			$vName == 'defaults'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JAMEGAFILTER_CRON'),
			'index.php?option=com_jamegafilter&view=cron',
			$vName == 'cron'
		);
	}
}