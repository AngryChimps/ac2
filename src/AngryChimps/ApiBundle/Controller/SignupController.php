<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\ProviderAdService;
use AngryChimps\ApiBundle\Services\SignupService;
use AngryChimps\GeoBundle\Services\GeolocationService;
use Norm\riak\Company;
use Norm\riak\Location;
use Norm\riak\ProviderAd;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use \Norm\riak\Member;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use AngryChimps\ApiBundle\Services\AuthService;

/**
 * Class SignupController
 *
 * @Route("/signup")
 */
class SignupController extends AbstractController
{
    protected $signupService;
    protected $geolocationService;
    protected $providerAdService;
    protected $companyService;
    protected $locationService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, SignupService $signupService,
                                GeolocationService $geolocationService, ProviderAdService $providerAdService,
                                CompanyService $companyService, LocationService $locationService)
    {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->signupService = $signupService;
        $this->geolocationService = $geolocationService;
        $this->providerAdService = $providerAdService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
    }

    public function registerProviderAdAction()
    {
        $payload = $this->getPayload();

        $errors = array();
        $data = $this->signupService->registerProviderAd($payload['ad_title'], $payload['ad_description'],
            new \DateTime($payload['start']), new \DateTime($payload['end']), $payload['service_name'],
            $payload['discounted_price'], $payload['original_price'],
            $payload['mins_for_service'], $payload['mins_notice'], $payload['category_id'], $errors);

        if($data === false) {
            $error = array('code' => 'Api.SignupController.registerProviderAd.1',
                'human' => 'Validation Error',
                'debug' => $errors);
            return $this->responseService->failure(400, $error);
        }



        return $this->responseService->success($data);
    }

    public function registerProviderCompanyAction() {
        $payload = $this->getPayload();

        if($this->getAuthenticatedUser() === null) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.1',
                'human' => 'You must be authenticated to use this method');
            return $this->responseService->failure(403, $error);
        }

        if($this->getAuthenticatedUser()->status !== Member::PARTIAL_REGISTRATION_STATUS) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.2',
                'human' => 'This member has already completed registration');
            return $this->responseService->failure(403, $error);
        }

        if($payload['member_id'] != $this->getAuthenticatedUser()->id) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.3',
                'human' => 'Only the authenticated user may edit their information');
            return $this->responseService->failure(403, $error);
        }

        $company = $this->companyService->getByPk($this->getAuthenticatedUser()->managedCompanyIds[0]);

        if($company === null){
            $error = array('code' => 'Api.SignupController.registerProviderCompany.4',
                'human' => 'Unable to find the specified company');
            return $this->responseService->failure(400, $error);
        }

        $address = $this->geolocationService->lookupAddress($payload['street1'], $payload['zip']);

        if($address === null) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.5',
                'human' => 'Google Maps failed to find the specified address');
            return $this->responseService->failure(400, $error);
        }

        $location = $this->locationService->getByPk($company->locationIds[0]);

        if($location === null) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.6',
                'human' => 'Unable to find the associated location');
            return $this->responseService->failure(400, $error);
        }

        $errors = array();
        $result = $this->signupService->registerProviderCompany($this->getAuthenticatedUser(), $company,
            $location, $payload['company_name'], $payload['member_name'], $payload['email'],
            $payload['password'], new \DateTime($payload['dob']), $payload['street1'],
            $payload['street2'], $payload['zip'], $address, $payload['phone'], $payload['mobile_phone'],
            $errors);

        if(!$result) {
            $error = array('code' => 'Api.SignupController.registerProviderCompany.6',
                'human' => 'Validation Errors',
                'debug' => $errors);
            return $this->responseService->failure(400, $error);
        }

        //Publish the ad
        $this->providerAdService->publish($this->providerAdService->getProviderAd($result['providerAd']['id']));

        return $this->responseService->success($result);
    }

    public function uploadFirstAdPhotoAction() {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $photo = $this->getRequest()->files->get('photo');

        if($photo === null) {
            $error = array('code' => 'Api.SignupController.uploadFirstAdPhoto.1',
                'human' => 'No photo was attached');
            return $this->responseService->failure(400, $error);
        }

        try {
            $this->signupService->uploadPhoto($this->getAuthenticatedUser(), $photo);
        }
        catch(\Exception $ex) {
            $error = array('code' => 'Api.SignupController.uploadFirstAdPhoto.2',
                'human' => 'Unknown error occurred processing the image');
            return $this->responseService->failure(400, $error);
        }

        return $this->responseService->success();
    }
}
