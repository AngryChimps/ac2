<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MediaBundle\Services\MediaService;
use AngryChimps\NormBundle\services\NormService;
use Norm\Company;
use Norm\ProviderAd;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyMediaService {

    /** @var MediaService */
    protected $mediaService;

    /** @var NormService */
    protected $norm;

    public function __construct(MediaService $mediaService, NormService $norm) {
        $this->mediaService = $mediaService;
        $this->norm = $norm;
    }
    public function postMedia(UploadedFile $file, Company $company, ProviderAd $providerAd = null) {
        $filename = 'ci/' . $this->mediaService->persist('company_images_fs', $file);

        $companyPhotos = $this->norm->getCompanyPhotos($company->getId());
        $companyPhotos->addToPhotos($filename);
        $this->norm->update($companyPhotos);

        if($providerAd !== null) {
            $providerAd->addToPhotos($filename);
            $this->norm->update($providerAd);
        }

        return $filename;
    }
}