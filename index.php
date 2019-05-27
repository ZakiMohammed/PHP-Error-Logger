<?php

    require_once('logger.php');

    try {
        
        $db = new Database();

    } catch (Throwable $e) {
        var_dump(Logger::getLog($e, 'Nice exception'));
        Logger::write($e, 'Nice exception logged');
    }

?>