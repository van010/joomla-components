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

// Try extending time, as unziping/ftping took already quite some...
@set_time_limit(240);
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Install sub packages and show installation result to user
 *
 * @return void
 */
function com_install()
{

  	$messages = array();

	// Import required modules
	jimport('joomla.installer.installer');
	jimport('joomla.installer.helper');
	jimport('joomla.filesystem.file');
	//$db = JFactory::getDBO();
	//$query = "ALTER TABLE #__k2_items ADD FULLTEXT(extra_fields)";
	//$db->setQuery($query);
	//$db->query();
	// Get packages
	$p_dir = JPath::clean(JPATH_SITE.'/components/com_jak2filter/packages');
	// Did you give us a valid directory?
	if (!is_dir($p_dir)){
		$messages[] = JText::_('Package directory(Related modules, plugins) is missing');
	}
	else {
		$subpackages = JFolder::files($p_dir);
		$result = true;
		$installer = new JInstaller();
		if ($subpackages) {
			$app = JFactory::getApplication();
			$templateDir = 'templates/'.$app->getTemplate();

			foreach ($subpackages as $zpackage) {
				if (JFile::getExt($p_dir.'/'.$zpackage) != "zip") {
					continue;
				}
				$subpackage = JInstallerHelper::unpack($p_dir.'/'.$zpackage);
				if ($subpackage) {
					$type = JInstallerHelper::detectType($subpackage['dir']);
					if (! $type) {
						$messages[] = '<img src="'.$templateDir.'/images/admin/publish_x.png" alt="" width="16" height="16" />&nbsp;<span style="color:#FF0000;">'.JText::_($zpackage." Not valid package") . '</span>';
						$result = false;
					}
					if (! $installer->install($subpackage['dir'])) {
						// There was an error installing the package
						$messages[] = '<img src="'.$templateDir.'/images/admin/publish_x.png" alt="" width="16" height="16" />&nbsp;<span style="color:#FF0000;">'.JText::sprintf('Install %s: %s', $type." ".JFile::getName($zpackage), JText::_('Error')).'</span>';
					}
					else {
						$messages[] = '<img src="'.$templateDir.'/images/admin/tick.png" alt="" width="16" height="16" />&nbsp;<span style="color:#00FF00;">'.JText::sprintf('Install %s: %s', $type." ".JFile::getName($zpackage), JText::_('Success')).'</span>';
					}

					if (! is_file($subpackage['packagefile'])) {
						$subpackage['packagefile'] = $p_dir.'/'.$subpackage['packagefile'];
					}
					if (is_dir($subpackage['extractdir'])) {
						JFolder::delete($subpackage['extractdir']);
					}
					if (is_file($subpackage['packagefile'])) {
						JFile::delete($subpackage['packagefile']);
					}
				}
			}
		}
		JFolder::delete($p_dir);

	}
}
?>
