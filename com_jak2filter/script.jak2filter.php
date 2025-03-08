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

// Try extending time, as unziping/ftping took already quite some... :
@set_time_limit( 0 );
defined ( '_JEXEC' ) or die ( 'Restricted access' );
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}

class Com_jak2filterInstallerScript
{
	function postflight($type, $parent) {
		if(version_compare( JVERSION, '3.0.0', '>' )){
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
	}
	public function uninstall($parent){
		if(version_compare( JVERSION, '3.0.0', '>' )){
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
						$el = $db->loadColumn();
						if (count($el))
						{
							foreach ($el as $id)
							{
								$installer = new JInstaller;
								$result = $installer->uninstall('module', $id);
								$messages[] = JText::_('Uninstalling module "'.$package.'" was successful.');
							}

						}
						break;
					case "plg":
						$info = explode("_", $package);
						if (count($info) >= 3) {
							$info[2] = str_replace($info[0]."_".$info[1]."_", "", $package);
							$db->setQuery("SELECT extension_id, `name` FROM #__extensions WHERE `type` = 'plugin' AND `element` = '".$info[2]."' AND `folder` = '".$info[1]."' ");
							$extensions = $db->loadColumn();
							if (count($extensions))
							{
								foreach ($extensions as $id)
								{
									$installer = new JInstaller;
									$result = $installer->uninstall('plugin', $id);
									$messages[] = JText::_('Uninstalling plugin "'.$package.'" was successful.');
								}

							}
						}

						break;
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
			<?php
		}
	}
}
?>
