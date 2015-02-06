<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\TaskBundle\Services\TaskerService;
use AngryChimps\TaskBundle\Services\Tasks\MemberCreateTask;
use AngryChimps\TaskBundle\Services\Tasks\MemberUpdateTask;
use Norm\riak\Member;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class MemberService {
    /** @var  MailerService */
    protected $mailer;

    /** @var TimedTwigEngine  */
    protected $templating;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  NormRiakService */
    protected $riak;

    /** @var NormMysqlService */
    protected $mysql;

    /** @var  TaskerService */
    protected $tasker;

    protected $userModifiableFields = ['name', 'email'];

    public function __construct(MailerService $mailer,
                                TimedTwigEngine $templating,
                                ValidatorInterface $validator,
                                NormRiakService $riak,
                                NormMysqlService $mysql,
                                TaskerService $tasker)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->validator = $validator;
        $this->riak = $riak;
        $this->mysql = $mysql;
        $this->tasker = $tasker;
    }

    public function update(Member $member, array $changes, array &$errors) {

        foreach($changes as $fieldName => $value) {
            $member->$fieldName = $value;
        }

        $errors = $this->validator->validate($member);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->update($member);

        //Update Mysql and ElasticSearch
        $task = new MemberUpdateTask($member, $changes);
        $this->tasker->store($task);

        return true;
    }

    public function create(array $data, &$errors) {
        $member = new Member();
        $member->role = Member::USER_ROLE;
        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
        foreach($data as $prop => $value) {
            $member->$prop = $value;
        }

        $errors = $this->validator->validate($member);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($member);

        //Update Mysql and ElasticSearch
        $task = new MemberCreateTask($member);
        $this->tasker->store($task);

        return $member;
    }

    public function createEmpty() {
        $member = new Member();
        $member->role = Member::USER_ROLE;
        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
        $this->riak->create($member);

        //Create Mysql and ElasticSearch
        $task = new MemberCreateTask($member);
        $this->tasker->store($task);

        return $member;
    }

    public function getMemberByEmailEnabled($email) {
        $member = $this->riak->getObjectBySecondaryIndex('Norm\\riak\\Member', 'email_bin', $email);
        return $member;
    }

    public function getMember($id) {
        return $this->riak->getMember($id);
    }

    public function resetPassword($email, $resetCode) {
        $member = $this->getMemberByEmailEnabled($email);
        $now = new \DateTime();
        $member->password = 'reset: ' . $now->format("Y-m-d H:i:s");
        $member->passwordResetCode = $resetCode;
        $this->riak->update($member);
    }

    public function changePassword($email, $hashedPassword, $resetCode = null) {
        $member = $this->getMemberByEmailEnabled($email);

        if($member->passwordResetCode !== $resetCode) {
            return false;
        }

        $member->passwordResetCode = null;
        $member->password = $hashedPassword;
        $this->riak->update($member);
        return true;
    }

    public function markMemberDeleted(Member $member) {
        $member->status = Member::DELETED_STATUS;
        $this->riak->update($member);
    }
}