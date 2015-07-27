<?php


namespace AngryChimps\ApiSampleBundle\services;


class DocsService {
    const api_dir = '/../apis';
    const api_prefix = '/api/v1/';

    public function getTwigData() {
        $data = [];
        $data['apis'] = [];

        $fh = opendir(__DIR__ . self::api_dir);

        while(false !== ($entry = readdir($fh))) {
            if($entry === 'description.md') {
                $contents = file_get_contents(__DIR__ . self::api_dir . '/description.md');
                $data['main_description'] = \Parsedown::instance()->text($contents);
            }
            elseif($entry !== '..' && $entry != '.') {
                $data['apis'][] = $this->getTwigApiData($entry);
            }
        }

        sort($data['apis']);

        return $data;
    }

    protected function getTwigApiData($api) {
        $data = [];
        $data['name'] = $api;
        $data['methods'] = [];
        $methods = ['get', 'post', 'put', 'delete'];

        //Get main api description
        if(file_exists(__DIR__ . self::api_dir . '/' . $api . '/description.md')) {
            $contents = file_get_contents(__DIR__ . self::api_dir . '/' . $api . '/description.md');
            $data['description'] = \Parsedown::instance()->text($contents);
        }
        else {
            $data['description'] = '';
        }

        // Get the regular REST endpoint data
        foreach($methods as $method) {
            if(!file_exists(__DIR__ . self::api_dir . '/' . $api . '/' . $method)) {
                continue;
            }

            $data['methods'][] = $this->getTwigApiMethodData($api, $method);
        }

        // Get custom endpoint data
        $data['endpoints'] = [];
        $fh = opendir(__DIR__ . self::api_dir . "/" . $api);
        while(false !== ($entry = readdir($fh))) {
            if($entry === '.' || $entry === '..' || $entry === 'description.md') {
                continue;
            }
            elseif(in_array($entry, $methods)) {
                continue;
            }
            else {
                $data['endpoints'][] = $this->getTwigApiMethodData($api, $entry);
            }
        }


        return $data;
    }

    protected function getTwigApiMethodData($api, $method) {
        $methodData = [];
        $methodData['name'] = $method;
        $methodData['scenarios'] = [];

        //Figure out if there are scenarios involved
        $fh = opendir(__DIR__ . self::api_dir . '/' . $api . '/' . $method);

        while(false !== ($entry = readdir($fh))) {
            if($entry === '.' || $entry === '..') {
                continue;
            }
            if(strpos($entry, 'scenario_') === 0) {
                $parts = explode('_', $entry);
                $scenarioId = (int) $parts[1];

                $methodData['scenarios'][] =
                    $this->getInputOutput(__DIR__ . self::api_dir . '/' . $api . '/' . $method . '/scenario_' . $scenarioId,
                        $api, $scenarioId);
            }
        }

        if($methodData['scenarios'] === []) {
            $methodData['scenarios'][] = $this->getInputOutput(__DIR__ . self::api_dir . '/' . $api . '/' . $method,
                $api);
        }


         if(file_exists(__DIR__ . self::api_dir . '/' . $api . '/' . $method . '/description.md')) {
            $contents = file_get_contents(__DIR__ . self::api_dir . '/' . $api . '/' . $method . '/description.md');
            $methodData['description'] = \Parsedown::instance()->text($contents);
        }
        else {
            $methodData['description'] = '';
        }

        if(file_exists(__DIR__ . self::api_dir . '/' . $api . '/' . $method . '/errors.json')) {
            $contents = file_get_contents(__DIR__ . self::api_dir . '/' . $api . '/' . $method . '/errors.json');
            $methodData['errors'] = json_decode($contents, true);
        }
        else {
            $methodData['errors'] = [];
        }

        return $methodData;

    }

    public function getInputOutput($directoryName, $api, $scenarioId = null) {
        $arr = [];

        //If there are scenarios, prefix each with the scenario number
        if($scenarioId !== null) {
            $arr['input'] = "Scenario $scenarioId:\n\n";
            $arr['output'] = "Scenario $scenarioId:\n\n";
        }
        else {
            $arr['input'] = '';
            $arr['output'] = '';
        }

        if(file_exists($directoryName . '/input.json')) {
            $arr['input'] .= file_get_contents($directoryName . '/input.json');
        }
        else {
            $arr['input'] .= self::api_prefix . $api . '/{' . $api . 'Id}';
        }

        if(file_exists($directoryName . '/output.json')) {
            $arr['output'] .= file_get_contents($directoryName . '/output.json');
        }
        else {
            $arr['output'] = "{\n\t\"payload\": []\n}";
        }

        return $arr;
    }

    public function getSimulatorResponse($api, $method, $slug)
    {
        $path = __DIR__ . self::api_dir . '/' . $api;
        if ($api === 'auth') {
            $path .= '/' . $slug . ' - ' . $method . '/output.json';
        } else {
            $path .= '/' . $method . '/output.json';
        }

        if (!file_exists($path)) {
            return "{\n\t\"payload\": []\n}";
        }
        else {
            return file_get_contents($path);
        }
    }
}