<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\ApiBundle\Exceptions\InvalidSessionException;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Member;
use Norm\riak\Session;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\TemplateReferenceInterface;

class SessionService {
    protected $sessionHeaderName;

    /** @var Request  */
    protected $request;

    public function __construct(RequestStack $request, $sessionHeaderName) {
        $this->request = $request->getCurrentRequest();
        $this->sessionHeaderName = $sessionHeaderName;
    }

    protected function generateToken($length = 16) {
        $bytes = openssl_random_pseudo_bytes($length);
        $hex   = bin2hex($bytes);
        return $hex;
    }

    public function getNewSessionToken() {
        return $this->generateToken();
    }

    public function getBrowserHash() {
        return md5($this->request->getClientIp() . $this->request->headers->get('User-Agent'));
    }

    public function checkToken() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = Session::getByPk($sessionToken);
        if($session === null) {
            throw new InvalidSessionException('code: Api.SessionService.1');
        }

        if($session->userId !== null || $this->request->query->get('userId') !== null) {
            if($session->userId !== $this->request->query->get('userId')) {
                throw new InvalidSessionException('code: Api.SessionService.2');
            }
        }

        if($session->browserHash !== $this->getBrowserHash()) {
            throw new InvalidSessionException('code: Api.SessionService.3');
        }
    }

    public function getSessionUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = Session::getByPk($sessionToken);

        if($session->userId === null) {
            return null;
        }

        $user = Member::getByPk($session->userId);
        return $user;
    }

    public function logoutUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = Session::getByPk($sessionToken);
        $session->userId = null;
        $session->sessionBag = array();
    }
} 