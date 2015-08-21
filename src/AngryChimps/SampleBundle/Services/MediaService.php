<?php

namespace AngryChimps\SampleBundle\services;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Dumper;

class MediaService
{
    protected $sampleDir;

    public function __construct() {
        $this->sampleDir = __DIR__ . '/../samples';
    }

    public function get($region, $companyShortName, $name) {
        $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data/' . $name);
        $data = yaml_parse($contents);
        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/images/' . $data['filename'];

        return file_get_contents($filename);
    }

    public function post($region, $companyShortName, $name, File $file, $topRight, $topLeft, $bottomRight, $bottomLeft) {
        //Write the image file
        $ext = $file->getExtension();
        $file->move($this->sampleDir . '/' . $region . '/' . $companyShortName . '/images', $name . '.' . $ext);

        //Write the image data
        $arr = [];
        $arr['name'] = $name;
        $arr['filename'] = $name . '.' . $ext;
        $arr['topRight'] = $topRight;
        $arr['topLeft'] = $topLeft;
        $arr['bottomRight'] = $bottomRight;
        $arr['bottomLeft'] = $bottomLeft;

        $dumper = new Dumper();
        $yaml = $dumper->dump($arr, 5);

        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data/' . $name . '.yml';

        if(!file_exists($filename)) {
            touch($filename);
        }

        file_put_contents($filename, $yaml);
    }

    public function delete($region, $companyShortName, $name) {
        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data/' . $name . '.yml';

        $contents = file_get_contents($filename);
        $arr = yaml_parse($contents);

        unlink($filename);

        $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/images/' . $arr['filename'];
        unlink($filename);
    }
}