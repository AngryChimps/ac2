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

    public function update(Member $member, $name, $email, array &$errors) {
        $member->name = $name;
        $member->email = $email;

        $errors = $this->validator->validate($member);

        if(count($errors) > 0) {
            return false;
        }

        $member->save();
    }

    public function createEmpty() {
        $member = new Member();
        $member->role = Member::USER_ROLE;
        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
        $member->save();

        return $member;
    }
}