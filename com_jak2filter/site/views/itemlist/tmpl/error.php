<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;
?>
<div id="k2Container">
	<?php
	if ((!empty($this->blank_page) && empty($this->issearch)) || (!empty($this->blank_page) && !empty($this->blanktxt_after_search)))
			echo ($this->blank_txt);
	?>
	<div id="system-message-container">
		<div id="system-message">
			<div class="alert alert-notice">
				<a class="close" data-dismiss="alert">×</a>
				<h4 class="alert-heading"><?php echo JText::_('JNOTICE'); ?></h4>
				<div>
					<div class="alert-message"><?php echo JText::_('SEARCH_RESULT_NULL'); ?></div>
				</div>
			</div>
		</div>
	</div>
</div>