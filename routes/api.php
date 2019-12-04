<?php

use danog\MadelineProto\API as MLAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::get('/hello', function(){
//    return ['hello'];
//});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/hello', function (){
   return database_path('madeline-sessions'). DIRECTORY_SEPARATOR;
});

Route::any('/channel/get-messages/{channel}', function($channel){
    if (!file_exists(__DIR__ . '/../app/Helper/madeline.php')) {
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
    }
    include __DIR__ . '/../app/Helper/madeline.php';
    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278, // a day
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];
    $MadelineProto = new API('session2.madeline', $settings);

    $MadelineProto->start();
    $MadelineProto->getDialogs();
    $offset_id = 0;
    $limit = 100;
    do {
        $messages_Messages = $MadelineProto->messages->getHistory(['peer' => $channel, 'offset_id' => $offset_id, 'offset_date' => 0, 'add_offset' => 0, 'limit' => $limit, 'max_id' => 0, 'min_id' => 0, 'hash' => 0 ]);

        if (count($messages_Messages['messages']) == 0) break;

        foreach ($messages_Messages['messages'] as $message) {
            $image = '';
            $webpage = '';
            $info = '';
            if(array_key_exists('media', $message)){
                if($message['media']['_'] === 'messageMediaPhoto') {
                    $output_file_name = $MadelineProto->downloadToDir($message['media'], __DIR__ . '/../public/mmedia/');
                    $image = '/tsetb/public/mmedia/' . basename($output_file_name);
                    $image = "<img src='$image' width='100%'>";
                }elseif($message['media']['_']==='messageMediaWebPage'){
                    $webpageMedia = $message['media']['webpage'];
                    $webpage = "<blockquote><a href='$webpageMedia[url]' title='$webpageMedia[display_url]'>$webpageMedia[site_name]</a><br>$webpageMedia[title]<br>$webpageMedia[description]</blockquote>";
                }else{
                    $info = '<pre>'.print_r($message['media'], true) . '</pre>';
                }
            }
            echo "<div style='width: 400px; max-width: 400px;'>".
                " msg-id: " . $message['id'] .
                ' <b> ' . \App\Helper\JDate::jdate('Y-m-d H:i:s', $message['date']) .
                ' - ' . date('Y-m-d H:i:s', $message['date']) ."<br>".
                $image.
                $webpage .
                $info .
                "<p style='white-space: pre-wrap'>" . @$message['message'] ."</p>".
                "</div>" .
                "<hr>";
        }

        $offset_id = end($messages_Messages['messages'])['id'];

        usleep( 250 * 1000 );
    } while (true);
});


Route::get('/tg/{phone_number}/login', function($phone_number){
    $phoneNumber = \App\PhoneNumber::where('phone_number', '=', $phone_number)->first();

    if($phoneNumber === null) {
        $phoneNumber = new \App\PhoneNumber();
        $phoneNumber->phone_number = $phone_number;
        $phoneNumber->session_name = (string) Str::uuid();
    }
    $phoneNumber->status = 'start';
    $phoneNumber->save();

    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278,
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];

    $session_file = database_path('madeline-sessions').
        DIRECTORY_SEPARATOR .
        $phoneNumber->session_name;
    $MadelineProto = new MLAPI($session_file, $settings);

    $MadelineProto->phoneLogin('+' . $phoneNumber->phone_number);

    $phoneNumber->status = 'waiting-for-verification-code';
    $phoneNumber->save();

    return ['status'=>'start'];
})->where('number', '[1-9][0-9]{11}');

Route::get('/tg/{phone_number}/complete-login/{code}', function($phone_number, $code){

    $phoneNumber = \App\PhoneNumber::where('phone_number', '=', $phone_number)->first();

    if ($phoneNumber === null) {
        return ['status' => 'first login'];
    }

    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278,
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];

    $session_file = database_path('madeline-sessions').
        DIRECTORY_SEPARATOR .
        $phoneNumber->session_name;

    $MadelineProto = new MLAPI($session_file, $settings);

    $authorization = $MadelineProto->completePhoneLogin($code);
    $MadelineProto->serialize();
    if ($authorization['_'] === 'account.password') {
        return ['status'=>'need password', 'authorization'=>$authorization];
//        $authorization = yield $MadelineProto->complete_2fa_login(yield $MadelineProto->readline('Please enter your password (hint '..'): '));
    }
    if ($authorization['_'] === 'account.needSignup') {
        return ['status'=>'need first name or optional last name', 'authorization'=>$authorization];
//        $authorization = yield $MadelineProto->complete_signup(yield $MadelineProto->readline('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
    }

    $phoneNumber->status = 'active';
    $phoneNumber->save();

    return ['status'=>'ok'];

})->where(['phone_number'=> '[1-9][0-9]{11}', 'code'=>'[0-9]{5}']);

Route::get('/tg/{phone_number}/get-myself-info', function($phone_number){

    $phoneNumber = \App\PhoneNumber::where('phone_number', '=', $phone_number)->first();

    if($phoneNumber === null || $phoneNumber->status !== 'active') {
        return ['status' => 'first login'];
    }

    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278,
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];

    $session_file = database_path('madeline-sessions') .
        DIRECTORY_SEPARATOR .
        $phoneNumber->session_name;

    $MadelineProto = new MLAPI($session_file, $settings);

    //Gets info about the currently logged-in user.
    return $MadelineProto->getSelf();

})->where(['phone_number'=> '[1-9][0-9]{11}']);

Route::get('/tg/{phone_number}/channels', function($phone_number){

    $phoneNumber = \App\PhoneNumber::where('phone_number', '=', $phone_number)->first();

    if($phoneNumber === null || $phoneNumber->status !== 'active') {
        return ['status' => 'first login'];
    }

    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278,
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];

    $session_file = database_path('madeline-sessions') .
        DIRECTORY_SEPARATOR .
        $phoneNumber->session_name;

    $MadelineProto = new MLAPI($session_file, $settings);

    //get list of all channels, groups, chats, ...
    $r = $MadelineProto->getDialogs();
    $r2 = array_filter($r, function ($item){
        return ($item['_']==='peerChannel');
    });

    $inputChannels = array_map(function($item){return 'channel#' . $item['channel_id'];}, $r2);

    $channelsInfo = $MadelineProto->channels->getChannels(['id'=>$inputChannels]);

    $channels = [];
    foreach ($channelsInfo['chats'] as $item) {
        $channels[] = ['id' => $item['id'], 'title' => $item['title'], 'username' => @$item['username']];
    }
    return $channels;
})->where(['phone_number'=> '[1-9][0-9]{11}']);

Route::any('/tg/{phone_number}/channels/{input_channel}/messages/{offset}/{limit}', function($phone_number, $input_channel, $limit, $offset){

    $phoneNumber = \App\PhoneNumber::where('phone_number', '=', $phone_number)->first();

    if($phoneNumber === null || $phoneNumber->status !== 'active') {
        return ['status' => 'first login'];
    }

    $settings = [
        'app_info' => [ // Authorization settings
            'api_id' => 1099278, // a day
            'api_hash' => 'e38f6507b19ae2fcb94a660f927b94a0',
            'device_model'=>'Desktop',
            'system_version'=>'18.0.4',
            'app_version'=>'1.0',
            'lang_code'=>'en',
        ]
    ];

    $session_file = database_path('madeline-sessions') .
        DIRECTORY_SEPARATOR .
        $phoneNumber->session_name;

    $MadelineProto = new MLAPI($session_file, $settings);

    $offset = 0;
    $limit = 30;

    //    $channel = 'channel#1351592186';
    do {
        mb_internal_encoding('UTF-8');
        $messages_Messages = $MadelineProto->messages->getHistory(['peer' => $input_channel, 'offset_id' => $offset, 'offset_date' => 0, 'add_offset' => 0, 'limit' => $limit, 'max_id' => 0, 'min_id' => 0, 'hash' => 0 ]);
//        dump($messages_Messages['messages']);
//        return;
//        echo "<pre style='font-family: Menlo, Monaco, Consolas, \"Courier New\", monospace;direction: rtl;text-align: right;'>";

        if (count($messages_Messages['messages']) == 0) break;

        foreach ($messages_Messages['messages'] as $message) {
            $image = '';
            $webpage = '';
            $info = '';
            if(array_key_exists('media', $message)){
                if($message['media']['_'] === 'messageMediaPhoto') {
                    $output_file_name = $MadelineProto->downloadToDir($message['media'], __DIR__ . '/../public/mmedia/');
                    $image = '/public/mmedia/' . basename($output_file_name);
                    $image = "<img src='$image' width='100%'>";
                }elseif($message['media']['_']==='messageMediaWebPage'){
                    $webpageMedia = $message['media']['webpage'];
                    $webpage = "<blockquote><a href='$webpageMedia[url]' title='$webpageMedia[display_url]'>$webpageMedia[site_name]</a><br>$webpageMedia[title]<br>$webpageMedia[description]</blockquote>";
                }else{
                    $info = '<pre>'.print_r($message['media'], true) . '</pre>';
                }
            }

            $msgBody = @$message['message'];

            if(array_key_exists('entities', $message)) {
                $msgBody = $message['message'];
                foreach ($message['entities'] as $entity) {
                    if ($entity['_'] === 'messageEntityHashtag') {
                        $msgBodyOffset = (mbStrlen($msgBody)-mbStrlen($message['message'])) + $entity['offset'];
                        $tag = mbSubstr($msgBody, $msgBodyOffset, $entity['length']);
                        $beforeTag = mbSubstr($msgBody, 0, $msgBodyOffset);
                        $afterTag = mbSubstr($msgBody, $msgBodyOffset+$entity['length'], null);
                        $replacement = "<span style=\"color: deepskyblue;\">$tag</span>";
                        $msgBody = $beforeTag .$replacement. $afterTag;
                    }
                }
            }

//            echo "\r\nFinal Result:\r\n<br>";
//            echo /*str_replace(["\r", "\n"], ['\r', '\n'], */nl2br($msgBody);
            $msgBody = nl2br($msgBody);

            echo "<div style='width: 400px; max-width: 400px;'>".
                " <u>msg-id: $message[id] Views: $message[views]</u><br>" .
                ' <i> ' . \App\Helper\JDate::jdate('Y-m-d H:i:s', $message['date']) .
                '  -  ' . date('Y-m-d H:i:s', $message['date']) ."</i><br>".
                $image.
                $webpage .
//                $info .
                "<p style='unicode-bidi: plaintext;'>" . $msgBody ."</p>".
////                '<pre>'.print_r($message, true).'</pre>'.
                "<hr></div>";
        }

        break;
        $offset = end($messages_Messages['messages'])['id'];

        usleep( 250 * 1000 );
    } while (true);
});
