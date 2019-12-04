<?php


namespace App\Helper;


class Persian
{
    public static function arabicLetters2Farsi($str)
    {
        //replace('ي', 'ی')
        //replace('ك', 'ک')
        return str_replace(['ي', 'ك'], ['ی', 'ک'], $str);
    }
}
