<?php


namespace AngryChimps\GeoBundle\Services;


use AngryChimps\GeoBundle\Classes\Address;
use Norm\riak\Zipcode;

class GeolocationService {
    protected static $googleMapsApiKey;
    protected static $googleMapsApiAddress;

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
        $ch = curl_init(self::$googleMapsApiAddress . '?address=' . $zip . '&key=' . self::$googleMapsApiKey);
        $json = curl_exec($ch);
        curl_close($ch);

        $address = Address::getFromGoogleMapsJson($json);

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
        $addressString = str_replace(' ', '+', $street1 . ', ' . $street2 . ',  ' . $zip);

        //Make request to google
        $ch = curl_init(self::$googleMapsApiAddress . '?address=' . $addressString . '&key=' . self::$googleMapsApiKey);
        $json = curl_exec($ch);
        curl_close($ch);

        $address = Address::getFromGoogleMapsJson($json);

        return $address;
    }

} 