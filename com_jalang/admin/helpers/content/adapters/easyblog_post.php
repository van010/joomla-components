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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/models/blogs.php')) {
    require_once(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php');
	//Register if K2 is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'easyblog_post',
		4,
		Text::_('EASYBLOG_ENTRIES'),
		Text::_('EASYBLOG_ENTRIES')
	);

	//require_once( JPATH_ADMINISTRATOR . '/components/com_easyblog/models/blogs.php' );
	jimport('joomla.filesystem.file');

	class JalangHelperContentEasyblogPost extends JalangHelperContent
	{
    public $ebXml;

		public function __construct($config = array())
		{
			$this->table = 'easyblog_post';
			$this->edit_context = 'com_easyblog.edit.item';
			$this->associate_context = 'com_easyblog.item';
			$this->alias_field = 'permalink';
			$this->translate_fields = array('title', 'content', 'intro', 'excerpt');
      $this->ebXml = simplexml_load_file(JPATH_ADMINISTRATOR .'/components/com_easyblog/easyblog.xml');
			/**
			 * @TO_DO anable reference field to category when translate easyblog category task is enabled
			 */
			//$this->reference_fields = array('category_id'=>'easyblog_category');
			$xml = $this->ebXml;
      $version = (string)$xml->version;
      if((int)$version < 5.0){
        $this->translate_filters = array('ispending = 0');
      }else{
          $this->translate_filters = array('isnew = 0');
      }
			parent::__construct($config);
		}

		public function getEditLink($id) {
			if($this->checkout($id)) {
			  $xml = $this->ebXml;
                 $version = (string)$xml->version;
                 if((int)$version >= 5.0){
                    return 'index.php?option=com_easyblog&c=blogs&view=composer&tmpl=component&uid='.$id;
                 }else{
                    return 'index.php?option=com_easyblog&c=blogs&task=edit&blogid='.$id;
                 }
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
			//clone tag
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('tag_id')->from('#__easyblog_post_tag')->where('post_id='.$db->quote($sourceid));
			$db->setQuery($query);
			$items = $db->loadObjectList();

			if(count($items)) {
				$targetid = $row[$this->primarykey];
				$jdate = Date::getInstance();
				$date = $jdate->toSql();
				$query->clear();
				$query->delete('#__easyblog_post_tag')->where('post_id='.$db->quote($targetid));
				$db->setQuery($query);
				$db->execute();

				foreach ($items as $item) {

					$query->clear();
					$query->insert('#__easyblog_post_tag')->columns('tag_id, post_id, created');
					$query->values($db->quote($item->tag_id).','.$db->quote($targetid).','.$db->quote($date));

					$db->setQuery($query);
					$db->execute();
				}
			}

			//check & update featured entry
			$query->clear();
			$query->select('id')->from('#__easyblog_featured')->where('type="post" AND content_id='.$db->quote($sourceid));
			$db->setQuery($query);
			$item = $db->loadResult();
			if($item){
				$jdate = Date::getInstance();
				$date = $jdate->toSql();
				$query->clear();
				$query->insert('#__easyblog_featured')->columns('content_id, type, created');
				$query->values($db->quote($row['id']).',"post",'.$db->quote($date));
				$db->setQuery($query);
				$db->execute();
			}

			// Update language tag from * to default language.
			$default_lang = Factory::getLanguage();
			$update_lang = $db->getQuery(true);
			$update_lang->update('#__easyblog_post')->set('language="'.$default_lang->getTag().'"')->where('id = '.$sourceid)->where('language = "*"');
			$db->setQuery($update_lang);
			$db->execute();
			// update language tag for revision. // wil be update to new query.
			$update_lang_rev = 'UPDATE 
				#__easyblog_revisions
				SET '.$db->quoteName('content').' = REPLACE('.$db->quoteName('content').', \'"language":"*"\', \'"language":"'.$default_lang->getTag().'"\') 
				WHERE '.$db->quoteName('post_id').' = '.$sourceid.' ORDER BY '.$db->quoteName('id').' DESC LIMIT 1';
			$db->setQuery($update_lang_rev);
			$db->execute();
			// end update language tag.

            //Update Revision if EasyBlog ver 5.x
            $xml = $this->ebXml;
            $version = (string)$xml->version;
            if ((int)str_replace('.','',$version) >= 503) {
                // update associates table.
				$asso_array=array();
				$db->setQuery('SELECT title FROM #__easyblog_post WHERE id = '.$sourceid);
				$title = $db->loadAssoc();
				$obj = new stdClass();
				$obj->code = $translator->toLangTag;
				$obj->id = (string)$row['id'];
				$obj->post = $row['title'];
				$asso_array[0] = $obj;
				$obj = new stdClass();
				$obj->code = $translator->fromLangTag;
				$obj->id = (int)$sourceid;
				$obj->post = $title['title'];
				$asso_array[1] = $obj;
				$key = md5(json_encode($asso_array));
				$query = "insert into `#__easyblog_associations` (".$db->quoteName('id').", ".$db->quoteName('post_id').", ".$db->quoteName('key').") values ";
				$arr = array();
				foreach($asso_array as $assoc) {
					$arr[] = "(null, " . $db->quote($assoc->id) . "," . $db->quote($key) . ")";
				}
				$values = implode(",", $arr);
				$query .= $values;
				$db->setQuery($query);
				$db->execute();

                // update revision
                $post = EB::post($row['id']);
                $post->bind($post->post, array('force' => true));
                $post->save();
            }

			//
			parent::afterSave($translator, $sourceid, $row);
		}

        public function afterRetranslate($translator, $sourceid, &$row)
        {
            // Add source ID for blog entry
            $row['id'] = $sourceid;
            $row['language'] = str_replace(array('\'', '"'),array('',''),$row['language']); // remove quote for language tag.
            $post = EB::post($row['id']);
            $post->bind($post->post, array('force' => true));
            $post->save();
        }
	}
}
