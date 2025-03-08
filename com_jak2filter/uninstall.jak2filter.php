<?php
// No direct access
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
*/
defined('_JEXEC') or die();

// Uninstall JAK2Filter component
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
$db = JFactory::getDBO();
$messages = array();

$arrPackages = array("mod_jak2filter","plg_k2_jak2filter");

$eids = array();

foreach ($arrPackages as $package){
	$type = substr($package, 0, 3);
	switch ($type){
		case "mod":
			$db->setQuery("SELECT extension_id, `name` FROM #__extensions WHERE `type` = 'module' AND `element` = '".$package."'");
			$el = $db->loadObject();
			if ($el) {
				$eids[] = $el->extension_id;
				$messages[] = JText::_('Uninstalling module "'.$el->name.'" was successful.');
			}

			break;
		case "plg":
			$info = explode("_", $package);
			if (count($info) >= 3) {
				$info[2] = str_replace($info[0]."_".$info[1]."_", "", $package);
				$db->setQuery("SELECT extension_id, `name` FROM #__extensions WHERE `type` = 'plugin' AND `element` = '".$info[2]."' AND `folder` = '".$info[1]."' ");
				$el = $db->loadObject();
				if ($el) {
					$eids[] = $el->extension_id;
					$messages[] = JText::_('Uninstalling plugin "'.$el->name.'" was successful.');
				}
			}

			break;
	}
}

if (count($arrPackages) > 0 && count($eids) > 0) {
	$model = new InstallerModelManage();
	$result = $model->remove($eids);
	if (! $result) {
		$messages = array('Uninstalling modules and plugins were not successful!');
	}
}
?>
 <div style="text-align:left;">
  	<table width="100%" border="0" style="line-height:200%; font-weight:bold;">
	    <tr>
	      <td align="center">
	      		Uninstalling JA K2 Filter
				<?php
				if (count($messages) > 1) {
					echo ' and all related modules, plugins were';
				}
				else {
					echo ' was';
				}
				echo ' successful.<br />';

				echo implode("<br />", $messages);
				?>
				<br />
	      </td>
	    </tr>
    </table>
 </div>
