<?php


namespace AngryChimps\ApiBundle\Services;


use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\TemplateReferenceInterface;
use AngryChimps\NormBundle\services\NormService;
use Norm\Session;
use Norm\Member;

class SessionService extends AbstractRestService {
    protected $sessionHeaderName;

    /** @var Request  */
    protected $request;

    /** @var  NormService */
    protected $norm;

    /** @var DeviceService  */
    protected $deviceService;

    public function __construct(RequestStack $request, $sessionHeaderName, NormService $norm, DeviceService $deviceService) {
        $this->request = $request->getCurrentRequest();
        $this->sessionHeaderName = $sessionHeaderName;
        $this->norm = $norm;
        $this->deviceService = $deviceService;
    }

    public function isOwner($obj, Member $authenticatedMember)
    {
        return true;
    }

    public function post($endpoint, $data, $additionalData = [])
    {
        $session = new Session();
        $session->setId($this->generateToken());
        $session->setBrowserHash($this->getBrowserHash());
        $this->norm->create($session);

        $this->deviceService->register($session, $data['device_type'], $data['push_token'], $data['description']);
        return $session;
    }

    protected function generateToken($length = 16) {
        $bytes = openssl_random_pseudo_bytes($length);
        $hex   = bin2hex($bytes);
        return base64_encode(password_hash(microtime(true) . $hex, PASSWORD_DEFAULT));
    }

    public function getNewSessionToken() {
        return $this->generateToken();
    }

    public function getBrowserHash() {
        return md5($this->request->getClientIp() . $this->request->headers->get('User-Agent'));
    }

    public function checkToken() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->norm->getSession($sessionToken);
        if($session === null) {
            return 'Unable to find a session with that id';
        }

        // Session has user; $_GET has no userId parameter
        if($session->getUserId() !== null && $this->request->query->get('userId') == null) {
            return 'Session has authenticated user, but no $_GET["userId"] parameter';
        }

        // Session has user; $_GET has an empty userId parameter
        if($session->getUserId() !== null && $this->request->query->get('userId') === '') {
            return 'Session has authenticated user, but blank $_GET["userId"] parameter';
        }

        // Session has no user; $_GET has userId parameter
        if($session->getUserId() === null && !empty($this->request->query->get('userId'))) {
            return 'Session has no authenticated user, but $_GET["userId"] parameter does';
        }

        if($session->getUserId() !== null && $this->request->query->get('userId') !== null
            && $session->getUserId() != $this->request->query->get('userId'))  {
            return 'Session and $_GET userIds do not match';
        }

        if($session->getBrowserHash() !== $this->getBrowserHash()) {
            return 'Session browser hash does not match';
        }

        //Returning false to indicate a lack of errors
        return false;
    }

    public function getNewSession() {
        $token = $this->getNewSessionToken();

        $session = new Session();
        $session->setId($token);
        $session->setBrowserHash($this->getBrowserHash());

        $this->norm->create($session);
        return $session;
    }

    public function getSessionUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        if($sessionToken === null) {
            return null;
        }

        $session = $this->norm->getSession($sessionToken);

        if($session->getUserId() === null) {
            return null;
        }

        $user = $this->norm->getMember($session->getUserId());

        return $user;
    }

    public function setSessionUser(Member $user) {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->norm->getSession($sessionToken);
        $session->setUserId($user->getId());
        $this->norm->update($session);
    }

    public function logoutUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->norm->getSession($sessionToken);
        $session->setUserId(null);
        $session->setSessionBag([]);
        $this->norm->update($session);
    }
} 