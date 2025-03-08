<?php
/**
 * ------------------------------------------------------------------------
 * JA Megafilter Component
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class JaMegaFilterModelDefault extends AdminModel
{
	public function getTable($type = 'JaMegaFilter', $prefix = 'JaMegaFilterTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$jinput = Factory::getApplication()->input;
		$type = $jinput->get('type', 0);
		$item = $this->getItem();
		if (empty($type)) $type = $item->type;
		if (!empty($type)) {
			$lang = Factory::getLanguage();
			$extension = 'plg_jamegafilter_'.$type;
			$language_tag = Factory::getLanguage()->getTag();
			$lang->load($extension, JPATH_ADMINISTRATOR, $language_tag, true);
			$xml = JPATH_PLUGINS.'/jamegafilter/'.$type.'/forms/'.$type.'.xml';
			if(File::exists($xml)){
				// get form from third party
				Form::addFieldPath(JPATH_PLUGINS.'/jamegafilter/'.$type.'/fields/');
				$options = array('control' => 'jform', 'load_data' => $loadData);
				$form = Form::getInstance('jform', $xml, $options);
			}
		}
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	function saveobj()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$post = $jinput->get('jform', array(), 'array');
		$table = $this->getTable();
		$table->type = $post['jatype'];
		$table->published = $post['published'];
		$table->title = $post['title'];

		PluginHelper::importPlugin('jamegafilter');
		$app->triggerEvent('onBeforeSave'.ucfirst($post['jatype']).'Items', array( &$post ));

		$table->params = json_encode($post);
		if (!empty($post['id'])) {
			$table->id = $post['id'];
		}

		if (!$table->store()){
			$app->enqueueMessage($table->getError(), 'warning');
			// incase users have too many custom fields > 300 fields so the characters of text type will no be enough
			$db = Factory::getDbo();
			$query = 'ALTER TABLE `#__jamegafilter` MODIFY COLUMN `params` MEDIUMTEXT;';
			$db->setQuery($query);
			$db->execute();
		}

		$this->proxyExport($table->id);

		return $table;
	}

	function proxyExport($id)
	{
		$params = ComponentHelper::getParams('com_jamegafilter');
		$cronurl = Uri::root() . 'index.php?option=com_jamegafilter&task=cron&token=' . $params->get('crontoken');
		if ($cronurl) {
			$langs = LanguageHelper::getContentLanguages();
			$lang = array_shift($langs);
            $menuId = $this->getPublicMenu();
            $uri = $cronurl . '&proxy=1&id=' . $id . '&lang=' . $lang->sef . '&Itemid='.$menuId;
			$options = new Registry;
			$options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');
			try
			{
				$response = HttpFactory::getHttp($options)->get($uri);
				$data = @json_decode($response->body);
				if (is_object($data) && isset($data->success)) {
					Factory::getApplication()->enqueueMessage($data->success);
				} else {
					die($response->body);
				}
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException('Unable to open cron url: ' . $e->getMessage(), $e->getCode());
			}

			if ($response->code != 200)
			{
				throw new RuntimeException('Unable to open cron url: Response Code ' . $response->code);
			}
		}
	}
	
	function exportByID($id)
	{
		$libPath = JPATH_ADMINISTRATOR . '/components/com_jamegafilter/assets/gumlet-image-resize/';
		require_once $libPath . 'ImageResize.php';
		require_once $libPath . 'ImageResizeException.php';

		$app = Factory::getApplication();
		$item = $this->getItem($id);
		$isEnable = PluginHelper::isEnabled('jamegafilter', $item->type);
    
		if (!$isEnable) {
			$msg = array(
				'success' => 'Please enable ' . ucfirst($item->title) . ' Plugin',
			);
			die(json_encode($msg));
			/*$app->enqueueMessage(JTEXT::_('COM_JAMEGAFILTER_EXPORT_FAILED_FILTER_PLUGIN_NOT_FOUND').' : '.strtoupper($item->type), 'error');
			return false;*/
		}

		PluginHelper::importPlugin('jamegafilter');
		
		$path = JPATH_SITE.'/media/com_jamegafilter/';
		if(!Folder::exists($path)) {
			Folder::create($path, 0755);
		}

    	$result = $app->triggerEvent('onAfterSave'.ucfirst($item->type).'Items', array($item));
		$objectList = $result[0];
  
		foreach ($objectList as $key => $object) {
      		$object = $this->checkObject($object, $path, $id);
      		$json = json_encode($object);

			if (!File::write($path.$key.'/'.$id.'.json', $json)) {
				$msg = array(
					'success' => "Can't write data into Json file" ,
				);
				die(json_encode($msg));
				/*$app->enqueueMessage(JTEXT::_('COM_JAMEGAFILTER_CAN_NOT_EXPORT_JSON_TO_FILE').':'.$path.$key.'/'.$id.'.json', 'error');
				return false;*/
			}
    	}
		return true;
	}

    /**
     * get a menu id with public access and publish status
     * fix can not index or save a filter when an user set access for main menu != publish
     *
     * @return mixed|null
     */
    public function getPublicMenu()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')
            ->from('`#__menu`')
            ->where('`published` = 1')
            ->where('`access` = 1');
        $db->setQuery($query);
        return $db->loadResult();
    }
  
  	public function checkObject($obj, $path, $id){
		if (json_encode($obj) !== false){
		return $obj;
		}
		
		$log = '';
		$obj1 = clone $obj;
		
		foreach ($obj1 as $key => $val){
		if (json_encode($val) == false){
			$log .= $this->logLine($key, $val);
		}
		foreach ($val as $k => $v){
			if (json_encode($v) == false){
			$log .= $this->logLine($k, $v);
			if (!is_array($val->$k)){
				$val->$k = (string) $v;
			}else{
				foreach ($val->$k as $k1 => $v1){
				if (json_encode($v1) == false){
					$log .= $this->logLine($k1, $v1);
					if (!is_array($v1)){
					$val->$k[$k1] = (string) $v1;
					}
					foreach ($v1 as $k2 => $attr){
					if (json_encode($attr) == false){
						$log .= $this->logLine($k2, $attr);
						if (!is_array($attr)){
						$val->$k[$k1][$k2] = (string) $attr;
						}
					}
					}
				}
				}
			}
			}
		}
		}
		File::write($path.'/log/log_'.$id.'.txt', $log);
		return $obj1;
	}

	public function logLine($key, $val){
		if (!is_object($val) && !is_array($val)){
		return "key error: ". $key . " | value: ". $val . " | value type: " . gettype($val) . "\n";
		}
		return "key error: ". $key . " | value type: " . gettype($val) . "\n";
	}
}