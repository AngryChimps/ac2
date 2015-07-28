<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\NormBundle\services\NormService;
use Norm\Device;
use Norm\Session;

class DeviceService {

    /** @var  NormService */
    protected $norm;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

    public function register(Session $session, $type, $pushToken, $description) {
        $device = new Device();
        $device->setDeviceType((int) $type);
        $device->setPushToken($pushToken);
        $device->setDescription($description);
        $this->norm->create($device);

        $session->setDeviceId($device->getId());
        $this->norm->update($session);

        return $device;
    }

}