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

defined('_JEXEC') or die;

JalangHelperContent::registerAdapter(
	__FILE__,
	'content',
	2,
	Text::_('CONTENT_ARTICLES'),
	Text::_('CONTENT_ARTICLES')
);
if (version_compare(JVERSION, '4.0', 'ge')) {
	class ContentModelArticles extends Joomla\Component\Content\Administrator\Model\ArticlesModel{}
} else {
	require_once( JPATH_ADMINISTRATOR . '/components/com_content/models/articles.php' );
}
class jaContentModelArticles extends ContentModelArticles
{
	public function __construct($config) {
		parent::__construct($config);
	}

	public function getListQuery() {
		return parent::getListQuery();
	}
}

class JalangHelperContentContent extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table = 'content';
		$this->edit_context = 'com_content.edit.article';
		$this->associate_context = 'com_content.item';
		$this->alias_field = 'alias';
		$this->translate_fields = array('title', 'introtext', 'fulltext', 'metakey', 'metadesc');
		$this->reference_fields = array('catid'=>'categories');
		$this->fixed_fields = array('asset_id'=> 0, 'version' => 1);
		parent::__construct($config);
	}

	public function getEditLink($id) {
		if($this->checkout($id)) {
            return 'index.php?option=com_content&view=article&layout=edit&id='.$id;
		}
		return false;
	}

	public function getListQuery2($model)
	{
		$db = Factory::getDbo();
		$config = array();
		$modelContent = new jaContentModelArticles($config);

		$query = $modelContent->getListQuery();

		$defaultLanguage = JalangHelper::getDefaultLanguage();
		$language = $model->getState('mainlanguage');
		if($language == $defaultLanguage) {
			$query->where('(a.language = ' . $db->quote($language) . ' OR a.language = ' . $db->quote('*').')');
		} else {
			$query->where('a.language = ' . $db->quote($language));
		}
		return $query;
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 */
	public function getSortFields()
	{
		return array(
			'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
			'a.state' => Text::_('JSTATUS'),
			'a.title' => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'access_level' => Text::_('JGRID_HEADING_ACCESS'),
			'a.created_by' => Text::_('JAUTHOR'),
			'language' => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.created' => Text::_('JDATE'),
			'a.id' => Text::_('JGRID_HEADING_ID'),
			'a.featured' => Text::_('JFEATURED')
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
			//'a.ordering' => 'JGRID_HEADING_ORDERING',
			'category_title' => 'JCATEGORY',
			//'a.created' => 'JDATE'
		);
	}

	public function afterTranslate(&$translator) {
		//update front-page
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('cf.*')->from('#__content_frontpage AS cf');
		$query->innerJoin('#__content AS c ON c.id = cf.content_id');

		$where = array();
		$where[] = 'c.language = '.$db->quote($translator->fromLangTag);
		if($translator->convertLangTag) {
			$where[] = 'c.language = '.$db->quote($translator->convertLangTag);
		}
		$query->where($where, 'OR');

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if(count($rows)) {
			foreach ($rows as $row) {
				$newid = $translator->getAssociatedItem($this->table, $row->content_id);
				if($newid) {
					$queryTest = $db->getQuery(true);
					$queryTest->select('cf.content_id')->from('#__content_frontpage AS cf')->where('cf.content_id = '.$db->quote($newid));
					$db->setQuery($queryTest);
					$test = $db->loadResult();
					if(!$test) {
						$queryInsert = $db->getQuery(true);
						$queryInsert->insert('#__content_frontpage');
						$queryInsert->columns(array($db->quoteName('content_id'), $db->quoteName('ordering')));
						$queryInsert->values($db->quote($newid).','.$db->quote($row->ordering));

						$db->setQuery($queryInsert);
						$db->execute();
					}
				}
			}
		}
	}

	public function afterSave(&$translator, $sourceid, &$row) {
		//Update Extra fields are created by JA Content Type plugins
		$db = Factory::getDbo();
		if(in_array($db->getPrefix().'content_meta', $db->getTableList())) {
			$attribs = json_decode($row['attribs']);
			if($attribs) {
				$query = $db->getQuery(true);
				$query->insert('#__content_meta')
					->columns(array($db->quoteName('content_id'), $db->quoteName('meta_key'), $db->quoteName('meta_value'), $db->quoteName('encoded') ));
				$check = 0;
				foreach($attribs as $prop => $value) {
					if(strpos($prop, 'ctm_') === 0) {
						$check = 1;
						$prop = substr($prop, strlen('ctm_'));
						if(is_array($value) || is_object($value)) {
							$encoded = 1;
							$value = json_encode($value);
						} else {
							$encoded = 0;
						}
						$query->values($db->quote($row[$this->primarykey]).','.$db->quote($prop).','.$db->quote($value).','.$db->quote($encoded));
					}
				}
				if($check) {
					try {
						$db->setQuery($query);
						$db->execute();
					} catch (Exception $e) {
						//in case duplicate key when insert
						echo $e->getMessage();
					}
				}
			}
		}

        //update frontpage table to display Featured Article on the front-page
        if($row['featured'] == '1'){
            $query = $db->getQuery(true);
            $query->insert('#__content_frontpage')
                  ->columns('content_id, ordering')
                  ->values(implode(',',array($db->quote($row['id']), $db->quote($row['ordering']))));
            $db->setQuery($query);
            $db->execute();    
        }

        // Update language tag from * to default language.
        $default_lang = Factory::getLanguage();
		$update_lang = $db->getQuery(true);
		$update_lang->update('#__content')->set('language="'.$default_lang->getTag().'"')->where('id = '.$sourceid)->where('language = "*"');
		$db->setQuery($update_lang);
		$db->execute();
	}
}