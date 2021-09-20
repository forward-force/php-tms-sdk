# TMS API - PHP SDK

## Installation

Install via composer as follows:
```
composer require forward-force/tms-api-sdk
```

## Usage

### Authentication

In order to authenticate, you need to pass the *PRIVATE API TOKEN* like so:

```
$tms = new TMS($token); //pv_deec47538d80245234a66e1d14d38be81
```

### Examples


Get Lineups by zip code and country (country is optional):

```php
$lineups = $tms->lineups()->fetchByZipcode('USA','78701');
```

Fetch all channels for a lineup:

```php
try {
    $channels = $tms->lineups()->fetchChannels('USA-DTVNOW-DEFAULT');
    var_dump($channels);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}

```

Retrieve an image for an asset:
```php
try{
    $media = $tms->lineups()->fetchAssetFromMedia($token, 's51307_ll_h3_aa.png');
    var_dump($media);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}

```


## Contributions

To run locally, you can use the docker container provided here. You can run it like so:

```
docker-compose up
```
There is auto-generated documentation as to how to run this library on local, please  take a look at [phpdocker/README.md](phpdocker/README.md)

*If you find an issue, have a question, or a suggestion, please don't hesitate to open a github issue.*

### Acknowledgments

Thank you to [phpdocker.io](https://phpdocker.io) for making getting PHP environments effortless! 
