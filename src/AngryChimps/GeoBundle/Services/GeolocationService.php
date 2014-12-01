<?php


namespace AngryChimps\GeoBundle\Services;


use AngryChimps\GeoBundle\Classes\Address;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Norm\riak\Zipcode;

class GeolocationService {
    protected $googleMapsApiKey;
    protected $googleMapsApiAddress;
    /** @var  GuzzleService */
    protected $guzzleService;

    public function __construct(GuzzleService $guzzleService, $googleApiKey, $googleMapsApiAddress) {
        $this->guzzleService = $guzzleService;
        $this->googleMapsApiKey = $googleApiKey;
        $this->googleMapsApiAddress = $googleMapsApiAddress;
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

    protected function lookupZipcodeFromGoogle($zip) {
        //Make request to google
        $url = $this->googleMapsApiAddress . '?address=' . $zip . '&key=' . $this->googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);
        $json = $response->json();
        $address = Address::getFromGoogleMapsArray($json);

        $zipcode = new Zipcode();
        $zipcode->id = $zip;
        $zipcode->city = $address->city;
        $zipcode->state = $address->state;
        $zipcode->lat = $address->lat;
        $zipcode->long = $address->long;
        $zipcode->northLat = $json['results'][0]['geometry']['bounds']['northeast']['lat'];
        $zipcode->southLat = $json['results'][0]['geometry']['bounds']['southwest']['lat'];
        $zipcode->eastLong = $json['results'][0]['geometry']['bounds']['northeast']['lng'];
        $zipcode->westLong = $json['results'][0]['geometry']['bounds']['southwest']['lng'];
        $zipcode->save();

        return $zipcode;
    }

    protected function lookupAddressFromGoogle($street1, $street2, $zip) {
        $addressString = $street1 . ', ' . $street2 . ',  ' . $zip;

        //Make request to google
        $url = $this->googleMapsApiAddress . '?address=' . urlencode($addressString) . '&key=' . $this->googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);

        $address = Address::getFromGoogleMapsArray($response->json());

        return $address;
    }

} 