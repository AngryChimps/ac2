<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\SignupService;
use Norm\riak\Company;
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

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, SignupService $signupService)
    {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->signupService = $signupService;
    }

    public function registerProviderAd()
    {
        $payload = $this->getPayload();

        $data = $this->signupService->registerProviderAd($payload['ad_title'], $payload['ad_description'],
            $payload['calendarName'], $payload['start'], $payload['end'], $payload['service_name'],
            $payload['service_description'], $payload['discounted_price'], $payload['original_price'],
            $payload['mins_for_service'], $payload['mins_notice'], $payload['category_id']);

        $this->responseService->success($data);
    }

    public function registerProviderCompany() {
        $payload = $this->getPayload();

        $company = Company::getByPk($payload['company_id']);

        if($company === null){
            $error = array('code' => 'Api.SignupController.registerProviderCompany.1',
                'human' => 'Unable to find the specified company');
            return $this->responseService->failure(400, $error);
        }

        $data = $this->signupService->registerProviderCompany($company, $payload['company_name'], $payload['member_name'],
            $payload['email'], $payload['password'], new \DateTime($payload['dob']), $payload['street1'], $payload['street2'],
            $payload['zip'], $payload['phone']);

        $this->responseService->success($data);
    }

}
