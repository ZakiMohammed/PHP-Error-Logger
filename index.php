<?php

    require_once('logger.php');
    require_once('database.php');

    try {

        // Undefined variable: value
        // print_r($value);

         // Undefined offset: 4
        // $array = [1, 2, 3];
        // $polo = $array[4];

        // Division by zero
        // $data = 10 / 0;

        // Missing argument 1
        // niceFunction();

        // Call to undefined function
        // someFunction();

        // Call to a member function wooFunction() on null
        // $object = null;
        // $object->wooFunction();
        
        // Call to a member function wooFunction() on string
        // $object = '';
        // $object->wooFunction();

        // No such file or directory
        // echo file_get_contents('file.txt');

        // expects parameter 2 to be integer, float given
        // echo date('d/M/Y H:i:s', 986346293462948626783);

        // Class 'Database' not found
        // $db = new Database();

        // PDOException A connection attempt failed because the connected party did not properly respond
        $db = new Database();
        $result = $db->executeReader('SELECT * FROM foo;');        

    } catch (Throwable $e) {
        echo json_encode(Logger::get($e, 'Nice exception'));
        Logger::save($e, 'Nice exception logged');
    }

    function niceFunction($param1) {
        return $param1;
    }

?>