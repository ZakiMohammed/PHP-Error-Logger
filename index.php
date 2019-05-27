<?php

    require_once('logger.php');

    try {
        print_r($foo);

    } catch (Exception $e) {
        var_dump(Logger::getLog($e, 'Nice exception'));
        Logger::write($e, 'Nice exception logged');
    }

?>