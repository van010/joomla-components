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

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;


HTMLHelper::_('formbehavior.chosen', 'select');
$jversion = version_compare(JVERSION, '4', '>=') ? 'j4' : 'j3';

?>
<form action="<?php echo Route::_('index.php?option=com_jamegafilter&view=cron') ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2 <?php echo $jversion ?>">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>	
		<div class="form-horizontal">
			<?php echo $this->form->renderFieldSet('config') ?>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<style>
	.j4 .sidebar-nav {
		padding-left: 0;
		padding-right: 0;
	}

	.j4 .sidebar-nav .nav {
		border-bottom: 2px solid #DEE2E6;
		display: flex;
		flex-direction: row !important;
		align-items: stretch;
	}

	.j4 .sidebar-nav .nav li {
		padding: 0 12px;
		margin-bottom: -2px;
	}

	.sidebar-nav li.active, .sidebar-nav li.item:hover {
		border-bottom: 2px solid var(--template-link-color);
		background: rgba(255,255,255,.8);
	}

	.j4 .sidebar-nav .nav a {
		font-size: 1rem;
		padding: 8px 0;
	}

	.j4 .sidebar-nav li.active a {
		color: var(--template-link-color);
		font-weight: 600;
	}

	.j4 .sidebar-nav .nav a::before {
		display: none;
	}
</style>
