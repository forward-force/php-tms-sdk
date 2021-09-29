<?php

require_once __DIR__ . '../../vendor/autoload.php';

use ForwardForce\TMS\TMS;
use GuzzleHttp\Exception\GuzzleException;

//assumes token is in env var, or you can pass directly
$token = getenv('TMS_API_KEY');

//instance of the TMS client
$tms = new TMS($token);

//Get all lineups by postal code
try {
    $lineups = $tms->lineups()->fetchByZipcode('USA', '78701');
    var_dump($lineups);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}

//Get airings for a lineup
try {
    $airings = $tms->lineups()->fetchAirings('USA-DTVNOW-DEFAULT', date("c", strtotime('-2 days')), 'Sm');
    var_dump($airings);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}


//Get channels for a lineup
try {
    $channels = $tms->lineups()->fetchChannels('USA-DTVNOW-DEFAULT');
    var_dump($channels);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}

//Get associated media from asset
try {
    $media = $tms->lineups()->fetchAssetFromMedia($token, 's51307_ll_h3_aa.png');
    var_dump($media);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}
