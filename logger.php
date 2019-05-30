<?php

    class Logger {

        static $directoryName = 'log/';
        static $fileName = 'error.json';
        
        public static function init() {

            function handleError($code, $description, $file = null, $line = null, $context = null) {
                list($error, $log) = mapErrorCode($code);
                throw new LoggerException($description, $code, $file, $line, $context, $log, $error);
            }

            function handleException($ex) {
                throw new LoggerException($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
            }
            
            function mapErrorCode($code) {
                $error = $log = null;
                switch ($code) {
                    case E_PARSE:
                    case E_ERROR:
                    case E_CORE_ERROR:
                    case E_COMPILE_ERROR:
                    case E_USER_ERROR:
                        $error = 'Fatal Error';
                        $log = LOG_ERR;
                        break;
                    case E_WARNING:
                    case E_USER_WARNING:
                    case E_COMPILE_WARNING:
                    case E_RECOVERABLE_ERROR:
                        $error = 'Warning';
                        $log = LOG_WARNING;
                        break;
                    case E_NOTICE:
                    case E_USER_NOTICE:
                        $error = 'Notice';
                        $log = LOG_NOTICE;
                        break;
                    case E_STRICT:
                        $error = 'Strict';
                        $log = LOG_NOTICE;
                        break;
                    case E_DEPRECATED:
                    case E_USER_DEPRECATED:
                        $error = 'Deprecated';
                        $log = LOG_NOTICE;
                        break;
                    default :
                        break;
                }
                return array($error, $log);
            }

            error_reporting(E_ALL);
            ini_set("display_errors", "off");            
            set_error_handler("handleError");
            set_exception_handler("handleException");
        }

        public static function save($e, $customMessage = '', $customData = []) {
            
            if (!isset($e)) {
                $bt = debug_backtrace();
                $caller = array_shift($bt);

                if (isset($caller)) {
                    $e = new LoggerException($customMessage, 0, $caller['file'], $caller['line'], null, null, 'NoException');
                } else {
                    $e = new LoggerException($customMessage, 0, null, null, null, null, 'NoException');
                }
            }

            date_default_timezone_set('Asia/Kolkata');

            $logData = Logger::get($e, $customMessage, $customData);            

            $now = date('d_m_Y');
            
            $directoryName = Logger::$directoryName;
            $fileName = Logger::$fileName;

            if (!file_exists($directoryName)) {
                mkdir($directoryName);
            }

            $fileName = $directoryName . $now . '_' . $fileName;  

            if (!file_exists($fileName)) {
                $file = fopen($fileName, "w");
                fclose($file);
            }
            
            $jsonData = file_get_contents($fileName);
            $arrayData = json_decode($jsonData, true);
            if (!isset($arrayData)) {
                $arrayData = [];                
            }
            array_push($arrayData, $logData);
            $jsonData = json_encode($arrayData, JSON_PRETTY_PRINT);

            return file_put_contents($fileName, $jsonData);
        }

        public static function get($e, $customMessage = '', $customData = []) {

            $error = get_class($e) == 'LoggerException' ? $e->getError() : get_class($e);
            $context = [
                'HTTP_HOST'          => $_SERVER['HTTP_HOST'],
                'HTTP_CONNECTION'    => $_SERVER['HTTP_CONNECTION'],
                'HTTP_USER_AGENT'    => $_SERVER['HTTP_USER_AGENT'],                
                'REMOTE_ADDR'        => $_SERVER['REMOTE_ADDR'],                
                'REMOTE_PORT'        => $_SERVER['REMOTE_PORT'],
                'REQUEST_SCHEME'     => $_SERVER['REQUEST_SCHEME'],
                'REQUEST_METHOD'     => $_SERVER['REQUEST_METHOD'],
                'REQUEST_URI'        => $_SERVER['REQUEST_URI'],
                'QUERY_STRING'       => $_SERVER['QUERY_STRING'],
                'PHP_SELF'           => $_SERVER['PHP_SELF'],
                'REQUEST_TIME_FLOAT' => $_SERVER['REQUEST_TIME_FLOAT'],
                'REQUEST_TIME'       => $_SERVER['REQUEST_TIME'],
                'GET'                => $_GET,
                'POST'               => $_POST,
                'POST_BODY'          => file_get_contents('php://input'),
                'FILES'              => $_FILES,
                'COOKIE'             => $_COOKIE,                
            ];

            return array(
                'level' => $error,
                'code' => $e->getCode(),
                'error' => $error,
                'description' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),        
                'date' => date('d-m-Y h:i:s a'),        
                'message' => $error . ' (' . $e->getCode() . '): ' . $e->getMessage() . ' in [' . $e->getFile() . ', line ' . $e->getLine() . ']',
                'customMessage' => $customMessage,
                'customData' => $customData,
                'context' => $context
            );
        }
    }
    
	class LoggerException extends ErrorException {
        
        private $context = null;
        private $log = null;
        private $error = null;

		function __construct($description, $code, $file = null, $line = null, $context = null, $log = null, $error = null) {
            parent::__construct($description, 0, $code, $file, $line);
            $this->context = $context;
            $this->log = $log;
            $this->error = $error;
        }

        public function getContext() {
            return $this->context;
        }
        public function getLog() {
            return $this->log;
        }
        public function getError() {
            return $this->error;
        }
    }
    
    Logger::init();

?>