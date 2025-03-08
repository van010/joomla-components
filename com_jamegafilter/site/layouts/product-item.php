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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

 ?>
{#data}
<div class="item product product-item col">
    <div data-container="product-grid" class="product-item-info">

        <div class="product-item-details">
            <a tabindex="-1" class="product-item-photo" href="{url}">
                <span class="product-image-container">
                    <span class="product-image-wrapper">
                        <img alt="{name|s}" src="<?php echo Uri::base(true).DS; ?>{thumbnail}" class="product-image-photo">
                    </span>
                </span>
            </a> 

            <div class="product-reviews-summary short">
                <div class="rating-summary">
                    {?rating}
                    <div title="{rating} out of 5" class="rating-result">
                        <span style="width:{width_rating}%"></span>
                    </div>
                    {:else}
                    <div title="0%" class="rating-result">
                        <span style="width:0%"></span>
                    </div>
                    {/rating}
                </div>
            </div>

            <h4 class="product-item-name">
                <a href="{url}" class="product-item-link">
                    {name|s}
                </a>
            </h4>

            <div data-product-id="{id}" data-role="priceBox" class="price-box price-final_price">
                <span class="price-container price-final_price tax weee">
                    <span class="price-wrapper " data-price-type="finalPrice" data-price-amount="{price}" id="product-price-{id}">
                        <span class="price">{frontend_price|s}</span>
                    </span>
                </span>
            </div>
        </div>

        <div class="product-item-actions">
            {?on_sale}

            <div class="addtocart-area">
                <form method="post" class="product js-recalculate" action="<?php echo Route::_ ('index.php?option=com_virtuemart',false); ?>">
                    <div class="addtocart-bar">
                        <span class="quantity-box">
                            <input class="quantity-input js-recalculate" name="quantity[]" data-errstr="<?php echo Text::_('COM_JAMEGAFILTER_ERROR_CAN_ONLY_BUY'); ?> %s <?php echo Text::_('COM_JAMEGAFILTER_ERROR_CAN_ONLY_BUY_PIECES'); ?>!" value="1" init="1" step="1" type="text">
                        </span>
                        <span class="quantity-controls js-recalculate">
                            <input class="quantity-controls quantity-plus" type="button">
                            <input class="quantity-controls quantity-minus" type="button">
                        </span>
                        <span class="addtocart-button">
                            <input name="addtocart" class="btn btn-default" value="<?php echo Text::_('COM_JAMEGAFILTER_ADD_TO_CART'); ?>" title="<?php echo Text::_('COM_JAMEGAFILTER_ADD_TO_CART'); ?>" type="submit">
                        </span>
                            <input name="virtuemart_product_id[]" value="{virtuemart_product_id}" type="hidden">
                            <input type="hidden" name="task" value="add"/>
                    </div> 
                    <input type="hidden" name="option" value="com_virtuemart"/>
                    <input type="hidden" name="view" value="cart"/>
                    <input type="hidden" name="virtuemart_product_id[]" value="{virtuemart_product_id}"/>
                    <input type="hidden" name="pname" value="{name}"/>
                    <input type="hidden" name="pid" value="{virtuemart_product_id}"/>
                </form>
            </div>
            {:else}
                {?is_salable}
                    <div class="stock available"><span><?php echo Text::_('COM_JAMEGAFILTER_IN_STOCK'); ?></span></div>
                {:else}
                    <div class="stock unavailable"><span><?php echo Text::_('COM_JAMEGAFILTER_OUT_STOCK'); ?></span></div>
                {/is_salable}
            {/on_sale}
        </div>
    </div>
</div>
{/data}