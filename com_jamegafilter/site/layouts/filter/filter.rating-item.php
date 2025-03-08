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

?>

<li class="item rating-item rating-{options.value}">
    <label>
        <input style="" type="radio" name="{options.name}" value="{options.value}"/>
        <span class="rating-result">
            <span style="width:{options.width_rating}%"></span>
        </span>

        <span>({mids.length})</span>
    </label>
</li>
.rating-0{display: none;}