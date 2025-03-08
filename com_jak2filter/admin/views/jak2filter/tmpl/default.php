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

// No direct access.
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::script('https://cdnjs.cloudflare.com/ajax/libs/tingle/0.16.0/tingle.min.js');
JHtml::stylesheet('https://cdnjs.cloudflare.com/ajax/libs/tingle/0.16.0/tingle.min.css');

$params = JComponentHelper::getParams('com_jak2filter');
$key = $params->get('indexing_cron_key', 'indexing');
$cronUrl = JURI::root() . 'index.php?option=com_jak2filter&view=cron&jakey=' . $key;
?>
<h1>JA K2 Filter Component for Joomla 2.5.x and Joomla 3.x</h1>
<div style='font-weight: normal'>
	<p><span style='color: #ff6600;'><strong>Features:</strong></span>
		JA K2 Filter Component</p>

	<strong><span style='color: #ff0000;'>Usage Instruction:</span></strong><br />
	<ul>
		<li>Enable JA K2 Filter Module in Module Manager</li>
	</ul>
	<strong><span style='color: #ff0000;'>Upgrade Method:</span><br /></strong>
	<ul>
		<li>You can install new version directly over this version. Uninstallation is not required. </li>
	</ul>

	<span style='color: #008000;'><strong>Links:</strong></span><br />
	<ul>
		<li><a target="_blank" href="http://www.joomlart.com/joomla/extensions/ja-k2-search">Wiki Userguide</a></li>
		<li><a target='_blank' href='http://www.joomlart.com/forums/downloads.php?do=cat&id=20372'>Updates &amp; Versions</a></li>
	</ul>
	<p>Copyright 2004 - 2019 <a href='http://www.joomlart.com/' title='Visit Joomlart.com!'>JoomlArt.com</a>.</p>
</div>

<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == "reindexing") {
			var modal = new tingle.modal({
				closeMethods: ['overlay', 'button', 'escape'],
				closeLabel: "Close",
				cssClass: ['index-modal'],
				onClose: function() {
					jQuery('.index-modal').remove();
				},
			});

			// set content
			modal.setContent(`<iframe style="width: 100%; height: 300px;" src="<?php echo $cronUrl ?>" frameBorder="0"></iframe>`);

			// open modal
			modal.open();
		} else {
			Joomla.submitform(pressbutton);
		}
	}
</script>
<style>
.tingle-modal {
	backdrop-filter: unset;
}

.tingle-modal-box {
	max-width: 600px;
}
</style>