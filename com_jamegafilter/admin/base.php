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
//No direct to access this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\OutputFilter;

class BaseFilterHelper {
	const ALL = '-1';
	const NONE = '0';
	const INCLUDE_ROOT = '1';
	public $params = null;
	public $_params = null;

	function __construct($params = array()){
		if (!empty($params)) {
			$this->params = $this->buildFilterParams($params['filterfields']);
		}
	}

	function buildFilterParams($data) {
		$params = array();
		foreach ($data as $value) {
			if (!is_array($value)) continue;

			foreach ($value as $v) {
				$params[] = $v;
			}
		}

		return $params;
	}
	
	public function checkPublished($field) {
		foreach ($this->params AS $param) {
			if (isset($param['field']) && $field == $param['field']) {
				if ($param['published'] || $param['sort']) {
					return 1;
				}
			}
		}
		return 0;
	}
	
	public function checkDisplayOnFO($field) {
		foreach ($this->params AS $param) {
			if (isset($param['field']) && $field == $param['field']) {
				if ($param['showoff']) {
					return 1;
				}
			}
		}
		return 0;
	}
	
	public function getFieldConfig($field) {
		foreach ($this->params AS $param) {
			if (isset($param['field']) && $field == $param['field']) {
				return $param;
			}
		}
	}

	public function generateThumb($itemId, $img, $type)
	{
		if ($this->_params->get('generate_thumb')) {
			if (preg_match('/^(http|https):\/\//', $img)) {
				return $img;
			}

			$file = JPATH_ROOT . '/' . trim($img, '/');
			if (!File::exists($file)) {
				return '';
			}

			$width = (int) $this->_params->get('thumb_width', 300);
			$width = $width < 100 ? 100 : $width;
			$height = (int) $this->_params->get('thumb_height', 300);
			$height = $height < 100 ? 100 : $height;

			$info = pathinfo($file);
			$name = "{$info['filename']}_{$width}x{$height}_";
			$name .= md5(OutputFilter::stringURLSafe($img) . '-' . $itemId);
			
			$thumb = 'images/megafilter-thumb/' . $type . '/' . $name . '.' . $info['extension'];
			$thumbPath = JPATH_ROOT . '/' . $thumb;
			if (File::exists($thumbPath)) {
				return $thumb;
			}

			$image = new \Gumlet\ImageResize($file);
			$image->resizeToBestFit($width, $height);

			Folder::create(JPATH_ROOT . '/images/megafilter-thumb/' . $type);
			$image->save($thumbPath);

			return $thumb;
		}

		return $img;
	}

}