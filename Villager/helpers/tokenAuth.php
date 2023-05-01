<?php
require_once './vendor/autoload.php';


use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

//Test class for verifying idTokens sent from client SDK Firebase

$serviceAccount = ServiceAccount::fromValue('F:\Projects\Villager\local_files\villager-564a6-firebase-adminsdk-q87rq-ad7212cea5.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount);

$auth = $firebase->createAuth();

//Input idToken from client here
$idToken = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImI2NzE1ZTJmZjcxZDIyMjQ5ODk1MDAyMzY2ODMwNDc3Mjg2Nzg0ZTMiLCJ0eXAiOiJKV1QifQ.eyJuYW1lIjoic2V2ZW56enoiLCJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vdmlsbGFnZXItNTY0YTYiLCJhdWQiOiJ2aWxsYWdlci01NjRhNiIsImF1dGhfdGltZSI6MTY4Mjg5NjU5NSwidXNlcl9pZCI6IjB4WmlIMnNlbWlhNldNYzhoUWpxdEJUQWh3ODMiLCJzdWIiOiIweFppSDJzZW1pYTZXTWM4aFFqcXRCVEFodzgzIiwiaWF0IjoxNjgyODk2NTk5LCJleHAiOjE2ODI5MDAxOTksImVtYWlsIjoic2V2ZW5za2lsbHpAaG90bWFpbC5jYSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJlbWFpbCI6WyJzZXZlbnNraWxsekBob3RtYWlsLmNhIl19LCJzaWduX2luX3Byb3ZpZGVyIjoicGFzc3dvcmQifX0.hQG_V1oJulzkN_TewdoILjysQ3TP-A6D8pYjEy9y_0xwfK8MvtzKQPVBcrv6xjq3EoHhcKzdmtnLw0uFS7SFLxOi1jk0pbScxdirGlR20YbgQVhHwwkSoTRxOLoX3PRCsFgtfA-3o__sWIbJ7sF2f-XIWQH7K5jrA5877JltiALKHEC_t2yMmcepiO6_Zy9Q1yBcNuGZbpJM641yf7aWQWNudij42KB-l9xKt6QxA1Lq6hmNJg3EM-_6K4emlh5Fh_TYVht6UOGCSZ2JdAl8nT6CADq9bx70itg8hMslQUAguU8TmBkDsidHgtsuKtkA2ba2jB7KXLE_mwpZre6gMQ';

try {
    $token = $auth->verifyIdToken($idToken, true);
    /** @var .\Kreait\Firebase\Auth\IdToken $token */

    //attempt to verify this??
    echo("success");
    print_r( $token->claims()->get('sub') );

    if( $token->isExpired(new DateTime())){
        echo ("true");
    }
} catch (Exception $e) {
    echo "Authentication failed: " . $e->getMessage();
}
?>
