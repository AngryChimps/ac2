<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MediaBundle\Services\MediaService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use Norm\riak\Company;
use Norm\riak\ProviderAd;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyMediaService {

    /** @var MediaService */
    protected $mediaService;

    /** @var NormRiakService */
    protected $riak;

    public function __construct(MediaService $mediaService, NormRiakService $riak) {
        $this->mediaService = $mediaService;
        $this->riak = $riak;
    }
    public function postMedia(UploadedFile $file, Company $company, ProviderAd $providerAd = null) {
        $filename = 'ci/' . $this->mediaService->persist('company_images_fs', $file);

        $companyPhotos = $this->riak->getCompanyPhotos($company->id);
        $companyPhotos->photos[] = $filename;
        $this->riak->update($companyPhotos);

        if($providerAd !== null) {
            $providerAd->photos[] = $filename;
            $this->riak->update($providerAd);
        }

        return $filename;
    }
}