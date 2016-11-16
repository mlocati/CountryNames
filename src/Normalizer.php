<?php

namespace MLocati\CountryNames;

class Normalizer
{
    public static function normalize($string)
    {
        $string = str_replace('&', ' and ', $string);
        $string = str_replace(array('’', "\xA0", "'", '"', '.'), ' ', $string);
        $string = trim(preg_replace('/[\x00-\x20]+/', ' ', $string));
        $string = mb_strtolower($string, 'UTF-8');
        $string = str_replace(array('å', 'à', 'ã'), 'a', $string);
        $string = str_replace(array('ç'), 'c', $string);
        $string = str_replace(array('é', 'è'), 'e', $string);
        $string = str_replace(array('í', 'ì'), 'i', $string);
        $string = str_replace(array('òô'), 'o', $string);
        $string = str_replace(array('ù'), 'u', $string);

        return ($string === '') ? '' : ("\x00".$string."\x00");
    }
}
