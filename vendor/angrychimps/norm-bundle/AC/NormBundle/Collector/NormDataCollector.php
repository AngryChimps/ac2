<?php


namespace AC\NormBundle\Collector;


use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\Services\NormService;
use Assetic\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class NormDataCollector extends DataCollector {
    protected $lastQueryStartTime = null;
    protected $lastQueryData = [];
    protected $data = [];
    protected $totalTime = 0;

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        try {
            if(isset($this->data['queries'])) {
                $this->data['time'] = $this->totalTime;
                 $this->data['querycount'] = count($this->data['queries']);
                $this->data['collector'] = $this->data;
            } else {
                $this->data['time'] = 0;
                $this->data['querycount'] = 0;
                $this->data['collector'] = $this->data;
                $this->data['queries'] = [];
            }
        }
        catch(\Exception $ex) {
            //Do nothing.
        }
    }

    protected function startQuery($data) {
        //If the last query start time isn't null, then we had an exception during our query
        if($this->lastQueryStartTime !== null) {
            $this->lastQueryData['title'] = 'FAILED :: ' . $this->lastQueryData['className'] . ' -- '
                . $this->lastQueryData['method'];
            $this->lastQueryData['time'] = 0;
            $this->data['queries'][] = $this->lastQueryData;
            $this->lastQueryData = [];
            $this->lastQueryStartTime = null;
        }

        $this->lastQueryStartTime = microtime(true);
        $this->lastQueryData = $data;

        return $data;
    }

    public function startCreateQuery($obj) {
        $data = [];
        $data['className'] = get_class($obj);
        $data['method'] = 'CREATE';
        $data['obj'] = $obj->getMapValues();
        return $this->startQuery($data);
    }

    public function startReadQuery($class, $pks) {
        $data = [];
        $data['className'] = $class;
        $data['method'] = 'READ';
        $data['pks'] = $pks;
        return $this->startQuery($data);
    }

    public function startReadByQueryQuery($class, $query, $limit, $offset, $datastoreName) {
        $data = [];
        $data['className'] = $class;
        $data['method'] = 'READ BY QUERY';
        $data['query'] = $query;
        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['datastoreName'] = $datastoreName;
        return $this->startQuery($data);
    }

    public function startReadCollectionByQueryQuery($class, $query, $limit, $offset, $datastoreName) {
        $data = [];
        $data['className'] = $class;
        $data['method'] = 'READ BY QUERY';
        $data['query'] = $query;
        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['datastoreName'] = $datastoreName;
        return $this->startQuery($data);
    }

    public function startUpdateQuery($obj) {
        $data = [];
        $data['className'] = get_class($obj);
        $data['method'] = 'UPDATE';
        $data['obj'] = $obj->getMapValues();
        return $this->startQuery($data);
    }

    public function startDeleteQuery($obj) {
        $data = [];
        $data['className'] = get_class($obj);
        $data['method'] = 'DELETE';
        $data['obj'] = $obj->getMapValues();
        return $this->startQuery($data);
    }

    public function endQuery($debug, $result = null) {
        $this->lastQueryData = $debug;
        $this->lastQueryData['title'] = $this->lastQueryData['className'] . ' -- ' . $this->lastQueryData['method'];
        $this->lastQueryData['time'] = microtime(true) - $this->lastQueryStartTime;
        $this->lastQueryData['result'] = $result;
        $this->data['queries'][] = $this->lastQueryData;
        $this->totalTime += $this->lastQueryData['time'];
        $this->lastQueryData = [];
        $this->lastQueryStartTime = null;
    }

    public function endQueryFailed($debug, $result = null) {
        $this->lastQueryData = $debug;
        $this->lastQueryData['title'] = 'FAILED :: ' . $this->lastQueryData['className'] . ' -- '
            . $this->lastQueryData['method'];
        $this->lastQueryData['time'] = microtime(true) - $this->lastQueryStartTime;
        $this->lastQueryData['result'] = $result;
        $this->data['queries'][] = $this->lastQueryData;
        $this->totalTime += $this->lastQueryData['time'];
        $this->lastQueryData = [];
        $this->lastQueryStartTime = null;
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName()
    {
        return 'norm_data_collector';
    }

    public function getQueries()
    {
        return $this->data['collector']['queries'];
    }

    public function getQuerycount()
    {
        return $this->data['collector']['querycount'];
    }

    public function getTime()
    {
        return $this->data['collector']['time'];
    }
}