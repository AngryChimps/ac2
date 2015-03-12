<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\es\Comment;
use Norm\riak\Company;
use Norm\riak\Member;
use NormTests\riak\CompanyReviews;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;

class CommentService {
    /** @var  NormRiakService */
    protected $riak;

    /** @var NormEsService */
    protected $es;

    public function __construct(NormRiakService $riak, NormEsService $es) {
        $this->riak = $riak;
        $this->es = $es;
    }

    public function recordComment(Member $member, Company $company, $rating, $commentText) {
        //Update rating in
        $company->ratingCount++;
        $company->ratingTotal += $rating;
        $company->ratingAvg = $company->ratingTotal / $company->ratingCount;
        $this->riak->update($company);

        //Create comment in Elasticsearch
        $comment = new Comment();
        $comment->companyId = $company->id;
        $comment->memberId = $member->id;
        $comment->rating = $rating;
        $comment->comment = $commentText;
        $this->es->publish($comment);
    }
}