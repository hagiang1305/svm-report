<?php

// Import functions.php
require_once __DIR__.'/functions.php';

// Datasets
$datasets = array('cm1',  'jm1', 'kc1', 'kc2',  'pc1');

foreach ($datasets as $data) {
    // If we have a svm file before, delete it first.
    if (file_exists($path = 'data/'.$data.'.svm')) {
        unlink($path);
    }

    try {
        arff2svm('data/'.$data.'.arff', $path);
        echo 'Convert successfully! <br>';
    } catch (Exception $e) {
        echo $e->getMessage() . '<br>';
    }
}
