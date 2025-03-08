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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\LanguageHelper;

class JFormFieldCronToken extends FormField {
	
	protected $tpye="crontoken";
	
	function getInput() {
		if ($this->value) {
			$langs = LanguageHelper::getContentLanguages();
			$lang = array_shift($langs);
			$url = Uri::root() . 'index.php?option=com_jamegafilter&task=cron&token='.$this->value . '&lang=' . $lang->sef;

			$html = '<div class="well well-small" style="display:inline-block;">';
			$html .= '<a style="word-break: break-all;" target="_blank" href="' . $url . '">' . $url . '</a>';
			$html .= '</div>';
			$html .= '<input name="jform[crontoken]" type="hidden" value="'.$this->value.'"/>';
			return $html;
		}
	}
}