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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$input = Factory::getApplication()->input;
$doc = Factory::getDocument();
$currentLanguage = Factory::getLanguage();
$isRTL = $currentLanguage->isRtl();

$doc->addStyleSheet(Uri::root(true) . '/components/com_jamegafilter/assets/css/jquery-ui.min.css');
if (!defined('T3_PLUGIN')) {
	$doc->addStyleSheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}
HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');

if (!version_compare(JVERSION, '4', 'ge')){
  HTMLHelper::_('jquery.ui');
}

HTMLHelper::_('formbehavior.chosen');

if ($input->get('sticky')) {
	$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/sticky-kit.min.js');
}
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/jquery-ui.range.min.js');

if ($isRTL) {
	$doc->addStyleSheet(Uri::root(true) . '/components/com_jamegafilter/assets/css/jquery.ui.slider-rtl.css');
	$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/jquery.ui.slider-rtl.min.js');
}

$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/jquery.ui.datepicker.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/jquery.ui.touch-punch.min.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/jquery.cookie.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/script.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/libs.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/megafilter.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/main.js');
$doc->addScript(Uri::root(true) . '/components/com_jamegafilter/assets/js/dust-helpers.min.js');

Text::script('COM_JAMEGAFILTER_FROM');
Text::script('COM_JAMEGAFILTER_LOADING');