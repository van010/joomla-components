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
		'virtuemart_shipmentmethods',
		4,
		Text::_('VIRTUEMART_SHIPMENT_METHODS'),
		Text::_('VIRTUEMART_SHIPMENT_METHODS')
	);


	class JalangHelperContentVirtuemartShipmentmethods extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table_type = 'table';
			$this->table = 'virtuemart_shipmentmethods';
			$this->primarykey = 'virtuemart_shipmentmethod_id';
			$this->edit_context = 'virtuemart.edit.category';
			$this->associate_context = 'virtuemart.category';
			$this->translate_fields = array('shipment_name', 'shipment_desc');
			$this->translate_filters = array();
			$this->alias_field = '';
			$this->title_field = 'shipment_name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_virtuemart&view=shipmentmethod&task=edit&cid[]='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.shipment_name' => Text::_('JGLOBAL_TITLE')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.virtuemart_shipmentmethod_id' => 'JGRID_HEADING_ID',
				'a.shipment_name' => 'JGLOBAL_TITLE'
			);
		}
	}
}
