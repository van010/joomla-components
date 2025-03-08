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

$jinput = Factory::getApplication()->input;
$itc     = $jinput->get('itempercol', 5, 'INT');
?>
<div class="ja-toolbar-wrapper toolbar toolbar-products toolbar-wrapper toolbar-top">

</div>

<div class="ja-products-wrapper products wrapper grid products-grid cols-<?php echo $itc; ?>">
	<div class="row products list items product-items clearfix cols-<?php echo $itc; ?>"></div>
</div>

<div class="ja-toolbar-wrapper toolbar toolbar-products toolbar-wrapper toolbar-bottom">
</div>