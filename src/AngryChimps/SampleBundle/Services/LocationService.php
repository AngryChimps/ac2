<?php

namespace AngryChimps\SampleBundle\services;

use Symfony\Component\Yaml\Dumper;

class LocationService
{
    protected $sampleDir;

    public function __construct() {
        $this->sampleDir = __DIR__ . '/../samples';
    }

    public function post($region, $companyShortName, $locationShortName, $data) {
        $dumper = new Dumper();
        $yaml = $dumper->dump($data, 5);

        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations/' . $locationShortName . '.yml';

        if(!file_exists($filename)) {
            touch($filename);
        }

        file_put_contents($filename, $yaml);
    }

    public function get($region, $companyShortName, $locationShortName) {
        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations/' . $locationShortName . '.yml';
        $contents = file_get_contents($filename);
        return yaml_parse($contents);
    }

    public function getAllShortNames($region, $companyShortName) {
        $fh = opendir($this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations');

        $arr = [];
        while(false !== ($file = readdir($fh))) {
            if($file != '.' && $file != '..') {
                $arr[] = substr($file, 0, strlen($file) - 4);
            }
        }

        closedir($fh);

        return $arr;
    }

    public function delete($region, $companyShortName, $locationShortName) {
        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations/' . $locationShortName . '.yml';
        unlink($filename);
    }

    public function put($region, $companyShortName, $locationShortName, $data) {
        $this->post($region, $companyShortName, $locationShortName, $data);
    }
}