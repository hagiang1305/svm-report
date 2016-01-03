<?php

// Import functions.php
require_once __DIR__.'/functions.php';

// Convert
set_time_limit(0);

// Dataset avaiable
$datasets = array('cm1',  'jm1', 'kc1', 'kc2',  'pc1');
$dataset = ! empty($_GET['dataset']) ? htmlentities($_GET['dataset']) : 'cm1';

// Paths
$resultPath = __DIR__.'/results';
$svmData = __DIR__.'/data/'.$dataset.'.svm';

// Get SVM to array data
$data = svm2array($svmData);

$plus = array();
$minus = array();

foreach ($data as $el) {
    if ($el[0] == 1) {
        $plus[] = $el;
    } else {
        $minus[] = $el;
    }
}

$SVMProblems = array();

for ($i = 0; $i < 10; $i++) {
    shuffle($plus);
    shuffle($minus);

    $problem = array_merge(
        array_slice($plus, 0, count($plus) / 2),
        array_slice($minus, count($minus) / 2)
    );

    $testProblem = array_merge(
        array_slice($plus, count($plus) / 2),
        array_slice($minus, 0, count($minus) / 2)
    );

    // SVM object
    $svm = new SVM;

    $svm->setOptions(array(
        SVM::OPT_TYPE => SVM::C_SVC,
        SVM::OPT_KERNEL_TYPE => SVM::KERNEL_LINEAR,
    ));

    $model = $svm->train($problem);

    $target = array();
    foreach ($testProblem as $p) {
        unset($p[0]);
        $target[] = $model->predict($p);
    }

    $SVMProblems[] = new SVMProblem($testProblem, $target);
}

?><!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SVM Report</title>

        <!-- Bootstrap CSS -->
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

        <style type="text/css">
            #container {
                padding-top: 50px;
            }
        </style>
    </head>
    <body>

        <div id="container" class="container">
            <form action="index.php" method="get" accept-charset="utf-8" class="clearfix">
                <div class="pull-left form-group">
                    <select name="dataset" class="form-control" onchange="document.location.href=this.options[this.selectedIndex].value;">
                        <?php foreach ($datasets as $_data) : ?>
                            <option <?php echo ($dataset == $_data) ? 'selected' : '' ?> value="index.php?dataset=<?php echo $_data ?>"><?php echo strtoupper($_data); ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="8" class="text-center">
                            <?php echo strtoupper(pathinfo($svmData, PATHINFO_BASENAME)); ?>
                        </th>
                   </tr>
                    <tr>
                        <th>TP</th>
                        <th>FP</th>
                        <th>TN</th>
                        <th>FN</th>
                        <th>Accuracy</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th class="active">f-Measure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($SVMProblems as $SVMProblem) : ?>
                        <tr>
                            <td><?php echo $SVMProblem->TP; ?></td>
                            <td><?php echo $SVMProblem->FP; ?></td>
                            <td><?php echo $SVMProblem->TN; ?></td>
                            <td><?php echo $SVMProblem->FN; ?></td>
                            <td><?php echo $SVMProblem->accuracy(); ?></td>
                            <td><?php echo $SVMProblem->precision(); ?></td>
                            <td><?php echo $SVMProblem->recall(); ?></td>
                            <td class="active"><?php echo $SVMProblem->fMeasure(); ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- jQuery -->
        <script src="//code.jquery.com/jquery.js"></script>
        <!-- Bootstrap JavaScript -->
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    </body>
</html>
