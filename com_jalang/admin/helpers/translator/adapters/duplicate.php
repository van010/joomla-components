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

class JalangHelperTranslatorDuplicate extends JalangHelperTranslator
{
    public function __construct($parent, $db, $options = array()) {
        //$this->contentType = 'text/html';//text/plain
        parent::__construct();
    }

    public function translateArray($sentences, $fields) {
        if(!is_array($sentences)){
            $sentences = array($sentences);
        }
		$addSuffix	= $this->params->get('duplicate_language_code', 0);
        if ($this->to && $addSuffix) {
			foreach($fields as $index => $field) {
				if($field == 'title' || $field == 'name') {
					if ($addSuffix==1) {
						$sentences[$index] .= ' ('.$this->to.')';
					} else if ($addSuffix == 2) {
						$sentences[$index] .= ' ('.$this->toLangTag.')';
					}
				}
			}
        }
		return $sentences;
    }
}