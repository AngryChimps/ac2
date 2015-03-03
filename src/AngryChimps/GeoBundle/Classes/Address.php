<?php


namespace AngryChimps\GeoBundle\Classes;


class Address {
    public $streetNumber;
    public $route;
    public $city;
    public $state;
    public $zip;
    public $lat;
    public $lon;

    public static function getFromGoogleMapsArray($arr) {
        $firstResult = $arr['results'][0];
        $components = $firstResult['address_components'];

        $address = new self();

        foreach($components as $component) {
            if(in_array('street_number', $component['types'])) {
                $address->streetNumber = $component['long_name'];
            }
            if(in_array('route', $component['types'])) {
                $address->route = $component['long_name'];
            }
            if(in_array('locality', $component['types'])) {
                $address->city = $component['long_name'];
            }
            if(in_array('administrative_area_level_1', $component['types'])) {
                $address->state = $component['short_name'];
            }
            if(in_array('postal_code', $component['types'])) {
                $address->zip = $component['long_name'];
            }
        }

        $address->lat = $firstResult['geometry']['location']['lat'];
        $address->lon = $firstResult['geometry']['location']['lng'];

        return $address;
    }
} 