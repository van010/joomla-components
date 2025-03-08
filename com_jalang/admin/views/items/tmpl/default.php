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

if(JalangHelper::isJoomla4x()) {
	echo $this->loadTemplate('4x');
} else if (JalangHelper::isJoomla3x()) {
	echo $this->loadTemplate('3x');
} else {
	echo $this->loadTemplate('2x');
}