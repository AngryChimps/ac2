<?php


namespace AngryChimps\ApiBundle\Services;

use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use Norm\riak\Booking;
use Norm\riak\BookingDetail;
use Norm\riak\Member;
use Norm\riak\ProviderAdImmutable;
use Norm\riak\Service;

class BookingService {
    protected $riak;
    protected $providerAdService;

    public function __construct(NormRiakService $riak, ProviderAdService $providerAdService) {
        $this->riak = $riak;
        $this->providerAdService = $providerAdService;
    }

    public function getBooking($bookingId) {
        return $this->riak->getBooking($bookingId);
    }

    public function createBooking(Member $user, ProviderAdImmutable $providerAdImmutable,
                                   Service $service, $type, \DateTime $startingAt, \DateTime $endingAt,
                                   $stripeToken) {
        //Republish the ad
        $this->providerAdService->publish($providerAdImmutable->providerAd);

        //Create booking
        $booking = new Booking();
        $booking->title = $service->name;
        $booking->type = $type;
        $booking->start = $startingAt;
        $booking->end = $endingAt;
        $booking->providerAdImmutableId = $providerAdImmutable->id;
        $booking->serviceId = $service->id;
        $booking->memberId = $user->id;
        $booking->paymentType = Booking::STRIPE_PAYMENT__TYPE;
        $booking->status = Booking::PENDING_STATUS;
        $booking->stripeToken = $stripeToken;
        $this->riak->create($booking);

        return $booking;
    }

    public function deleteBooking(Booking $booking) {
        $booking->status = Booking::CANCELED_STATUS;
        $this->riak->update($booking);
    }

    public function verifyStripeToken($token) {
        return true;
    }
}