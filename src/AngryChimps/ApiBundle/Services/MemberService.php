<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\riak\Member;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MemberService {
    /** @var  \Swift_Mailer */
    protected $mailer;

    /** @var TemplateReferenceInterface  */
    protected $templating;

    /** @var \AngryChimps\ApiBundle\Services\AuthService */
    protected $auth;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(\Swift_Mailer $mailer,
                                TemplateReferenceInterface $templating,
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
        $member->password = $this->auth->hashPassword($password);
        $member->dob = $dob;

        $errors = $this->validator->validate($member);

        if(count($errors) > 0) {
            return false;
        }

        $member->save();
        return $member;
    }
}