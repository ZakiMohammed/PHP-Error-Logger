<?php

    class Logger {
        
        public static function init() {

            function handleError($code, $description, $file = null, $line = null, $context = null) {
                $displayErrors = ini_get("display_errors");
                $displayErrors = strtolower($displayErrors);
                if (error_reporting() === 0 || $displayErrors === "on") {
                    return false;
                }		
                list($error, $log) = mapErrorCode($code);
                throw new LoggerException($description, $code, $file, $line, $context, $log, $error);
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
        }

        public static function write($e, $customMessage = '') {
            Logger::fileLog(Logger::getLog($e, $customMessage));
        }

        public static function getLog($e, $customMessage = '') {

            $context = $e->getContext();

            if (isset($context)) {
                $context = [
                    '_GET' => $context['_GET'],
                    '_POST' => $context['_POST'],                    
                    '_REQUEST' => $context['_REQUEST'],
                    '_FILES' => $context['_FILES']
                ];
            }

            return array(
                'level' => $e->getError(),
                'code' => $e->getCode(),
                'error' => $e->getError(),
                'description' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),                
                'message' => $e->getError() . ' (' . $e->getCode() . '): ' . $e->getMessage() . ' in [' . $e->getFile() . ', line ' . $e->getLine() . ']',
                'customMessage' => $customMessage,
                'context' => $context
            );
        }

        private static function fileLog($logData) {
            date_default_timezone_set('Asia/Kolkata');

            $now = date('d_m_Y');
            
            $directoryName = 'log/';
            $fileName = 'error.txt';

            if (!file_exists($directoryName)) {
                mkdir($directoryName);
            }

            $fileName = $directoryName . $now . '_' . $fileName;            

            $fh = fopen($fileName, 'a+');
            if (is_array($logData)) {
                $logData = print_r($logData, 1);
            }
            $status = fwrite($fh, $logData);
            fclose($fh);
            return ($status) ? true : false;
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