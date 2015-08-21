<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 8/18/2015
 * Time: 1:07 PM
 */

namespace AngryChimps\SampleBundle\services;


use Symfony\Component\Yaml\Dumper;

class CompanyService
{
    protected $sampleDir;

    public function __construct() {
        $this->sampleDir = __DIR__ . '/../samples';
    }

    public function createFolders($region, $companyShortName) {
        $folders = [
            $this->sampleDir . '/' . $region,
            $this->sampleDir . '/' . $region . '/' . $companyShortName,
            $this->sampleDir . '/' . $region . '/' . $companyShortName . '/images',
            $this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data',
            $this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations',
            $this->sampleDir . '/' . $region . '/' . $companyShortName . '/staff',
        ];

        foreach($folders as $folder) {
            if(!file_exists($folder)) {
                mkdir($folder);
            }
        }
    }

    public function post($region, $companyShortName, $data) {
        $dumper = new Dumper();
        $yaml = $dumper->dump($data, 5);

        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/company.yml';

        if(!file_exists($filename)) {
            touch($filename);
        }

        file_put_contents($filename, $yaml);
    }

    public function get($region, $companyShortName) {
        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/company.yml';
        $contents = file_get_contents($filename);
        return yaml_parse($contents);
    }

    public function getAllShortNames($region) {
        $fh = opendir($this->sampleDir . '/' . $region);

        $arr = [];
        while(false !== ($file = readdir($fh))) {
            if($file != '.' && $file != '..') {
                $arr[] = $file;
            }
        }

        closedir($fh);

        return $arr;
    }

    public function delete($region, $companyShortName) {
        $dir = $this->sampleDir . '/' . $region . '/' . $companyShortName;
        unlink($dir);
    }

    public function put($region, $companyShortName, $data) {
        $this->post($region, $companyShortName, $data);
    }
}