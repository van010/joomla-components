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

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_hikashop/classes/product.php')) {
	//Register if hikashop is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'hikashop_product',
		5,
		Text::_('HIKASHOP_PRODUCT'),
		Text::_('HIKASHOP_PRODUCT')
	);

	jimport('joomla.filesystem.file');

	class JalangHelperContentHikashopProduct extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table = 'hikashop_product';
			$this->edit_context = 'com_hikashop.edit.item';
			$this->associate_context = 'com_hikashop.item';
			$this->alias_field = 'product_alias';
			$this->translate_fields = array('product_name', 'product_description', 'product_page_title', 'product_meta_description');
// 			$this->reference_fields = array('catid'=>'product_parent_id');
			$this->translate_filters = array('product_code NOT LIKE "%jalang"');
			$this->title_field = 'product_name';
			$this->primarykey = 'product_id';
			$this->table_type = 'table_alone';
			$this->unique_field = 'product_code';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_hikashop&ctrl=product&task=edit&cid[]='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.product_name' => Text::_('JGLOBAL_TITLE'),
				'a.product_access' => Text::_('JGRID_HEADING_ACCESS'),
				'a.product_id' => Text::_('JGRID_HEADING_ID')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.product_id' => 'JGRID_HEADING_ID',
				'a.product_name' => 'JGLOBAL_TITLE'
			);
		}
		
		// sourceid is the default item id.
		public function afterSave(&$translator, $sourceid, &$row) {
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' ', 'file_ref_id', $row['product_id'], 'AND file_type="product"');
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' ', 'file_ref_id', $row['product_id'], 'AND file_type="file"');
			$this->transferData('price', 'WHERE price_product_id='.$sourceid.'', 'price_product_id', $row['product_id']);
			$this->transferCategoryId($translator, $sourceid, $row);
			//$this->transferRelatedId($translator, $sourceid, $row);
			$this->updateParent($translator, $sourceid, $row);
		}

		// sourceid is the default item id.
		public function afterRetranslate(&$translator, $sourceid, &$row) {
			$val = $translator->aAssociation['hikashop_product'][$sourceid][$translator->toLangTag];
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' AND file_type="product"', 'file_ref_id', $val, 'AND file_type="product"');
			$this->transferData('file', 'WHERE file_ref_id='.$sourceid.' AND file_type="file"', 'file_ref_id', $val, 'AND file_type="file"');
			$this->transferData('price', 'WHERE price_product_id='.$sourceid.'', 'price_product_id', $val);
			$itemTrans = $row;
			$itemTrans['product_id'] = $val;
			$this->transferCategoryId($translator, $sourceid, $itemTrans);
			//$this->transferRelatedId($translator, $sourceid, $itemTrans);
			$this->updateParent($translator, $sourceid, $itemTrans);
		}
		
		// update characteristic and parent relationship.
		public function updateParent($translator, $sourceid, $row) {
			$db = Factory::getDbo();
			$delete = 'DELETE FROM #__hikashop_variant WHERE variant_product_id = '.$row['product_id'];
			$db->setQuery($delete);
			$db->execute();
			
			$sql = 'SELECT * FROM #__hikashop_variant WHERE variant_product_id = '.$sourceid;
			$db->setQuery($sql);
			$results = $db->loadObjectList();
			if (!empty($results)) {
				foreach ($results AS $r) {
					$obj = $r;
					$obj->variant_product_id = $row['product_id'];
					$result = Factory::getDbo()->insertObject('#__hikashop_variant', $obj);
				}
			}
			
			if (!empty($row['product_parent_id'])) {
				if (!empty($translator->aAssociation['hikashop_product'][$row['product_parent_id']][$translator->toLangTag])) {
					$parent_id = $translator->aAssociation['hikashop_product'][$row['product_parent_id']][$translator->toLangTag];
					$update = 'UPDATE #__hikashop_product 
						SET product_parent_id = '.$parent_id.'
						WHERE product_id = '.$row['product_id'];
					$db->setQuery($update);
					$db->execute();
				}
			}
		}
		
		public function transferRelatedId($translator, $sourceid, $row) {
			$db = Factory::getDbo();
			$delete = 'DELETE FROM #__hikashop_product_related WHERE product_id = '.$row['product_id'];
			$db->setQuery($delete);
			$db->execute();
			
			$sql = 'SELECT * FROM #__hikashop_product_related WHERE product_id = '.$sourceid;
			$db->setQuery($sql);
			$results = $db->loadObjectList();
			if (!empty($results)) {
				foreach ($results AS $r) {
					$obj = $r;
					$obj->product_id = $row['product_id'];
					$obj->product_related_id = $translator->aAssociation['hikashop_product'][$r->product_related_id][$translator->toLangTag];
					$result = Factory::getDbo()->insertObject('#__hikashop_product_related', $obj);
				}
			}
		}
		
		// sourceid is the default item id.
		public function transferCategoryId($translator, $sourceid, $row) {
			$db = Factory::getDbo();
			$delete = 'DELETE FROM #__hikashop_product_category WHERE product_id = '.$row['product_id'];
			$db->setQuery($delete);
			$db->execute();
			
			// add relation ship for product and category for new translate items.
			$sql = 'SELECT pc.category_id FROM #__hikashop_product_category pc
					LEFT JOIN #__hikashop_product p ON (p.product_id = pc.product_id)
					WHERE p.product_id = '.$sourceid;
			$db = Factory::getDbo();
			$db->setQuery($sql);
			$column = $db->loadColumn();
			if (!empty($column)) {
				foreach ($column AS $col) {
					if (!empty($translator->aAssociation['hikashop_category'][$col][$translator->toLangTag])) {
						$sql = 'INSERT INTO #__hikashop_product_category (category_id, product_id)
								VALUES ('.$translator->aAssociation['hikashop_category'][$col][$translator->toLangTag].','.$row['product_id'].')';
						$db->setQuery($sql);
						$db->execute();
					}
				}
			}
		}
		
		// transfer file and price. table that not depend on relationship.
		public function transferData($table, $where, $ref_field, $value, $and='') {
			$db = Factory::getDbo();
			$delete = 'DELETE FROM #__hikashop_'.$table.' WHERE '.$ref_field.' = '.$value.' '.$and;
			$db->setQuery($delete);
			$db->execute();
			
			$sql = 'SELECT * FROM #__hikashop_'.$table.' '.$where.' '.$and;
			$db->setQuery($sql);
			$results = $db->loadObjectList();
			if (!empty($results)) {
				foreach ($results AS $r) {
					$obj = $r;
					$obj->{$table.'_id'}=NULL;
					$obj->$ref_field = $value;
					$result = Factory::getDbo()->insertObject('#__hikashop_'.$table.'', $obj);
				}
			}
		}
	}
}
