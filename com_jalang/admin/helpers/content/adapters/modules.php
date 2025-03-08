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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

JalangHelperContent::registerAdapter(
	__FILE__,
	'modules',
	100,
	Text::_('MODULES'),
	Text::_('MODULES')
);

class JalangHelperContentModules extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table = 'modules';
		$this->edit_context = 'com_modules.edit.module';
		$this->associate_context = 'com_modules.module';
		$this->translate_fields = array('title', 'content');
		$this->translate_filters = array('client_id=0');
		$this->reference_tables = array('menu');
		parent::__construct($config);
	}

	public function getEditLink($id) {
		if($this->checkout($id)) {
			return 'index.php?option=com_modules&view=module&layout=edit&id='.$id;
		}
		return false;
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 */
	public function getSortFields()
	{
		return array(
			'a.title' => Text::_('JGLOBAL_TITLE'),
			'a.access' => Text::_('JGRID_HEADING_ACCESS'),
			'a.module' => Text::_('MODULE'),
			'a.id' => Text::_('JGRID_HEADING_ID')
		);
	}
	
	/**
	 * Returns an array of fields will be displayed in the table list
	 */
	public function getDisplayFields()
	{
		return array(
			'a.id' => 'JGRID_HEADING_ID',
			'a.title' => 'JGLOBAL_TITLE',
			'a.module' => 'MODULE',
			'a.note' => 'NOTE'
		);
	}

	public function beforeSave(&$translator, $sourceid, &$row) {
		//Update module settings depend on module type
		$module = $row['module'];


		$adapter = JalangHelperContent::getInstance('categories');
		if($adapter) {
			$translator->loadAssociate($adapter->table, $adapter->associate_context, $adapter->primarykey);
		}

		$params = json_decode($row['params']);
		if($module == 'mod_custom') {
			//do nothing since content had been translated
		} elseif ($module == 'mod_menu') {
			if ($params->menutype) $params->menutype = $this->getNewAlias($params->menutype, $translator->fromLangTag, $translator->toLangTag, null, 'alias', false);
		} elseif ($module == 'mod_related_items') {
			//do nothing
		} elseif ($module == 'mod_articles_archive') {
			//do nothing
		} elseif ($module == 'mod_articles_categories') {
			$params->parent = $translator->getAssociatedItem('categories', $params->parent, $params->parent);
		}

		// update category ids
		$cats = array('catid', 'catsid', 'display_model-modcats-category', 'category', 'featured_categories', 'list_categories');

		/**
		 * @to_do whenever new component is added to translation list
		 * it requires to update below code snippet to get correct category id for that component's items
		 */
		if(strpos($module, 'k2') !== false) {
			$tableCategory = 'k2_categories';
		} elseif(strpos($module, 'virtuemart') !== false) {
			$tableCategory = 'virtuemart_categories';
		} else {
			$tableCategory = 'categories';
		}

		if($module == 'mod_ja_acm') {
			if(isset($params->{"jatools-config"})) {
				$config = json_decode($params->{"jatools-config"});

				$pattern = "/^[a-z0-9\-]+\[(".implode('|', $cats).")\]/i";
				foreach($config as $property => $value) {
					if(is_object($value)) {
						foreach ($value as $field => $fvalue) {
							if(preg_match($pattern, $field)) {
								if(!is_array($fvalue)) {
									$cid = $fvalue;
									$value->{$field} =  $translator->getAssociatedItem($tableCategory, $cid, $cid);
								} else {
									$catid = array();
									foreach ($fvalue as $cid) {
										$catid[] = $translator->getAssociatedItem($tableCategory, $cid, $cid);
									}
									$value->{$field} = $catid;
								}
							}
						}
						$config->$property = $value;
					}
				}
				$params->{"jatools-config"} = json_encode($config);
			}
		} else {
			foreach ($cats as $cat) {
				if (isset($params->$cat)) {
					if(is_array($params->$cat) && count($params->$cat)) {
						$catid = array();
						foreach ($params->$cat as $cid) {
							$catid[] = $translator->getAssociatedItem($tableCategory, $cid, $cid);
						}
						$params->$cat = $catid;
					} else {
						if (is_numeric($params->$cat)) {
							$cid = $params->$cat;
							$params->$cat = $translator->getAssociatedItem($tableCategory, $cid, $cid);
						}
					}
				}
			}
		}


		$row['params'] = json_encode($params);
	}

	public function afterSave(&$translator, $sourceid, &$row) {
		//Update menu assignment

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('menuid')->from('#__modules_menu')->where('moduleid='.$db->quote($sourceid));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if(count($items)) {
			$targetid = $row[$this->primarykey];
			$query->clear();
			$query->delete('#__modules_menu')->where('moduleid='.$db->quote($targetid));
			$db->setQuery($query);
			$db->execute();

			foreach ($items as $item) {
				if($item->menuid == 0) {
					//all
					$menuid = 0;
				} elseif ($item->menuid > 0) {
					//selected menu
					$menuid = $translator->getAssociatedItem('menu', $item->menuid, $item->menuid);
				} else {
					//all but exclude selected
					$oldmenuid = abs($item->menuid);
					$menuid = -($translator->getAssociatedItem('menu', $oldmenuid, $oldmenuid));
				}
                $query->clear();
                $query->select('*')->from('#__modules_menu')->where('moduleid = '.(int)$targetid.' AND menuid = '.(int)$menuid);
                $db->setQuery($query);
                $check_duplicate = $db->loadResult();
				if(!$check_duplicate){
				    $query->clear();
				    $query->insert('#__modules_menu')->columns('moduleid, menuid');
				    $query->values($db->quote($targetid).','.$db->quote($menuid));

				    $db->setQuery($query);
				    $db->execute();
				}
			}
		}

		//
		parent::afterSave($translator, $sourceid, $row);
	}
}