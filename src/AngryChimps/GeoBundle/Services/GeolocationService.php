<?php


namespace AngryChimps\GeoBundle\Services;


use AngryChimps\GeoBundle\Classes\Address;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use AngryChimps\NormBundle\services\NormService;
use Norm\Zipcode;
use Symfony\Component\Validator\Constraints\DateTime;

class GeolocationService {
    protected $googleMapsApiKey;
    protected $googleMapsApiAddress;
    protected $googleMapsTimeAddress;
    /** @var  GuzzleService */
    protected $guzzleService;

    /** @var NormService */
    protected $norm;

    public function __construct(GuzzleService $guzzleService, $googleApiKey, $googleMapsApiAddress, $googleMapsTimeAddress,
            NormService $norm) {
        $this->guzzleService = $guzzleService;
        $this->googleMapsApiKey = $googleApiKey;
        $this->googleMapsApiAddress = $googleMapsApiAddress;
        $this->googleMapsTimeAddress = $googleMapsTimeAddress;
        $this->norm = $norm;
    }

    public function lookupZipcode($zip) {
        //Check to see if we've cached the information in norm
        $zipcode = $this->norm->getZipcode($zip);

        if($zipcode === null) {
            $zipcode = $this->lookupZipcodeFromGoogle($zip);
        }

        return $zipcode;
    }

    public function lookupAddress($street1, $zip) {
        return $this->lookupAddressFromGoogle($street1, $zip);
    }

    protected function lookupZipcodeFromGoogle($zip) {
        //Make request to google
        $url = $this->googleMapsApiAddress . '?address=' . $zip . '&key=' . $this->googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);
        $json = $response->json();
        $address = Address::getFromGoogleMapsArray($json);

        //Get timezone information
        $now = new \DateTime();
        $year = $now->format('Y');
        $dstTime = new \DateTime($year . '-07-01');
        $url = $this->googleMapsTimeAddress . '?location=' . $address->lat . ',' . $address->lon . '&timestamp='
             . $dstTime->format('U') . '&key=' . $this->googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);
        $timeData = $response->json();

        $zipcode = new Zipcode();
        $zipcode->id = $zip;
        $zipcode->city = $address->city;
        $zipcode->state = $address->state;
        $zipcode->dstTimezoneOffset = $timeData['dstOffset'];
        $zipcode->rawTimezoneOffset = $timeData['rawOffset'];
        $zipcode->timezoneId = $timeData['timeZoneId'];
        $zipcode->timezoneName = $timeData['timeZoneName'];
        $zipcode->lat = $address->lat;
        $zipcode->lon = $address->lon;
        $zipcode->northLat = $json['results'][0]['geometry']['bounds']['northeast']['lat'];
        $zipcode->southLat = $json['results'][0]['geometry']['bounds']['southwest']['lat'];
        $zipcode->eastLong = $json['results'][0]['geometry']['bounds']['northeast']['lng'];
        $zipcode->westLong = $json['results'][0]['geometry']['bounds']['southwest']['lng'];
        $this->norm->create($zipcode);

        return $zipcode;
    }

    protected function lookupAddressFromGoogle($street1, $zip) {
        $addressString = $street1 . ',  ' . $zip;

        //Make request to google
        $url = $this->googleMapsApiAddress . '?address=' . urlencode($addressString) . '&key=' . $this->googleMapsApiKey;
        $request = $this->guzzleService->createRequest('GET', $url);
        $response = $this->guzzleService->send($request);

        $address = Address::getFromGoogleMapsArray($response->json());

        return $address;
    }

} 