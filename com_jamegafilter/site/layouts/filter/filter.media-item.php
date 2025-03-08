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

use Joomla\CMS\Uri\Uri;

?>
<li class="item media-item">
	<label>
		<input style="display:none;" type="checkbox" name="{options.name}" value="{options.value}" />
		<img class="img-item" src="<?php echo Uri::root(true) ?>/{options.frontend_value|s}" />
		<div class="item-counter" style="text-align: center">({mids.length})</div>
	</label>
</li> 