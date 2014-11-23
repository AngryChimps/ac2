<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\riak\Member;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;

class MemberService {
    /** @var  MailerService */
    protected $mailer;

    /** @var TimedTwigEngine  */
    protected $templating;

    /** @var \AngryChimps\ApiBundle\Services\AuthService */
    protected $auth;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(MailerService $mailer,
                                TimedTwigEngine $templating,
                                AuthService $auth,
                                ValidatorInterface $validator) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->auth = $auth;
        $this->validator = $validator;
    }

    public function createMember($name, $email, $password, \DateTime $dob, array &$errors) {
        $member = new Member();
        $member->name = $name;
        $member->email = $email;
        $member->password = $password;
        $member->dob = $dob;
        $member->status = Member::ACTIVE_STATUS;
        $member->role = Member::USER_ROLE;

        $errors = $this->validator->validate($member);

        //Hash password
        $member->password = $this->auth->hashPassword($password);

        if(count($errors) > 0) {
            return false;
        }

        $member->save();

        return $member;
    }
}