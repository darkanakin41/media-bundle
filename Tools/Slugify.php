<?php

namespace PLejeune\MediaBundle\Tools;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sluggify
 *
 * @author Pierre LEJEUNE <darkanakin41@gmail.com>
 */
class Slugify {
    public static function process($text) {
        $text = str_ireplace(array("à","'"), array("a",""), $text);
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        if (function_exists('iconv')) {
            $text = iconv(ini_get("default_charset"), "UTF-8", $text);
        }
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

}
