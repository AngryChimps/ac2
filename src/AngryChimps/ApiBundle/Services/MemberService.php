<?php


namespace AngryChimps\ApiBundle\Services;


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

    /** @var  NormMysqlService */
    protected $mysql;

    protected $userModifiableFields = ['name', 'email'];

    public function __construct(MailerService $mailer,
                                TimedTwigEngine $templating,
                                ValidatorInterface $validator,
                                NormRiakService $riak,
                                NormMysqlService $mysql)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->validator = $validator;
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function update(Member $member, array $changes, array &$errors, $isAdmin = false) {
        $objectsToSave = array();
        $mysqlMember = null;

        foreach($changes as $fieldName => $value) {
            switch ($fieldName) {
                case 'email':
                case 'name':
                    $member->$fieldName = $value;
                    if ($mysqlMember === null) {
                        $mysqlMember = $this->mysql->getMember($member->mysqlId);
                        $objectsToSave[] = $mysqlMember;
                    }
                    $mysqlMember->$fieldName = $value;
                    break;
                case 'dob':
                case 'status':
                case 'password':
                case 'role':
                case 'last_activity_date':
                case 'blocked_company_ids':
                case 'ad_flag_keys':
                case 'ad_message_keys':
                    if (!$isAdmin) {
                        throw new \Exception('The ' . $fieldName . ' field is not user updatable');
                    }
                    $member->$fieldName = $value;
                    if ($mysqlMember === null) {
                        $mysqlMember = $this->mysql->getMember($member->mysqlId);
                        $objectsToSave[] = $mysqlMember;
                    }
                    $mysqlMember->$fieldName = $value;
                    break;
                case 'photo':
                case 'mobile':
                    $member->$fieldName = $value;
                    break;
                case 'id':
                case 'mysql_id':
                    throw new \Exception('The ' . $fieldName . ' field is immutable');
                case 'created_at':
                case 'updated_at':
                    //Do nothing, these are updated automatically
                    break;
                default:
                    throw new \Exception('Unknown field name: ' . $fieldName . ' in MemberService::update');
            }
        }

//        $errors = $this->validator->validate($member);
//
//        if(count($errors) > 0) {
//            return false;
//        }

        foreach($objectsToSave as $obj) {
            $this->mysql->update($obj);
        }

        $this->riak->update($member);

        //Tell the controller that everything was valid
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

        $mysqlMember = new \Norm\mysql\Member();
        $mysqlMember->id = $member->id;
        $mysqlMember->email = $member->email;
        $mysqlMember->name = $member->name;
        $mysqlMember->mobile = $member->mobile;
        $mysqlMember->dob = $member->dob;
        $mysqlMember->status = $member->status;
        $mysqlMember->role = $member->role;
        $this->mysql->create($mysqlMember);

        $member->mysqlId = $mysqlMember->mysqlId;
        $this->riak->update($member);

        return $member;
    }

    public function createEmpty() {
        $member = new Member();
        $member->role = Member::USER_ROLE;
        $member->status = Member::PARTIAL_REGISTRATION_STATUS;
        $this->riak->create($member);

        $mysqlMember = new \Norm\mysql\Member();
        $mysqlMember->id = $member->id;
        $mysqlMember->status = $member->status;
        $mysqlMember->role = $member->role;
        $this->mysql->create($mysqlMember);

        $member->mysqlId = $mysqlMember->mysqlId;
        $this->riak->update($member);

        return $member;
    }

    public function getMemberByEmailEnabled($email) {
        $mysqlMember = $this->mysql->getMemberByEmail($email);
        if($mysqlMember->status == Member::ACTIVE_STATUS) {
            return $this->riak->getMember($mysqlMember->id);
        }
        return null;
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