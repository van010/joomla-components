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

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_k2/models/items.php')) {
	//Register if K2 is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'k2_items',
		4,
		Text::_('K2_ITEMS'),
		Text::_('K2_ITEMS')
	);

	//require_once( JPATH_ADMINISTRATOR . '/components/com_k2/models/items.php' );
	jimport('joomla.filesystem.file');

	class JalangHelperContentK2Items extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table = 'k2_items';
			$this->edit_context = 'com_k2.edit.item';
			$this->associate_context = 'com_k2.item';
			$this->alias_field = 'alias';
			$this->translate_fields = array('title', 'introtext', 'fulltext', 'metakey', 'metadesc');
			$this->reference_fields = array('catid'=>'k2_categories');
			$this->translate_filters = array('trash <> 1');
			parent::__construct($config);
		}

		public function getEditLink($id) {
			if($this->checkout($id)) {
				return 'index.php?option=com_k2&view=item&cid='.$id;
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
				'language' => Text::_('JGRID_HEADING_LANGUAGE'),
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
				'a.title' => 'JGLOBAL_TITLE'
			);
		}

		public function afterSave(&$translator, $sourceid, &$row) {
			//Clone images?
			$clone = (int) $translator->params->get('k2_clone_image', 1);
			if($clone) {
				$imgpath = JPATH_ROOT.'/media/k2/items/';
				$srcfile = md5('Image'.$sourceid);
				$dstfile = md5('Image'.$row[$this->primarykey]);

				if(File::exists($imgpath.'src/'.$srcfile.'.jpg') && !File::exists($imgpath.'src/'.$dstfile.'.jpg')) {
					File::copy($imgpath.'src/'.$srcfile.'.jpg', $imgpath.'src/'.$dstfile.'.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_Generic.jpg', $imgpath.'cache/'.$dstfile.'_Generic.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_L.jpg', $imgpath.'cache/'.$dstfile.'_L.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_M.jpg', $imgpath.'cache/'.$dstfile.'_M.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_S.jpg', $imgpath.'cache/'.$dstfile.'_S.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_XL.jpg', $imgpath.'cache/'.$dstfile.'_XL.jpg');
					File::copy($imgpath.'cache/'.$srcfile.'_XS.jpg', $imgpath.'cache/'.$dstfile.'_XS.jpg');
				}
			}

			//
			parent::afterSave($translator, $sourceid, $row);
		}
	}
}
