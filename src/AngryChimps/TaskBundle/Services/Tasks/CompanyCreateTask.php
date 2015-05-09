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

        foreach ($this->member as $fieldName => $value) {
            if(property_exists($mysqlMember, $fieldName)) {
                $mysqlMember->$fieldName = $value;
            }
        }

        $this->mysql->create($mysqlMember);

        $this->member->mysqlId = $mysqlMember->mysqlId;
        $this->riak->update($this->member);
    }
}