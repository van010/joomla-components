<?php
/**
 * ------------------------------------------------------------------------
 * JA Multilingual J2x-J3x.
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;
?>
<div class="container-fluid container-main">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12 form-horizontal">
			<div class="tab-pane active" id="item-data">
				<div class="row-fluid">
					<div class="span12">
						<?php if($this->item): ?>
							<?php foreach($this->item as $key => $value): ?>
								<?php if($this->primarykey && $key == $this->primarykey): ?>
									<div class="control-group">
										<label title="" class="control-label" for="jform_<?php echo $key ?>" id="jform_<?php echo $key ?>-lbl">ID</label>
										<div class="controls">
											<div id="jform_<?php echo $key ?>"><?php echo $value ?></div>
										</div>
									</div>
								<?php elseif($this->alias_field && $key == $this->alias_field): ?>
									<div class="control-group">
										<label title="" class="control-label" for="jform_<?php echo $key ?>" id="jform_<?php echo $key ?>-lbl">Alias</label>
										<div class="controls">
											<div id="jform_<?php echo $key ?>"><?php echo $value ?></div>
										</div>
									</div>
								<?php elseif(isset($this->reference_fields[$key])): ?>
									<?php if(isset($this->item->{$key.'_ref'})): ?>
										<div class="control-group">
											<label title="" class="control-label" for="jform_<?php echo $key ?>" id="jform_<?php echo $key ?>-lbl"><?php echo $key; ?></label>
											<div class="controls">
												<div id="jform_<?php echo $key ?>"><?php echo $this->item->{$key.'_ref'} ?> (<?php echo $value ?>)</div>
											</div>
										</div>
									<?php endif; ?>
								<?php elseif(in_array($key, $this->translate_fields)): ?>
									<div class="control-group">
										<label title="" class="control-label" for="jform_<?php echo $key ?>" id="jform_<?php echo $key ?>-lbl"><?php echo $key; ?></label>
										<div class="controls">
											<div id="jform_<?php echo $key ?>"><?php echo $value ?></div>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>

					</div>
				</div>

			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('label').each(function(index){
			if(jQuery('#'+jQuery(this).attr('id'), window.opener.document)) {
				jQuery(this).html(jQuery('#'+jQuery(this).attr('id'), window.opener.document).html());
			}
		});
	});

</script>