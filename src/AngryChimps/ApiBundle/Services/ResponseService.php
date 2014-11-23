<?php


namespace AngryChimps\ApiBundle\Services;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use Psr\Log\LoggerInterface;

class ResponseService {

    /** @var Request  */
    protected $request;
    protected $payload;
    protected $content;

    /** @var  LoggerInterface */
    protected $loggerService;

    /** @var  ViewHandler */
    protected $viewHandler;

    public function __construct(RequestStack $requestStack, LoggerInterface $loggerService,
                                ViewHandler $viewHandler) {
        $this->request = $requestStack->getCurrentRequest();
        $this->loggerService = $loggerService;
        $this->viewHandler = $viewHandler;
    }

    /**
     * @param Array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function success(array $data = array()) {
        $viewData = $this->getViewData($data, array(), null);

        $this->loggerService->info(json_encode(array('request' => $this->request->getContent())));
        $this->loggerService->info(json_encode(array('success_response' => $viewData)));

        $view = $this->getView($viewData, 200);
        return $this->handleView($view);
    }

    /**
     * @param $code
     * @param array $errors
     * @param \Exception $ex
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function failure($code, array $errors, \Exception $ex = null) {
        $viewData = $this->getViewData(array(), $errors, $ex);

        $this->loggerService->info(json_encode(array('request' => $this->request->getContent())));
        $this->loggerService->info(json_encode(array('failure_response' => $viewData)));

        $view = $this->getView($viewData, $code);
        return $this->handleView($view);
    }

    private function getViewData($data, array $errors = array(), \Exception $ex = null) {
        if($ex === null) {
            $exArr = array();
        }
        else {
            $exArr = array('type' => get_class($ex),
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'stack' => $ex->getTraceAsString(),
            );
        }

        $return = array(
            'payload' => $data,
            'error' => $errors,
            'exception' => $exArr,
            'request' => array(
                'uri' => $this->request->getUri(),
                'method' => $this->request->getMethod(),
                'payload' => $this->getPayload(),
            )
        );

        return $return;
    }

    private function getView($data, $statusCode)
    {
        $view = View::create($data, $statusCode);
        return $view->setFormat('json');
    }

    private function handleView(View $view) {
        return $this->viewHandler->handle($view);
    }

    protected function getPayload() {
        if($this->payload === null) {
            $this->content = json_decode($this->request->getContent(), true);
            $this->payload = $this->content['payload'];
        }
        return $this->payload;
    }


} 