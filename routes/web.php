<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
function mb_substr_replace ($string, $replacement, $start, $length = 0)
{
    $result = '';
    if($start != 0) {
        $result = mb_substr($string, 0, $start-1, 'UTF-8');
        echo "'" . str_replace(["\r", "\n"], ['\r', '\n'], $result) . "'\r\n";
    }
    $result .= $replacement;

    if ($length > 0) {
        $lastPart = mb_substr($string, ($start+$length-1), mb_strlen($string, 'UTF-8'), 'UTF-8');
        echo "'" . str_replace(["\r", "\n"], ['\r', '\n'], $lastPart) . "'\r\n";
        $result .= $lastPart;
    }

    return $result;
}
Route::get('/home', function(){

//    $msgBody = '#فاراک
//#فاذر
//در منفی ها برسی شوند
//
//98/3/25
//
//⏳ @BORSANJ';

    $msgBody = "👆 #ختور\nدو ماه قبل روزی که از 1000 تومن با رنج منفی تا محدوده 970 پایین اومد در ویژه برای هدف 1400 کوتاه مدت معرفی شد.\nبعد از ریزش شدید بازار بعد از 10 مهر تا محدوده 800 هم نزول کرد اما نهایتا بعد از خوابیدن هیجانات بازار به مسیر واقعی خود برگشت و تا امروز حدود 40 درصد بازدهی داشته.\n👇اپدیت";


    echo str_replace(["\r", "\n"], ['\r', '\n'], $msgBody);
    echo "\r\n\r\n\r\n\r\n\r\n\r\n";

//    $entities = [
//    0 => [
//          "_" => "messageEntityHashtag",
//          "offset" => 0,
//          "length" => 6
//        ],
//        1 => [
//          "_" => "messageEntityHashtag",
//          "offset" => 7,
//          "length" => 5
//        ],
//        2 => [
//          "_" => "messageEntityMention",
//          "offset" => 47,
//          "length" => 8
//]];
    $entities = [
        0 =>  [
            "_" => "messageEntityHashtag",
            "offset" => 3,
            "length" => 5
        ]
    ];
    return "'" . mb_substr($msgBody, $entities[0]['offset'], $entities[0]['length'], 'UTF-8') . "'";
    $offsetBase = 0;
    $i =1;
    foreach ($entities as $entity) {
//        if($entity['_']==='messageEntityHashtag'){
            echo "\r\n============== Iteration: $i ================ \r\n";$i++;
            $offset = $entity['offset'] + $offsetBase;
            $tag = mb_substr($msgBody, $offset, $entity['length'], 'UTF-8');
            echo "offsetBase: $offsetBase, Offset: $offset, Length: $entity[length]" . "\r\n";

            echo str_replace(["\r", "\n"], ['\r', '\n'], $tag) . "\r\n";
//            $replacement = str_repeat('آ', $entity['length']);
            $replacement = "<span style=\"color: deepskyblue;\">$tag</span>";

            echo str_replace(["\r", "\n"], ['\r', '\n'], $replacement) . "\r\n";

            $t = (mb_strlen($replacement, 'UTF-8') - mb_strlen($tag, 'UTF-8'));

            $msgBody = mb_substr_replace($msgBody, $replacement, $offset, $entity['length']);
            $offsetBase += $t;

            echo $t."\r\nResult:\r\n";
            echo str_replace(["\r", "\n"], ['\r', '\n'], $msgBody);
//        }
    }

    echo ("\r\n\r\n\r\n\r\n\r\n\r\n");
//    echo $msgBody;

//            $msgBody = nl2br($msgBody);
    echo str_replace(["\r", "\n"], ['\r', '\n'], $msgBody);

//    echo "<p style='unicode-bidi: plaintext;'>" . $msgBody ."</p>";

//    'HomeController@index'
})->name('home');
