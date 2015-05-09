<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\riak\Member;

class MemberCreateTask extends AbstractTask {
    protected $member;

    public function __construct(Member $member) {
        $this->member = $member;
    }

    public function execute()
    {
        $mysqlMember = new \Norm\mysql\Member();

        $this->createMysqlObj($this->member, $mysqlMember);
    }
}