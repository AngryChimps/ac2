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
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class SessionService {
    protected $sessionHeaderName;

    /** @var Request  */
    protected $request;

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(RequestStack $request, $sessionHeaderName, NormRiakService $riak, NormMysqlService $mysql) {
        $this->request = $request->getCurrentRequest();
        $this->sessionHeaderName = $sessionHeaderName;
        $this->riak = $riak;
        $this->mysql = $mysql;
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

        $session = $this->riak->getSession($sessionToken);
        if($session === null) {
            $debug = array(
                'code' => 'Api.SessionService.1a',
                'human' => 'Unable to find a session with that id',
            );
            throw new InvalidSessionException($debug);
        }

        // Session has user; $_GET has no userId parameter
        if($session->userId !== null && $this->request->query->get('userId') == null) {
            $debug = array(
                'code' => 'Api.SessionService.1b',
                'human' => 'Session has authenticated user, but no $_GET["userId"] parameter',
            );
            throw new InvalidSessionException($debug);
        }

        // Session has user; $_GET has an empty userId parameter
        if($session->userId !== null && $this->request->query->get('userId') === '') {
            $debug = array(
                'code' => 'Api.SessionService.1c',
                'human' => 'Session has authenticated user, but blank $_GET["userId"] parameter',
            );
            throw new InvalidSessionException($debug);
        }

        // Session has no user; $_GET has userId parameter
        if($session->userId === null && !empty($this->request->query->get('userId'))) {
            $debug = array(
                'code' => 'Api.SessionService.1d',
                'human' => 'Session has no authenticated user, but $_GET["userId"] parameter does',
            );
            throw new InvalidSessionException($debug);
        }

        if($session->userId !== null && $this->request->query->get('userId') !== null
            && $session->userId != $this->request->query->get('userId'))  {
            $debug = array(
                'code' => 'Api.SessionService.1e',
                'human' => 'Session and $_GET userIds do not match',
            );
            throw new InvalidSessionException($debug);
        }

        if($session->browserHash !== $this->getBrowserHash()) {
            $debug = array(
                'code' => 'Api.SessionService.1f',
                'human' => 'Session browser hash does not match',
            );
            throw new InvalidSessionException($debug);
        }
    }

    public function getNewSession() {
        $token = $this->getNewSessionToken();

        $session = new Session();
        $session->id= $token;
        $session->browserHash = $this->getBrowserHash();

        $this->riak->create($session);
        return $session;
    }

    public function getSessionUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->riak->getSession($sessionToken);

        if($session->userId === null) {
            return null;
        }

        $user = $this->riak->getMember($session->userId);
        return $user;
    }

    public function setSessionUser(Member $user) {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->riak->getSession($sessionToken);
        $session->userId = $user->id;
        $this->riak->update($session);
    }

    public function logoutUser() {
        $sessionToken = $this->request->headers->get($this->sessionHeaderName);

        $session = $this->riak->getSession($sessionToken);
        $session->userId = null;
        $session->sessionBag = array();
        $this->riak->update($session);
    }
} 