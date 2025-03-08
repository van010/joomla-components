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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

JalangHelperContent::registerAdapter(
	__FILE__,
	'menu',
	10,
	Text::_('MENUS'),
	Text::_('MENUS')
);

class JalangHelperContentMenu extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table = 'menu';
		$this->edit_context = 'com_menus.edit.item';
		$this->associate_context = 'com_menus.item';
		$this->alias_field = 'alias';
		$this->translate_filters = array('id<>1', 'client_id=0', "menutype <> 'default-all'");
		$this->translate_fields = array('title');
		//$this->reference_fields = array('menutype'=>'menu_types');
		$this->reference_tables = array('menu_types');
		$this->nested_field = 'parent_id';
		$this->nested_value = 1;
		parent::__construct($config);
	}

	public function getEditLink($id) {
		if($this->checkout($id)) {
			return 'index.php?option=com_menus&view=item&layout=edit&id='.$id;
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
			'a.menutype' => Text::_('MENU_TYPE'),
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
			'a.menutype' => 'MENU_TYPE',
		);
	}

	public function beforeSave(&$translator, $sourceid, &$row) {
		$params = json_decode($row['params']);
        
		//menutype
		$row['menutype'] = $this->getNewAlias($row['menutype'], $translator->fromLangTag, $translator->toLangTag, null, 'alias', false);

		//path
		if(isset($row['path'])) {
			$parentPath = $this->getParentPath($row[$this->nested_field]);
			$row['path'] = $parentPath ? $parentPath . '/'.$row[$this->alias_field] : $row[$this->alias_field];
		}

		//update link
		$aLinks = array(
			//com_banners
			array('table' => 'categories', 		'link' => 'index.php?option=com_banners&view=category&id='),
			//com_content
			array('table' => 'content', 		'link' => 'index.php?option=com_content&view=article&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_content&view=category&layout=blog&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_content&view=category&layout=fixel&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_content&view=category&layout=ja_fixel:fixel&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_content&view=category&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_content&view=categories&id='),
			//com_contact
			array('table' => 'contact_details', 'link' => 'index.php?option=com_contact&view=contact&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_contact&view=category&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_contact&view=categories&id='),
			//com_newsfeeds
			array('table' => 'newsfeeds', 		'link' => 'index.php?option=com_newsfeeds&view=newsfeed&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_newsfeeds&view=category&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_newsfeeds&view=categories&id='),
			//com_weblinks
			array('table' => 'weblinks', 		'link' => 'index.php?option=com_weblinks&view=weblink&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_weblinks&view=category&id='),
			array('table' => 'categories', 		'link' => 'index.php?option=com_weblinks&view=categories&id='),

			//com_k2
			array('table' => 'k2_items', 		'link' => 'index.php?option=com_k2&view=item&layout=item&id='),
			array('table' => 'k2_categories', 	'link' => 'index.php?option=com_k2&view=itemlist&layout=category&task=category&id='),

			//com_virtuemart
			array('table' => 'virtuemart_categories', 		'link' => 'index.php?option=com_virtuemart&view=categories&virtuemart_category_id='),
			array('table' => 'virtuemart_categories', 		'link' => 'index.php?option=com_virtuemart&view=category&virtuemart_category_id='),
			array('table' => 'virtuemart_manufacturers',	'link' => 'index.php?option=com_virtuemart&view=manufacturer&layout=details&virtuemart_manufacturer_id='),
			array('table' => 'virtuemart_products',			'link' => 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='),
			array('table' => 'virtuemart_products',			'link' => 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='),
			array('table' => 'virtuemart_vendors',			'link' => 'index.php?option=com_virtuemart&view=vendor&layout=contact&virtuemart_vendor_id='),
			array('table' => 'virtuemart_vendors',			'link' => 'index.php?option=com_virtuemart&view=vendor&layout=details&virtuemart_vendor_id='),
			array('table' => 'virtuemart_vendors',			'link' => 'index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=')
		);

		$tableCategory = 'categories';
		foreach($aLinks as $link) {
			if(strpos($row['link'], $link['link']) === 0) {
				$oid = (int) substr($row['link'], strlen($link['link']));
				if(in_array($link['table'] , array('k2_categories', 'virtuemart_categories'))) {
					$tableCategory = $link['table'];
				}

				if($oid) {
					$nid = $translator->getAssociatedItem($link['table'], $oid);
					if($nid) {
						$row['link'] = str_replace($link['link'].$oid, $link['link'].$nid, $row['link']);
					}
				}
				break;
			}
		}

		if(strpos($row['link'], 'option=com_k2') !== false) {
			$tableCategory = 'k2_categories';
		}
		
		// fix menu link for FLEXI;
		if(strpos($row['link'], 'option=com_flexicontent') !== false) {
			$newlink = array();
			$params = explode('&', $row['link']);
			for ($i=0;$i<count($params);$i++) {
				if (strpos($params[$i], 'cid') !== false
					|| strpos($params[$i], 'rootcat') !== false
					|| strpos($params[$i], 'id') !== false) {
					if (strpos($params[$i], 'cid') === false && strpos($params[$i], 'id') !== false)
						$tableCategory = 'content';
					else
						$tableCategory = 'categories';
					$cid = explode('=', $params[$i]);
					$j=0;
					$cids = array();
					while ($j<count($cid)) {
						$cids[] = $cid[$j];
						$cids[] = $translator->getAssociatedItem($tableCategory, $cid[$j+1], $cid[$j+1]);
						$j+=2;
					}
					$newlink[] = implode('=', $cids);
				} else {
					$newlink[] = $params[$i];
				}
			}
			$row['link'] = implode('&', $newlink);
		}
		
		// update category ids
		$cats = array('catid', 'categories', 'catsid', 'categoryIDs', 'display_model-modcats-category', 'category', 'featured_categories', 'userCategoriesFilter', 'categoriesFilter', 'list_categories');
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

		/**
		 * Update Item Alias:
		 * should double check in afterTranslate event because in some case, older item was configured to point to newer menu item
		 * so, it might not return correct associated item here
		 */
		if($row['type'] == 'alias') {
			if($params->aliasoptions) {
				$params->aliasoptions = $translator->getAssociatedItem($this->table, $params->aliasoptions, $params->aliasoptions);
			}
		}

		$row['params'] = json_encode($params);
	}

	public function afterTranslate(&$translator) {
		if (!version_compare(JVERSION, '4.0', 'ge')) {
			require_once( JPATH_ADMINISTRATOR . '/components/com_menus/tables/menu.php' );
		}
		$db = Factory::getDbo();
		//Update Item Alias
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id').','.$db->quoteName('params'))
				->from('#__'.$this->table)
				->where(array('client_id=0', $db->quoteName('type').'='.$db->quote('alias'), $db->quoteName('language').'='.$db->quote($translator->toLangTag)));
		$db->setQuery($query);
		$rows = $db->loadAssocList();

		if (count($rows)) {
			foreach ($rows as $row) {
				$params = json_decode($row['params']);
				$aid = $params->aliasoptions;
				$params->aliasoptions = $translator->getAssociatedItem($this->table, $params->aliasoptions, $params->aliasoptions);

				if ($params->aliasoptions != $aid) {
					$query = "UPDATE #__menu SET params=".$db->quote(json_encode($params))." WHERE id=".$db->quote($row['id']);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		// create default-all menutype with default menu item for all languages
		$query = "INSERT IGNORE INTO #__menu_types(".$db->quoteName('menutype').", ".$db->quoteName('title').", ".$db->quoteName('description').") VALUES('default-all', 'Default', 'Default for all languages')";
		$db->setQuery($query);
		$db->execute();

		// insert default menu item
		$query = "INSERT IGNORE INTO #__menu ".
			"(".$db->quoteName('menutype').", ".$db->quoteName('title').", ".$db->quoteName('alias').", ".$db->quoteName('path').", ".$db->quoteName('link').", ".$db->quoteName('type').", ".$db->quoteName('published').", ".$db->quoteName('parent_id').", ".$db->quoteName('level').", ".$db->quoteName('component_id').", ".$db->quoteName('browserNav').", ".$db->quoteName('access').", ".$db->quoteName('template_style_id').", ".$db->quoteName('home').", ".$db->quoteName('language').", ".$db->quoteName('client_id').")".
			" VALUES('default-all', 'Default', 'default', 'default', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, 1, 0, 1, '*', 0)";
		$db->setQuery($query);
		$db->execute();

		//REBUILD MENU TREE
		$config = array();
		if (!version_compare(JVERSION, '4.0', 'ge')) {
			$tableMenu = Table::getInstance('Menu', 'MenusTable', $config);
			$tableMenu->rebuild();
		}

		// support skip component content options T3.
		$query = 'SELECT id, params FROM #__template_styles WHERE params LIKE "%t3_template%"';
		$db->setQuery($query);
		$lists = $db->loadObjectList();
		if (!empty($lists)) {
			foreach ($lists AS $l) {
				$json = json_decode($l->params);
				if (!empty($json->skip_component_content)) {
					$scc = $json->skip_component_content;
					$newscc = array();
					if (!empty($scc)) {
						foreach ($scc AS $sc) {
							$associationID = $translator->getAssociatedItem($this->table, $sc, NULL);
							if (!empty($associationID))
								$newscc[] = $associationID;
							$newscc[] = $sc;
						}
						$newscc = array_unique($newscc);
					}
					if (!empty($newscc)) {
						$json->skip_component_content = $newscc;
						$query = 'UPDATE #__template_styles SET params = '.$db->quote(json_encode($json)).' WHERE id = '.$l->id;
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}

	public function beforeTranslate(&$translator) {
		//Fix bug: menutype's lenght is limited 24 characters and make duplicated error
		$db = Factory::getDbo();
		$query = "ALTER TABLE #__{$this->table} MODIFY ".$db->quoteName('menutype')."  varchar(100) NOT NULL";

		$db->setQuery($query);
		$db->execute();
	}

	public function getParentPath($pk)
	{
		if(!$pk) return '';

		$db = Factory::getDbo();
		$k = $this->primarykey;

		// Get the path from the node to the root.
		$query = $db->getQuery(true);
		$query->select('p.path')->from('#__menu AS p')->where('p.' . $k . ' = ' . (int) $pk);
		$db->setQuery($query);

		return $db->loadResult();
	}

	public function getNewAlias($alias, $fromLangTag, $toLangTag, $row = null, $generateFrom = '', $makeUnique = false) {
		if(empty($generateFrom)) {
			if(is_array($row) && isset($row['type'])) {
				if($row['type'] == 'alias' || $row['type'] == 'url') {
					//$generateFrom = 'alias';
					$jdate = new Date();
					$date = $jdate->format('Y-m-d-H-i-s');
					return parent::getNewAlias($date, $fromLangTag, $toLangTag, $row, $generateFrom, $makeUnique);
				}
			}
		}
		return parent::getNewAlias($alias, $fromLangTag, $toLangTag, $row, $generateFrom, $makeUnique);
	}
}