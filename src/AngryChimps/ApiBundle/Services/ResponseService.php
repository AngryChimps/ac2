<?php


namespace AngryChimps\ApiBundle\services;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use Psr\Log\LoggerInterface;

class ResponseService {

    const ERROR_404 = 'Error404';
    const VALIDATION_ERROR = 'ValidationError';
    const USER_NOT_AUTHENTICATED = 'UserNotAuthenticated';
    const USER_NOT_AUTHORIZED = 'UserNotAuthorized';
    const AUTHENTICATION_FAILURE = 'AuthenticationFailure';
    const INVALID_MEMBER_ID = 'InvalidMemberId';
    const INVALID_COMPANY_ID = 'InvalidCompanyId';
    const INVALID_LOCATION_ID = 'InvalidLocationId';
    const INVALID_STAFF_ID = 'InvalidStaffId';
    const AUTHENTICATED_MEMBER_ALREADY_IN_SESSION = 'AuthenticatedMemberAlreadyInSession';
    const INVALID_SESSION_INFORMATION = 'InvalidSessionInformation';
    const UNKNOWN_POST_DATA_FIELD = 'UnknownPostDataField';

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

        $this->loggerService->info(json_encode(array('request' => json_decode($this->request->getContent()))));
        $this->loggerService->info(json_encode(array('success_response' => $viewData)));

        $view = $this->getView($viewData, 200);
        return $this->handleView($view);
    }

    /**
     * @param $code
     * @param array $error
     * @param \Exception $ex
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function failure($code, $error, \Exception $ex = null, $debug = null) {
        $viewData = $this->getViewData(array(), $error, $ex, $debug);

        $this->loggerService->info(json_encode(array('request' => json_decode($this->request->getContent(), true))));
        $this->loggerService->info(json_encode(array('failure_response' => $viewData)));

        $view = $this->getView($viewData, $code);
        return $this->handleView($view);
    }

    private function getViewData($data, $error = null, \Exception $ex = null, $debug = null) {
        if($ex === null) {
            $exArr = array();
        }
        else {
            $exArr = array('type' => get_class($ex),
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'stack' => $this->jTraceEx($ex),
            );
        }

        $return = array(
            'payload' => $data,
            'error' => $error,
            'debug' => $debug,
            'exception' => $exArr,
            'request' => array(
                'session_id' => $this->request->headers->get('angrychimps-api-session-token'),
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

    /**
     * jTraceEx() - provide a Java style exception trace
     * @param $e
     * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return array of strings, one entry per trace line
     */
    protected function jTraceEx($e, $seen=null) {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
//        $result = join("\n", $result);
//        if ($prev)
//            $result  .= "\n" . jTraceEx($prev, $seen);

        return $result;
    }

} 