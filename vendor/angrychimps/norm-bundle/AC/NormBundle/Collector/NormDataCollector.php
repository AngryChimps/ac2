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
    /** @var  NormService */
    protected $norm;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

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
        $this->data['collector'] = $this->norm->collectDebugData();
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