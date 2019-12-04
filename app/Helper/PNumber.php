<?php


namespace App\Helper;


class PNumber
{
    public static function en2FaDigit($str)
    {
        $num_en = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.');
        $num_fa = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', ',');
        return str_replace($num_en, $num_fa, $str);
    }

    public static function fa2EnDigit($str)
    {
        $num_en = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.');
        $num_fa = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', ',');
        return str_replace($num_fa, $num_en, $str);
    }
}
