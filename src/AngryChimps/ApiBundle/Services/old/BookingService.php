<?php


namespace AngryChimps\ApiBundle\services;

use AngryChimps\NormBundle\services\NormService;
use Norm\Booking;
use Norm\Member;
use Norm\ProviderAdImmutable;
use Norm\Service;

class BookingService {
    protected $norm;
    protected $providerAdService;

    public function __construct(NormService $norm, ProviderAdService $providerAdService) {
        $this->norm = $norm;
        $this->providerAdService = $providerAdService;
    }

    public function getBooking($bookingId) {
        return $this->norm->getBooking($bookingId);
    }

    public function createBooking(Member $user, ProviderAdImmutable $providerAdImmutable,
                                   Service $service, $type, \DateTime $startingAt, \DateTime $endingAt,
                                   $stripeToken) {
        //Republish the ad
        $this->providerAdService->publish($providerAdImmutable->getProviderAd());

        //Create booking
        $booking = new Booking();
        $booking->setTitle($service->getName());
        $booking->setType($type);
        $booking->setStart($startingAt);
        $booking->setEnd($endingAt);
        $booking->setProviderAdImmutableId($providerAdImmutable->getId());
        $booking->setServiceId($service->id);
        $booking->setMemberId($user->getId());
        $booking->setPaymentType(Booking::STRIPE_PAYMENT__TYPE);
        $booking->setStatus(Booking::PENDING_STATUS);
        $booking->setStripeToken($stripeToken);
        $this->norm->create($booking);

        return $booking;
    }

    public function deleteBooking(Booking $booking) {
        $booking->setStatus(Booking::CANCELED_STATUS);
        $this->norm->update($booking);
    }

    public function verifyStripeToken($token) {
        return true;
    }
}