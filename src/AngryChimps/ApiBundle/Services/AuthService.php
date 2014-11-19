<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Member;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Templating\TemplateReferenceInterface;

class AuthService {
    const MINIMUM_PASSWORD_LENGTH = 6;

    /** @var  \Armetiz\FacebookBundle\FacebookSessionPersistence */
    protected $facebookSdk;

    /** @var  MailerService */
    protected $mailer;

    /** @var TimedTwigEngine  */
    protected $templating;

    public function __construct(FacebookSessionPersistence $facebookSdk,
                                MailerService $mailer,
                                TimedTwigEngine $templating) {
        $this->facebookSdk = $facebookSdk;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * @param $fb_id
     * @param $access_token
     * @return Member|null
     * @throws \Exception
     */
    public function fbAuth($fb_id, $access_token) {
        $this->facebookSdk->setAccessToken($access_token);
        $userProfile = $this->facebookSdk->api('/' . $fb_id, 'GET');

        if($userProfile['id'] !== $fb_id) {
            throw new \Exception('Facebook id does not match access_token');
        }

        return $userProfile;
    }

    public function loginFormUser($email, $password) {
        $user = Member::getByEmail($email);

        if($user === null) {
            return false;
        }

        if(!$this->isPasswordCorrect($user, $password)) {
            return false;
        }

        return $user;
    }

    public function isPasswordCorrect(Member $user, $password) {
        return $user->password === $this->hashPassword($password);
    }

    public function hashPassword($password) {
        $options = [
            'cost' => 12,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function forgotPassword($email) {
        $user = Member::getByEmail($email);

        if($user === null) {
            return;
        }

        $resetCode = $this->generateToken(8);

        $now = new \DateTime();
        $user->password = 'reset: ' . $now->format("Y-m-d H:i:s");
        $user->passwordResetCode = $resetCode;
        $user->save();

        //Send password change email
        $message = BasicMessage::newInstance()
            ->setTo($email)
            ->setSubject('Forgot Password Reset Notification')
            ->setBody(
                $this->templating->render(
                    'ApiBundle:AuthService:resetPassword.email.txt.twig',
                    array('reset_code' => $resetCode)
                ), 'text/html'
            )
            ->addPart(
                $this->templating->render(
                    'ApiBundle:AuthService:resetPassword.email.html.twig',
                    array('reset_code' => $resetCode)
                ), 'text/plain'
            );
        $this->mailer->send($message);
    }

    public function registerFbUser($userProfile) {
        $member = new Member();
        $member->name = $userProfile['name'];
        $member->email = $userProfile['email'];
        $member->fname = $userProfile['first_name'];
        $member->lname = $userProfile['last_name'];
        $member->gender = $userProfile['gender'];
        $member->locale = $userProfile['locale'];
        $member->timezone = $userProfile['timezone'];

        $member->status = Member::ACTIVE_STATUS;
        $member->role = Member::USER_ROLE;

        $member->save();

        return $member;
    }

    public function resetPassword($email, $password) {
        $user = Member::getByEmail($email);
        $user->password = $this->hashPassword($password);
        $user->passwordResetCode = null;
        $user->save();
    }

    public function generateToken($length = 16) {
        $bytes = openssl_random_pseudo_bytes($length);
        $hex   = bin2hex($bytes);
        return $hex;
    }

    public function getUserByAuthToken($authToken) {
        $session = $this->get('session');

        if($session->get('ac.auth_service.auth_token') != $authToken) {
            return null;
        }

        return Member::getByPk($session->get('ac.auth_service.user_id'));
    }
} 