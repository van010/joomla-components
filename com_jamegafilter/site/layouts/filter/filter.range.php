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

use Joomla\CMS\Language\Text;

?>
<dt role="heading" aria-level="3" class="filter-options-title"><span>{options.title}</span></dt>
<dd class="filter-options-content ">
	<div class="range-item"></div>
	<div class="range-value clearfix">
	    <label class="range-min pull-left"><?php echo Text::_('COM_JAMEGAFILTER_MIN'); ?> <span class="range-value0"></span></label>
	    <label class="range-max pull-right"><?php echo Text::_('COM_JAMEGAFILTER_MAX'); ?> <span class="range-value1"></span></label>
	</div>
</dd>