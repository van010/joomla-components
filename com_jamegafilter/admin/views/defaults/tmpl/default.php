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
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$jversion = version_compare(JVERSION, '4', '>=') ? 'j4' : 'j3';
?>
<form action="index.php?option=com_jamegafilter&view=defaults" method="post" id="adminForm" name="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2 <?php echo $jversion ?>">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>	
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="1%"><?php echo Text::_('COM_JAMEGAFILTER_NUM'); ?></th>
			<th width="2%">
				<?php echo HTMLHelper::_('grid.checkall'); ?>
			</th>
			<th width="90%">
				<?php echo Text::_('COM_JAMEGAFILTER_NAME') ;?>
			</th>
			<th width="5%">
				<?php echo Text::_('COM_JAMEGAFILTER_TYPE'); ?>
			</th>
			<th width="5%">
				<?php echo Text::_('COM_JAMEGAFILTER_PUBLISHED'); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = Route::_('index.php?option=com_jamegafilter&task=default.edit&id=' . $row->id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
						</td>
						<td>
							<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JAMEGAFILTER_EDIT'); ?>">
								<?php echo $row->title; ?>
							</a>
						</td>
						<td align="center">
							<?php echo $row->type; ?>
						</td>
						<td align="center">
							<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'defaults.', true, 'cb'); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
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
