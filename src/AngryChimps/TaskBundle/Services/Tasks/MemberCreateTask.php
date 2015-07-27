<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\Member;

class MemberCreateTask extends AbstractTask {
    protected $member;

    public function __construct(Member $member) {
        $this->member = $member;
    }

    public function execute()
    {
        $this->createMysqlObj($this->member);
    }
}