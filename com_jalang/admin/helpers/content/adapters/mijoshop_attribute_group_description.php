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

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_mijoshop/mijoshop.php')) {
	//Register if Mijoshop is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'mijoshop_attribute_group_description',
		3,
		Text::_('MIJOSHOP_ATTRIBUTE_GROUP'),
		Text::_('MIJOSHOP_ATTRIBUTE_GROUP')
	);


	class JalangHelperContentMijoshopAttributeGroupDescription extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table_type 			= 'table_ml';
			$this->language_field 		= 'language_id';
			$this->language_mode 		= 'id';
			$this->table 				= 'mijoshop_attribute_group_description';
			$this->primarykey 			= 'attribute_group_id';
			$this->edit_context 		= 'mijoshop.edit.attribute_group';
			$this->associate_context 	= 'mijoshop.attribute_group';
			$this->translate_fields 	= array('name');
			$this->translate_filters 	= array();
			$this->alias_field 			= '';
			$this->title_field 			= 'name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_mijoshop&route=catalog/attribute_group/'.$this->mijoshop_type.'&attribute_group_id='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.name' => Text::_('JGLOBAL_TITLE')
			);
		}

		/**
		 * Returns an array of fields will be displayed in the table list
		 */
		public function getDisplayFields()
		{
			return array(
				'a.attribute_group_id' => 'JGRID_HEADING_ID',
				'a.name' => 'JGLOBAL_TITLE'
			);
		}
	}
}
