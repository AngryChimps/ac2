<?php


namespace AngryChimps\ApiBundle\Services;


//use AngryChimps\ApiBundle\Services\Armetiz\FacebookBundle\FacebookSessionPersistence;
use \Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Member;
use Symfony\Component\Config\Definition\Exception\Exception;

class AuthService {
    /** @var  \Armetiz\FacebookBundle\FacebookSessionPersistence */
    protected $facebookSdk;

    public function __construct(FacebookSessionPersistence $facebookSdk) {
        $this->facebookSdk = $facebookSdk;
    }

    /**
     * @param $fb_id
     * @param $access_token
     * @return Member|null
     * @throws \Exception
     */
    public function fbAuth($fb_id, $access_token) {
        $this->facebookSdk->setAccessToken($access_token);
        $userProfile = $this->facebookSdk->api('/' . $fb_id, 'GET');

        if($userProfile['id'] !== $fb_id) {
            throw new \Exception('Facebook id does not match access_token');
        }

        return $userProfile;
    }

    public function registerFbUser($userProfile) {
        $member = new Member();
        $member->name = $userProfile['name'];
        $member->email = $userProfile['email'];
        $member->fname = $userProfile['first_name'];
        $member->lname = $userProfile['last_name'];
        $member->gender = $userProfile['gender'];
        $member->locale = $userProfile['locale'];
        $member->timezone = $userProfile['timezone'];

        $member->status = Member::ACTIVE_STATUS;
        $member->role = Member::USER_ROLE;

        $member->save();

        return $member;
    }
} 