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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/category.php')) {
	//Register if K2 is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'virtuemart_products',
		5,
		Text::_('VIRTUEMART_PRODUCTS'),
		Text::_('VIRTUEMART_PRODUCTS')
	);


	class JalangHelperContentVirtuemartProducts extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table_type = 'table';
			$this->table = 'virtuemart_products';
			$this->primarykey = 'virtuemart_product_id';
			$this->edit_context = 'virtuemart.edit.products';
			$this->associate_context = 'virtuemart.products';
			$this->translate_fields = array('product_s_desc', 'product_desc', 'product_name', 'metakey', 'metadesc');
			$this->translate_filters = array();
			$this->alias_field = '';
			$this->title_field = 'product_name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.product_name' => Text::_('JGLOBAL_TITLE')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.virtuemart_product_id' => 'JGRID_HEADING_ID',
				'a.product_name' => 'JGLOBAL_TITLE'
			);
		}
	}
}
