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
defined('_JEXEC') or die ;

class JAK2FilterHelperUpgrade
{

	function checkUpdate() {
		jimport('joomla.filesystem.file');
		$dbPath = JPATH_ADMINISTRATOR.'/components/com_jak2filter/installer/sql/';

		/**
		 * Example code for upgrade to version x.x.x
		 */
		$versions = array("1.0.2", "1.0.7", "1.0.9", "1.1.5", "1.2.4");
		$runIndexing = false;
		foreach ($versions as $version) {
			$check = dirname(__FILE__)."/updated_{$version}.log";
			if (!JFile::exists($check)) {
				if($version == "1.1.5") {
					$runIndexing = true;
				}
				//processing code here
				$file = $dbPath.'upgrade_v'.$version.'.sql';
				$this->parseSQLFile($file);
				//end of update code

				$flag = 'Updated at '.date('Y-m-d H:i:s');
				JFile::write($check, $flag);
			}
		}
		if($runIndexing) {
			$helper = new JAK2FilterHelper();
			$message = $helper->indexingData('upgrade');
		}
	}

	function parseSQLFile($file) {
		jimport('joomla.filesystem.file');

		try {
			if(JFile::exists($file)) {
				$buffer = JFile::read($file);
				if($buffer) {
					$db = JFactory::getDbo();
					$queries = $db->splitSql($buffer);
					foreach ($queries as $query) {
						$query = trim($query);
						if(empty($query)) continue;

						$db->setQuery($query);
						@$db->query();
					}
				}
			}
		} catch(Exception $e) {
			//echo $e->getMessage();
		}
	}

}