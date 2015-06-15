<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\riak\Member;

class MemberUpdateTask extends AbstractTask {
    protected $member;
    protected $changes;

    public function __construct(Member $member, $changes) {
        $this->member = $member;
        $this->changes = $changes;
    }

    public function execute()
    {
        $this->updateMysqlObj($this->member, $this->changes);
    }
}