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

require_once(JPATH_ADMINISTRATOR . '/components/com_jalang/helpers/helper.php');
if(File::exists(JPATH_ADMINISTRATOR . '/components/com_mijoshop/mijoshop.php')) {
	//Register if Mijoshop is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'mijoshop_product_description',
		5,
		Text::_('MIJOSHOP_PRODUCT'),
		Text::_('MIJOSHOP_PRODUCT')
	);


	class JalangHelperContentMijoshopProductDescription extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table_type 			= 'table_ml';
			$this->language_field 		= 'language_id';
			$this->language_mode 		= 'id';
			$this->table 				= 'mijoshop_product_description';
			$this->primarykey 			= 'product_id';
			$this->edit_context 		= 'mijoshop.edit.product';
			$this->associate_context 	= 'mijoshop.product';
			$this->translate_fields 	= array('name','description','meta_description','meta_keyword','tag');
			$this->translate_filters 	= array();
			$this->alias_field 			= '';
			$this->title_field 			= 'name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
        	return 'index.php?option=com_mijoshop&route=catalog/product/'.$this->mijoshop_type.'&product_id='.$id;
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
				'a.product_id' => 'JGRID_HEADING_ID',
				'a.name' => 'JGLOBAL_TITLE'
			);
		}

        public function afterSave(&$translator, $sourceid, &$row)
        {
            $db = Factory::getDbo();
            $product_exists = $db->getQuery(true);
            // Check if the product with language already exists.
            $product_exists->select('name')
            		->from('#__mijoshop_product_description')
            		->where('language_id ='.JalangHelper::getLanguageIdFromCode($translator->toLangTag))
            		->where('product_id = '.$sourceid);
            $db->setQuery($product_exists);
            $check_exists = $db->loadColumn();
            $query = $db->getQuery(true);
            if (empty($check_exists)) {
				//Update language_id if language_id = 0
				$query->update('#__mijoshop_product_description')
					  ->set('language_id ='.JalangHelper::getLanguageIdFromCode($translator->toLangTag))
					  ->where('language_id = 0')
					  ->where('product_id = '.$sourceid);
				$db->setQuery($query);
				$db->execute();
            }

            $query_select = $db->getQuery(true);
            $query_select->select('keyword')->from('#__mijoshop_url_alias')->where($db->quoteName('query').'='.$db->quote('product_id='.$sourceid).' AND language_id='.$db->quote(JalangHelper::getLanguageIdFromCode($translator->fromLangTag)));
            $db->setQuery($query_select);
            $keyword = $db->loadResult();
            if($keyword){
                $query->clear();
                $query->select('*')->from('#__mijoshop_url_alias')->where($db->quoteName('language_id').'='.$db->quote($row['language_id']).' AND '.$db->quoteName('query').'='.$db->quote('product_id='.$row['product_id']));
                $db->setQuery($query);
                $check = $db->loadObject();
                if(!$check){
                    $query->clear();
                    $query->insert('#__mijoshop_url_alias')
                          ->columns('query, keyword, language_id')
                          ->values(implode(',',array($db->quote('product_id='.$row['product_id']), $db->quote($keyword.'-'.$translator->to), $db->quote($row['language_id']))));
                    $db->setQuery($query);
                    $db->execute();  
                }
            }
        }
	}
}
