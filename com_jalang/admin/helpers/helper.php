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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class JalangHelper extends CMSObject
{
	public static $extension = 'com_jalang';

    public static function equalJoomla3x()
    {
        return version_compare(JVERSION, '3.0', 'ge') && version_compare(JVERSION, '4.0', '<');
    }

    public static function smallerThanJ4x()
    {
        return version_compare(JVERSION, '4.0', '<');
    }

	public static function isJoomla3x() {
		return version_compare(JVERSION, '3.0', 'ge');
	}

	public static function isJoomla32() {
		return version_compare(JVERSION, '3.2', 'ge');
	}

	public static function isJoomla4x() {
		return version_compare(JVERSION, '4.0', 'ge');
	}

    public static function greaterThanJoomla4x() {
		return version_compare(JVERSION, '4.0', 'ge');
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	$vName	The name of the active view.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public static function addSubmenu($vName, $layout = 'default')
	{
		return '<ul class="nav nav-tabs" id="jalang_tabs">
					<li class="nav-item '.($vName == 'tool' && $layout == 'default' ? 'active' : '').'">
						<a class="nav-link '.($vName == 'tool' && $layout == 'default' ? 'active' : '').'" href="index.php?option=com_jalang&view=tool">'.Text::_('TRANSLATE').'</a></li>
					<li class="nav-item '.($vName == 'items' ? 'active' : '').'">
						<a class="nav-link '.($vName == 'items' ? 'active' : '').'" href="index.php?option=com_jalang&view=items">'.Text::_('ASSOCIATION_MANAGER').'</a>
					</li>
					<li class="nav-item '.($vName == 'tool' && $layout == 'removelang' ? 'active' : '').'">
						<a class="nav-link '.($vName == 'tool' && $layout == 'removelang' ? 'active' : '').'" href="index.php?option=com_jalang&view=tool&layout=removelang">'.Text::_('DELETE_LANGUAGE_CONTENT').'</a>
					</li>
				</ul>';
		if(self::isJoomla3x()) {
			Sidebar::addEntry(
				Text::_('TRANSLATE'),
				'index.php?option=com_jalang&view=tool',
				$vName == 'tool' && $layout == 'default'
			);

			Sidebar::addEntry(
				Text::_('ASSOCIATION_MANAGER'),
				'index.php?option=com_jalang&view=items',
				$vName == 'items'
			);

			Sidebar::addEntry(
				Text::_('DELETE_LANGUAGE_CONTENT'),
				'index.php?option=com_jalang&view=tool&layout=removelang',
				$vName == 'tool' && $layout == 'removelang'
			);

			/*Sidebar::addEntry(
				Text::_('MOVE_LANGUAGE'),
				'index.php?option=com_jalang&view=tool&layout=movelang',
				$vName == 'tool' && $layout == 'movelang'
			);*/
		} else {
			JSubMenuHelper::addEntry(
				Text::_('TRANSLATE'),
				'index.php?option=com_jalang&view=tool',
				$vName == 'tool' && $layout == 'default'
			);

			JSubMenuHelper::addEntry(
				Text::_('ASSOCIATION_MANAGER'),
				'index.php?option=com_jalang&view=items',
				$vName == 'items'
			);

			JSubMenuHelper::addEntry(
				Text::_('DELETE_LANGUAGE_CONTENT'),
				'index.php?option=com_jalang&view=tool&layout=removelang',
				$vName == 'tool' && $layout == 'removelang'
			);

			/*JSubMenuHelper::addEntry(
				Text::_('MOVE_LANGUAGE'),
				'index.php?option=com_jalang&view=tool&layout=movelang',
				$vName == 'tool' && $layout == 'movelang'
			);*/
		}

	}
	
	public static function getHelperContent() {
		$app = Factory::getApplication();
		$itemtype = $app->getUserState('com_jalang.itemtype', 'content');
		return JalangHelperContent::getInstance($itemtype);
	}

	public static function getDefaultLanguage()
	{
		static $lang;
		// We need to go to com_languages to get the site default language, it's the best we can guess.
		if (empty($lang))
		{
			$lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}
		return $lang;
	}

	public static function getLanguage($lang_code = null) {
		if(!$lang_code) {
			$lang_code = self::getDefaultLanguage();
		}
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('extension_id'), $db->quoteName('name'), $db->quoteName('type'), $db->quoteName('element')))->from('#__extensions');
		$query->where(array($db->quoteName('type').'='.$db->quote('language'),
			$db->quoteName('client_id').'='.$db->quote('0'),
			$db->quoteName('element').'='.$db->quote($lang_code) ));
		$db->setQuery($query);
		$lang = $db->loadObject();
		return $lang;
	}

	public static function getListInstalledLanguages()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions');
		$query->where(array($db->quoteName('type').'='.$db->quote('language'),
			$db->quoteName('client_id').'='.$db->quote('0')));
		$db->setQuery($query);
		$list = $db->loadObjectList('element');
		return $list;
	}

	public static function getListLanguages($ignoreDefault = 1, $installedNote = 1) {
		static $list = null;
		if(empty($list)) {
			$db   = Factory::getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the updates table
			$query->select('name, element, version')->from('#__updates');

			// This Where clause will avoid to list languages already installed.
			//$query->where('extension_id = 0');
			$query->where($db->quoteName('type').'='.$db->quote('package'));

			$query->order($db->escape('extension_id') . ' ' . $db->escape('DESC'));
			$query->order($db->escape('name') . ' ' . $db->escape('ASC'));

			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$list = array();

			$languages = self::getListInstalledLanguages();
			$defaultLanguage = self::getDefaultLanguage();
			if(count($rows)) {
				foreach($rows as $row) {
					if(preg_match('/^pkg_([a-z]{2}-[A-Z]{2})$/', $row->element, $matches) && version_compare($row->version, '3.0', 'ge')) {
						$row->lang_code = $matches[1];
						if($ignoreDefault && $row->lang_code == $defaultLanguage) continue;
						if($installedNote && isset($languages[$row->lang_code])) {
							$row->text = sprintf('%s (%s) - %s', $row->name, $row->lang_code, Text::_('INSTALLED'));
						} else {
							$row->text = sprintf('%s (%s)', $row->name, $row->lang_code);
						}
						$list[$row->lang_code] = $row;
					}
				}
			}

			foreach($languages as $lang_code => $row) {
				if($ignoreDefault && $lang_code == $defaultLanguage) continue;
				if(!isset($list[$lang_code])) {
					$lang = new stdClass();
					$lang->name = $row->name;
					$lang->lang_code = $lang_code;
					$lang->element = 'pkg_'.$lang_code;
					$lang->version = JVERSION;
					if($installedNote) {
						$lang->text = sprintf('%s (%s) - %s', $row->name, $lang_code, Text::_('INSTALLED'));
					} else {
						$lang->text = sprintf('%s (%s)', $row->name, $lang_code);
					}
					array_unshift($list, $lang);
				}
			}
		}
		return $list;
	}

	public static function getListContentLanguages()
	{
		//static $items;
		//if (empty($items))
		//{
			// Get the database object and a new query object.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select('*')
				->from('#__languages AS a')
				->where('a.published >= 0')
				->order('a.title');

			// Set the query and load the options.
			$db->setQuery($query);
			$items = $db->loadObjectList();
		//}
		return $items;
	}

	public static function getLanguageIdFromCode($code) {
		$assoc = array();
		if($code == '*') return 0;
		//if(empty($assoc)) {
			$items = self::getListContentLanguages();
			foreach($items as $item) {
				$assoc[$item->lang_code] = $item->lang_id;
			}
		//}
		if(isset($assoc[$code])) {
			return $assoc[$code];
		} else {
			return 0;
		}
	}

	public static function createLanguageContent($lang_code) {
		if($lang_code == '*') return true;
		$data = JalangHelper::getLanguage($lang_code);
		if(!$data) return false;

		@list($lang, $country) = explode('-', $data->element);
		$sef = $image = $lang;

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		//check if language content is existed
		$query->select('lang_id, published')->from('#__languages')->where($db->quoteName('lang_code') .'='.$db->quote($lang_code));
		$db->setQuery($query);
		$test = $db->loadObject();
		if ($test)  {
			// update language to published joomla 3.7x if exists
			$query = 'UPDATE #__languages SET published=1 WHERE '.$db->quoteName('lang_code').' = '.$db->quote($data->element);
			$db->setQuery($query);
			$db->execute();
		} else {
			// if not existed -> created.
			//check if has other language with the same language code
			$query->clear('where');
			$query->where($db->quoteName('sef') .'='.$db->quote($lang));
			$db->setQuery($query);
			$test = $db->loadObject();
			if($test) {
				$sef = $image = strtolower($lang.'_'.$country);
			}

			$query = $db->getQuery(true);
			$query->insert('#__languages')
				->columns(array('lang_code','title','title_native','sef','image','published','access','ordering'))
				->values($db->quote($data->element).','.$db->quote($data->name).','.$db->quote($data->name).','.$db->quote($sef).','.$db->quote($image).','.$db->quote(1).','.$db->quote(1).','.$db->quote(0));
			$db->setQuery($query);
			$db->execute();
		}
		return true;
	}

	static public function isInstalled($extension)
	{
		$db = Factory::getDbo();
		$db->setQuery("SELECT ".$db->quoteName('extension_id')." FROM #__extensions WHERE ".$db->quoteName('type')." = 'component' AND ".$db->quoteName('element')." = ".$db->quote($extension));
		$id = $db->loadResult();
		return $id;
	}

	/**
	 * create alias suffix from language tag
	 * @param $langTag
	 */
	public static function getAliasSuffix($langTag) {
		return strtolower($langTag);
	}

	public function update() {
		$path = JPATH_COMPONENT_ADMINISTRATOR . '/installer/updates/';
		$dbPath=$path.'/mysql/';
		if(!Folder::exists($path)) {
			Folder::create($path, 0755);
		}
		$versions = array('105', '108', '109');
		foreach($versions as $version) {
			$file = $path . 'update.'.$version.'.log';
			if(!File::exists($file)) {
				$func = 'updateVersion'.$version;
				if(method_exists($this, $func)) {
					$result = call_user_func_array(array($this, $func), array());
					
					//processing code here
					$dbfile = $dbPath.$version.'.sql';
					$this->parseSQLFile($dbfile);
					
					$log = 'Updated on: '.date('Y-m-d H:i:s');
					File::write($file, $log);
				}
			}
		}
	}
	
	protected function parseSQLFile($file) {
		try {
			if(File::exists($file)) {
				$buffer = file_get_contents($file);
				if($buffer) {
					$db = Factory::getDbo();
					$queries = $db->splitSql($buffer);
					foreach ($queries as $query) {
						$query = trim($query);
						if(empty($query)) continue;

						$db->setQuery($query);
						@$db->execute();
					}
				}
			}
		} catch(Exception $e) {
			//echo $e->getMessage();
		}
	}
	
	protected function updateVersion109() {
		return true;
	}

	protected function updateVersion108() {
		/**
		 * Version 1.0.8
		 * Update content, easyblog language from 'all' to 'default'.
		 *
		 * This fix help prevent duplicate content after translate if choose re-translate when update new version or fresh install.
		 */

		$defaultLanguage = JalangHelper::getDefaultLanguage();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__content')
			->set('language="'.$defaultLanguage.'"')
			->where('language="*"');
		$db->setQuery($query);
		$db->execute();
		if (File::exists(JPATH_ADMINISTRATOR .'/components/com_easyblog/easyblog.xml')) {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR .'/components/com_easyblog/easyblog.xml');
			$version = (string)$xml->version;
			if ((int)$version >= (int)'5.0.30') {
				$db->clear();
				$query = $db->getQuery(true);
				$query->update('#__easyblog_post')
					->set('language="'.$defaultLanguage.'"')
					->where('language="*"');
				$db->setQuery($query);
				$db->execute();

				$db->clear();
				$query = $db->getQuery(true);
				$query->update('#__easyblog_revisions')
					->set('content=REPLACE(content, \'"language":"*"\', \'"language":"'.$defaultLanguage.'"\')')
					->where(''.$db->quoteName('content').' REGEXP "\"language\":\"\\*\""');
				$db->setQuery($query);
				$db->execute();

				// fix easyblog_post revision_id from old version.
				// we select every post has same revision_id and the post don't have revision_id
				$sql = 'SELECT GROUP_CONCAT(id) AS id, '.$db->quoteName('revision_id').' FROM #__easyblog_post
						GROUP BY '.$db->quoteName('revision_id').' having count(*) >= 2';
				$db->setQuery($sql);
				$lists = $db->loadObjectList();
				if (!empty($lists)) {
					$posts = array();
					foreach ($lists AS $list) {
						if (!empty($list->id)) {
							$li = explode(',', $list->id);
							sort($li);
							if (!empty($list->revision_id)) {
								unset($li[0]); // remove the post already had right revision_id.
							}
							$posts=array_merge($posts,$li);
						}
					}
					sort($posts);
					$query = $db->getQuery(true);
					$query->select('*')->from('#__easyblog_post')->where('id IN ('.implode(',', $posts).')');
					$db->setQuery($query);
					$items = $db->loadAssocList();
					foreach ($items AS $item) {
						$this->updateRevision($item);
					}
				}
			}
		}

		return true;
	}

 	public function updateRevision($row) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__easyblog_revisions')->columns('post_id, title, created, modified, created_by, content, state,ordering');
		$values = array();
		$values['post_id'] = $row['id'];
		$values['title'] = 'Initial Post';
		$values['created'] = $row['created'];
		$values['modified'] = $row['modified'];
		$values['created_by'] = $row['created_by'];
		$values['content'] = json_encode($row);
		$values['state'] = $row['state'];

		//ordering
		$sql_or = $db->getQuery(true);
		$sql_or = "SELECT MAX(ordering) FROM #__easyblog_revisions WHERE post_id = ".$db->quote($row['id']);
		$db->setQuery($sql_or);
		$ordering = $db->loadResult();
		if (!$ordering) {
			$values['ordering'] = $row['ordering'];
		} else {
			$values['ordering'] = (int)$ordering + 1;
		}

		foreach ($values as $v=>$value) {
			$values[$v] = $db->quote($value);
		}
		$query->values(implode(',',$values));
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->select('id')->from('#__easyblog_revisions')->where($db->quoteName('post_id').' = '.$db->quote($row['id']))->order('modified DESC');
		$db->setQuery($query);
		$revisionId = $db->loadResult();

		$query->clear();
		$query->update('#__easyblog_post')->set($db->quoteName('revision_id').' = '.$db->quote($revisionId))->where($db->quoteName('id') . ' = '.(int)($row['id']));
		$db->setQuery($query);
		$db->execute();
	}

	protected function updateVersion105() {
		/**
		 * Version 1.0.5
		 * Update alias of item in table type alias (E.g: menu_types table) from using language code (E.g: mainmenu-en) as alias suffix
		 * to use language tag instead (E.g: mainmenu-en-us)
		 *
		 * This fix is help to translate content into many languages that have same language code (E.g: en-US, en-GB, en-AU,...)
		 */
		$defaultLanguage = JalangHelper::getDefaultLanguage();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		//update menu_types and menu items
		$query->select('mt.id, mt.menutype, m.language')
			->from('#__menu_types AS mt')
			->innerJoin('#__menu AS m ON m.menutype = mt.menutype')
			->where('m.language <> '.$db->quote($defaultLanguage))
			->where('m.language <> '.$db->quote('*'))
			->group('mt.id, mt.menutype, m.language');
		$db->setQuery($query);
		$list = $db->loadObjectList();
		if(count($list)) {
			foreach($list as $item) {
				$langTag = $item->language;
				@list($lang, $country) = explode('-', $langTag);
				$oldmenutype = $memnutype = $item->menutype;
				//remove old suffix
				$memnutype = preg_replace('/-'.$lang.'$/', '', $memnutype);
				//add new suffix format
				$memnutype .= '-'.JalangHelper::getAliasSuffix($langTag);

				//update menu type
				$query->clear();
				$query->update('#__menu_types')
					->set($db->quoteName('menutype').'='.$db->quote($memnutype))
					->where($db->quoteName('menutype').'='.$db->quote($oldmenutype));
				$db->setQuery($query);
				$db->execute();
				//update menu item
				$query->clear();
				$query->update('#__menu')
					->set($db->quoteName('menutype').'='.$db->quote($memnutype))
					->where($db->quoteName('menutype').'='.$db->quote($oldmenutype));
				$db->setQuery($query);
				$db->execute();
				//update module
				$query->clear();
				$query->select('m.id, m.params')->from('#__modules AS m')->where('m.language = '.$db->quote($langTag));
				$db->setQuery($query);
				$modules = $db->loadObjectList();
				if(count($modules)) {
					foreach($modules as $mod) {
						$registry = new JRegistry;
						$registry->loadString($mod->params);
						$params = $registry->toArray();

						if($registry->get('menutype') == $oldmenutype) {
							$registry->set('menutype', $memnutype);

							$query->clear();
							$query->update('#__modules')
								->set($db->quoteName('params') .'='.$db->quote($registry->toString()))
								->where($db->quoteName('id').'='.$mod->id);
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
				//update template style - JA Mega menu config
				$query->clear();
				$query->select('t.id, t.params')->from('#__template_styles AS t')->where('t.client_id=0');
				$db->setQuery($query);
				$styles = $db->loadObjectList();
				if(count($styles)) {
					foreach($styles as $style) {
						$params = preg_replace('/\b'.$oldmenutype.'\b/', $memnutype, $style->params);
						if($params != $style->params) {
							$query->clear();
							$query->update('#__template_styles')
								->set($db->quoteName('params') .'='.$db->quote($params))
								->where($db->quoteName('id').'='.$style->id);
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}
		return true;
	}
}
