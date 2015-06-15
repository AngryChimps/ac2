<?php


namespace AngryChimps\ApiBundle\services;


use Norm\es\Comment;
use Norm\Company;
use Norm\Member;
use AngryChimps\NormBundle\services\NormService;

class CommentService {
    /** @var  NormService */
    protected $norm;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

    public function getComments(Company $company, $limit, $offset) {
        //If there are no ratings, return now
        if($company->getRatingCount() === 0) {
            return ['count' => 0, 'results' => [] ];
        }

        $filter = new \Elastica\Filter\Term( [ 'company_id' => $company->getId() ] );
        $query = new \Elastica\Query();
        $query->setFrom($offset);
        $query->setSize($limit);
        $query->setPostFilter($filter);

        $results = $this->norm->search('Norm\\Comment', $query, $limit, $offset);

        //Extract the data to return
        $arr = [];
        $arr['count'] = $results->getTotalHits();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $data = $result->getSource();

            //Add member information
            $member = $this->norm->getMember($data['member_id']);
            $data['member'] = [];
            $data['member']['photo'] = $member->getPhoto() ?: '';
            $data['member']['display_name'] = $member->getFname() . ' ' . substr($member->getLname(), 0, 1) . '.';

            //Unset data we don't want
            unset($data['company_id']);
            unset($data['member_id']);

           $arr['results'][] = $data;
        }

        return $arr;
    }

    public function recordComment(Member $member, Company $company, $rating, $commentText) {
        //Update rating in
        $company->incrementRatingCount(1);
        $company->incrementRatingTotal($rating);
        $company->setRatingAvg($company->getRatingTotal() / $company->getRatingCount());
        $this->norm->update($company);

        //Create comment in Elasticsearch
        $comment = new Comment();
        $comment->setCompanyId($company->getId());
        $comment->setMemberId($member->getId());
        $comment->setRating($rating);
        $comment->setComment($commentText);
        $this->norm->publish($comment);
    }
}