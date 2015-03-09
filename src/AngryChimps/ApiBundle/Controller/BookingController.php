<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\BookingService;
use AngryChimps\ApiBundle\Services\PurchaseService;
use AngryChimps\ApiBundle\Services\ServiceService;
use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Availability;
use Norm\riak\Booking;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\ProviderAdImmutableService;
use AngryChimps\ApiBundle\Services\CalendarService;

class BookingController extends AbstractController
{
    protected $bookingService;
    protected $providerAdImmutableService;
    protected $calendarService;
    protected $serviceService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, BookingService $bookingService,
                                ProviderAdImmutableService $providerAdImmutableService,
                                CalendarService $calendarService, ServiceService $serviceService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->bookingService = $bookingService;
        $this->providerAdImmutableService = $providerAdImmutableService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
    }

    public function indexGetAction($bookingId) {
        $booking = $this->bookingService->getBookingData($bookingId);

        if($booking === null) {
            $error = array('code' => 'Api.PurchaseController.indexGetAction.1',
                'human' => 'Unable to find the given booking record');
            return $this->responseService->failure(404, $error);
        }

    }

    public function indexPostAction() {
        $payload = $this->getPayload();
        $providerAdImmutable = $this->providerAdImmutableService->getProviderAdImmutable($payload['provider_ad_immutable_id']);

        if($providerAdImmutable === null) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.1',
                'human' => 'Unable to find the given immutable provider ad');
            return $this->responseService->failure(400, $error);
        }

        $service = $this->serviceService->getService($payload['service_id']);

        if($providerAdImmutable === null) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.2',
                'human' => 'Unable to find the given service');
            return $this->responseService->failure(400, $error);
        }

        if(!isset($payload['stripe_token'])) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.3',
                'human' => 'A payment token is required');
            return $this->responseService->failure(400, $error);
        }

        $now = new \DateTime();
        $avail = new Availability();
        $avail->start = new \DateTime($payload['starting_at']);
        $avail->end = new \DateTime($payload['ending_at']);
        $calendar = $this->calendarService->getCalendar($providerAdImmutable->calendar->id);

        if($avail->start < $now->add(new \DateInterval('PT' . $service->minsNotice . 'M'))) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.4',
                'human' => 'Service requires more notice to book');
            return $this->responseService->failure(400, $error);
        }

        if($avail->start->add(new \DateInterval('PT' . $service->minsForService . 'M'))
                != $avail->end) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.5',
                'human' => 'The availability does not match the number of minutes required for the service');
            return $this->responseService->failure(400, $error);
        }

        if(!$this->bookingService->verifyStripeToken($payload['stripe_token'])) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.6',
                'human' => 'Unable to verify stripe token');
            return $this->responseService->failure(400, $error);
        }

        $type = ($payload['type'] == 'system') ? Booking::SYSTEM_BOOKING_TYPE : Booking::MANUAL_BOOKING_TYPE;

        try {
            $this->calendarService->removeAvailability($calendar, $avail);
        }
        catch(\Exception $ex) {
            $error = array('code' => 'Api.PurchaseController.indexPostAction.7',
                'human' => 'Booking time no longer available',
                'debug' => $ex->getMessage());
            return $this->responseService->failure(400, $error);
        }

        try {
            $booking = $this->bookingService->createBooking($this->getAuthenticatedUser(), $providerAdImmutable,
                $service, $type, new \DateTime($payload['starting_at']), new \DateTime($payload['ending_at']),
                $payload['stripe_token']);
        }
        catch(\Exception $ex) {
            //Add back in the availability window we removed
            $this->calendarService->addAvailability($calendar, $avail);

            $error = array('code' => 'Api.PurchaseController.indexPostAction.8',
                'human' => 'Unable to complete purchase');
            return $this->responseService->failure(400, $error);
        }

        return $this->responseService->success(['booking' => ['id' => $booking->id]]);
    }

    public function indexDeleteAction($bookingId) {
        $booking = $this->bookingService->getBookingData($bookingId);

        if($booking === null) {
            $error = array('code' => 'Api.PurchaseController.indexDeleteAction.1',
                'human' => 'Unable to find the given booking detail record');
            return $this->responseService->failure(404, $error);
        }

        $this->bookingService->deleteBooking($booking);

        $providerAdImmutable = $this->providerAdImmutableService->getProviderAdImmutable($booking->providerAdImmutableId);
        $calendar = $this->calendarService->getCalendar($providerAdImmutable->calendar);
        $avail = new Availability();
        $avail->start = $booking->start;
        $avail->end = $booking->end;

        $this->calendarService->addAvailability($calendar, $avail);

        return $this->responseService->success();
    }
}
