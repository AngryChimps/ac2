<?php


namespace AngryChimps\GeoBundle\Services;


use AngryChimps\GeoBundle\Classes\Address;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Norm\riak\Zipcode;

class GeolocationService {
    protected static $googleMapsApiKey;
    protected static $googleMapsApiAddress;
    /** @var  GuzzleService */
    protected $guzzleService;

    public function __construct(GuzzleService $guzzleService) {
        $this->guzzleService = $guzzleService;
    }

    public function lookupZipcode($zip) {
        //Check to see if we've cached the information in riak
        $zipcode = Zipcode::getByPk($zip);

        if($zipcode === null) {
            $zipcode = $this->lookupZipcodeFromGoogle($zip);
        }

        return $zipcode;
    }

    public function lookupAddress($street1, $street2, $zip) {
        return $this->lookupAddressFromGoogle($street1, $street2, $zip);
    }

    public static function setGoogleMapsApiKey($apiKey) {
        self::$googleMapsApiKey = $apiKey;
    }

    public static function setGoogleMapsApiAddress($address) {
        self::$googleMapsApiAddress = $address;
    }

    protected function lookupZipcodeFromGoogle($zip) {
        //Make request to google
        $url = self::$googleMapsApiAddress . '?address=' . $zip . '&key=' . self::$googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);

        $address = Address::getFromGoogleMapsJson($response);

        $zipcode = new Zipcode();
        $zipcode->id = $zip;
        $zipcode->city = $address->city;
        $zipcode->state = $address->state;
        $zipcode->lat = $address->lat;
        $zipcode->long = $address->long;
        $zipcode->save();

        return $zipcode;
    }

    protected function lookupAddressFromGoogle($street1, $street2, $zip) {
        $addressString = $street1 . ', ' . $street2 . ',  ' . $zip;

        //Make request to google
        $url = self::$googleMapsApiAddress . '?address=' . $addressString . '&key=' . self::$googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);

        $address = Address::getFromGoogleMapsArray($response->json());

        return $address;
    }

} 