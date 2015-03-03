<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Member;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService {
    const MINIMUM_PASSWORD_LENGTH = 6;

    /** @var  \Armetiz\FacebookBundle\FacebookSessionPersistence */
    protected $facebookSdk;

    /** @var  MailerService */
    protected $mailer;

    /** @var TimedTwigEngine  */
    protected $templating;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  MemberService */
    protected $memberService;

    public function __construct(MailerService $mailer,
                                TimedTwigEngine $templating,
                                ValidatorInterface $validator,
                                MemberService $memberService)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->validator = $validator;
        $this->memberService = $memberService;
    }

    public function register($name, $email, $password, \DateTime $dob, array &$errors) {
        $data = array(
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'dob' => $dob,
        );

        $member = $this->memberService->create($data, $errors);

        if(count($errors) > 0) {
            return false;
        }

        return $member;
    }

//    /**
//     * @param $fb_id
//     * @param $access_token
//     * @return Member|null
//     * @throws \Exception
//     */
//    public function fbAuth($fb_id, $access_token) {
//        $this->facebookSdk->setAccessToken($access_token);
//        $userProfile = $this->facebookSdk->api('/' . $fb_id, 'GET');
//
//        if($userProfile['id'] !== $fb_id) {
//            throw new \Exception('Facebook id does not match access_token');
//        }
//
//        return $userProfile;
//    }

    public function loginFormUser($email, $password) {
        $user = $this->memberService->getMemberByEmailEnabled($email);

        if($user === null) {
            return null;
        }

        if(!$this->isPasswordCorrect($user, $password)) {
            return false;
        }

        return $user;
    }
    public function isPasswordCorrect(Member $user, $password) {
        return password_verify($password, $user->password);
    }

    public function hashPassword($password) {
        $options = [
            'cost' => 12,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function forgotPassword($email) {
        $resetCode = $this->generateToken(8);

        $this->memberService->resetPassword($email, $resetCode);

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

//    public function registerFbUser($userProfile) {
//        $member = new Member();
//        $member->name = $userProfile['name'];
//        $member->email = $userProfile['email'];
//        $member->fname = $userProfile['first_name'];
//        $member->lname = $userProfile['last_name'];
//        $member->gender = $userProfile['gender'];
//        $member->locale = $userProfile['locale'];
//        $member->timezone = $userProfile['timezone'];
//
//        $member->status = Member::ACTIVE_STATUS;
//        $member->role = Member::USER_ROLE;
//
//        $this->norm->save($member);
//
//        return $member;
//    }

    public function resetPassword($email, $password) {
        return $this->memberService->changePassword($email, $this->hashPassword($password));
    }

    public function generateToken($length = 16) {
        $bytes = openssl_random_pseudo_bytes($length);
        $hex   = bin2hex($bytes);
        return $hex;
    }
}