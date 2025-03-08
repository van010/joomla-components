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

defined('_JEXEC') or die;

// Try extending time, as unziping/ftping took already quite some... :
@set_time_limit( 0 );


class Com_jalangInstallerScript
{
	function postflight($type, $parent) {
		$messages = array();

		// Import required modules
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');
		jimport('joomla.filesystem.file');
		
	}

	public function install($parent)
	{
		//enable Language Filter plugin
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions')->set(''.$db->quoteName('enabled').' = 1')
			->where(array(''.$db->quoteName('type').'='.$db->quote('plugin'), ''.$db->quoteName('element').'='.$db->quote('languagefilter'), ''.$db->quoteName('folder').'='.$db->quote('system')));
		$db->setQuery($query);
		$db->execute();
	}
	
	public function uninstall($parent){
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');	
	}
}