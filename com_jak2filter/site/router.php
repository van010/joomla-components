<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

function JAK2FilterBuildRoute(&$query){
	$segments = array();
	
	if (isset($query['view'])) {
		if($query['view'] != 'itemlist') {
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}
	return $segments;
}

function JAK2FilterParseRoute($segments) {
	$vars = array();
	if(count($segments)) {
		switch($segments[0]) {
			case 'itemlist':
				$vars['view'] = 'itemlist';
				break;
			case 'suggestions':
				$vars['view'] = 'suggestions';
				break;
			case 'cron':
				$vars['view'] = 'cron';
				break;

		}
	} else {
		$vars['view'] = 'itemlist';
	}
	return $vars;
}