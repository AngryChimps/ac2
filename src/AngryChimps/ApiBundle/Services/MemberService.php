<?php


namespace AngryChimps\ApiBundle\Services;


use AC\NormBundle\services\InfoService;
use AngryChimps\TaskBundle\Services\TaskerService;
use AngryChimps\TaskBundle\Services\Tasks\MemberCreateTask;
use AngryChimps\TaskBundle\Services\Tasks\MemberUpdateTask;
use Norm\Member;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use AngryChimps\NormBundle\services\NormService;

class MemberService extends AbstractRestService {
    /** @var  MailerService */
    protected $mailer;

    /** @var TimedTwigEngine  */
    protected $templating;

    /** @var  TaskerService */
    protected $tasker;

    /** @var SessionService */
    protected $sessionService;

    protected $userModifiableFields = ['name', 'email'];

    public function __construct(MailerService $mailer,
                                TimedTwigEngine $templating,
                                ValidatorInterface $validator,
                                NormService $norm,
                                TaskerService $tasker,
                                InfoService $infoService,
                                SessionService $sessionService)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->tasker = $tasker;
        $this->sessionService = $sessionService;

        parent::__construct($norm, $infoService, $validator);
    }

    /**
     * @param Member $member
     * @param Member $authenticatedMember
     * @return bool
     */
    public function isOwner($member, Member $authenticatedMember) {
        return $authenticatedMember && ($authenticatedMember->getId() === $member->getId());
    }

    public function post($endpoint, $data, $additionalData = [])
    {
        $member = parent::post($endpoint, $data, $additionalData);

        //Since we created the new member, let's log them in as well
        if($member !== false) {
            $this->sessionService->setSessionUser($member);
        }

        return $member;
    }

    public function get($endpoint, $id)
    {
        if(strpos($id, '@') !== false) {
            return $this->norm->getMemberByEmail($id);
        }
        else {
            return parent::get($endpoint, $id);
        }
    }



//    public function update(Member $member, array $changes, array &$errors) {
//
//        foreach($changes as $fieldName => $value) {
//            $member->$fieldName = $value;
//        }
//
//        $errors = $this->validator->validate($member);
//        if(count($errors) > 0) {
//            return false;
//        }
//
//        $this->norm->update($member);
//
//        //Update Mysql and ElasticSearch
//        $task = new MemberUpdateTask($member, $changes);
//        $this->tasker->store($task);
//
//        return true;
//    }
//
//    public function create(array $data, &$errors) {
//        $member = new Member();
//        $member->role = Member::USER_ROLE;
//        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
//        foreach($data as $prop => $value) {
//            $member->$prop = $value;
//        }
//
//        $errors = $this->validator->validate($member);
//        if(count($errors) > 0) {
//            return false;
//        }
//
//        $options = [
//            'cost' => 12,
//        ];
//        $member->password = $this->password_hash($data['password'], PASSWORD_BCRYPT, $options);
//        $this->norm->create($member);
//
//        //Update Mysql and ElasticSearch
//        $task = new MemberCreateTask($member);
//        $this->tasker->store($task);
//
//        return $member;
//    }
//
//    public function createEmpty() {
//        $member = new Member();
//        $member->role = Member::USER_ROLE;
//        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
//        $this->norm->create($member);
//
//        //Create Mysql and ElasticSearch
//        $task = new MemberCreateTask($member);
//        $this->tasker->store($task);
//
//        return $member;
//    }
//
//    public function getMemberByEmailEnabled($email) {
////        $member = $this->norm->getObjectBySecondaryIndex('Norm\\norm\\Member', 'email_bin', $email);
//        $mysqlMember = $this->norm->getMemberByEmail($email);
//        $member = $this->norm->getMember($mysqlMember->id);
//        return $member;
//    }
//
//    public function getMember($id) {
//        return $this->norm->getMember($id);
//    }
//
//    public function resetPassword($email, $resetCode) {
//        $member = $this->getMemberByEmailEnabled($email);
//        $now = new \DateTime();
//        $member->password = 'reset: ' . $now->format("Y-m-d H:i:s");
//        $member->passwordResetCode = $resetCode;
//        $this->norm->update($member);
//    }
//
//    public function changePassword($email, $hashedPassword, $resetCode = null) {
//        $member = $this->getMemberByEmailEnabled($email);
//
//        if($member->passwordResetCode !== $resetCode) {
//            return false;
//        }
//
//        $member->passwordResetCode = null;
//        $member->password = $hashedPassword;
//        $this->norm->update($member);
//        return true;
//    }
//
//    public function markMemberDeleted(Member $member) {
//        $member->status = Member::DELETED_STATUS;
//        $this->norm->update($member);
//    }
}