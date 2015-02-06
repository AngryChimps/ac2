<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\riak\Member;

class MemberUpdateTask extends AbstractTask {
    protected $member;

    public function __construct(Member $member, $changes) {
        $this->member = $member;
        $this->changes = $changes;
    }

    public function execute()
    {
        $mysqlMember = $this->mysql->getMember($this->member->id);

        foreach ($this->changes as $fieldName => $value) {
            if(property_exists($mysqlMember, $fieldName)) {
                $mysqlMember->$fieldName = $value;
            }
        }

        $this->mysql->update($mysqlMember);
    }
}