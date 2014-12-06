<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Ad;
use Norm\riak\Calendar;
use Norm\riak\Company;
use Norm\riak\Location;
use Norm\riak\Member;
use Norm\riak\ProviderAd;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProviderAdService {
    protected $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    public function create($adTitle, $adDescription, Company $company,
                           Location $location, Calendar $calendar, $category, array &$errors) {
        $ad = new ProviderAd();
        $ad->title = $adTitle;
        $ad->description = $adDescription;
        $ad->companyId = $company->id;
        $ad->locationId = $location->id;
        $ad->calendarId = $calendar->id;
        $ad->categoryId = $category;
        $ad->status = ProviderAd::UN_PUBISHED_STATUS;

        $errors = $this->validator->validate($ad);

        if(count($errors) > 0) {
            return false;
        }

        $ad->save();

        return $ad;
    }


}