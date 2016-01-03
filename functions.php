<?php

class SVMProblem
{
    public $TP = 0; // Total +1 true
    public $FP = 0; // Total +1 false
    public $TN = 0; // Total -1 true
    public $FN = 0; // Total -1 false

    /**
     * //
     *
     * @param array $testProblem [description]
     * @param array $target      [description]
     */
    public function __construct(array $testProblem, array $target)
    {
        foreach ($testProblem as $i => $problem) {
            if ($target[$i] == 1) {
                if ($problem[0] == $target[$i]) {
                    $this->TP++;
                } else {
                    $this->FP++;
                }
            } elseif ($target[$i] == -1) {
                if ($problem[0] == $target[$i]) {
                    $this->TN++;
                } else {
                    $this->FN++;
                }
            }
        }
    }

    /**
     * //
     *
     * @return float
     */
    public function accuracy()
    {
        return @(($this->TP + $this->TN) / ($this->TP + $this->FP + $this->TN + $this->FN));
    }

    /**
     * //
     *
     * @return float
     */
    public function precision()
    {
        return @($this->TP / ($this->TP + $this->FP));
    }

    /**
     * //
     *
     * @return float
     */
    public function recall()
    {
        return @($this->TP / ($this->TP + $this->FN));
    }

    /**
     * //
     *
     * @return float
     */
    public function fMeasure()
    {
        return @((2 * $this->precision() * $this->recall()) / ($this->precision() + $this->recall()));
    }
}

/**
 * Conver .arff to .svm
 *
 * @param  string $arff Arff path
 * @param  string $svm  SVM path
 * @return void
 */
function arff2svm($arff, $svm = null)
{
    if (pathinfo($arff, PATHINFO_EXTENSION) !== 'arff') {
        throw new InvalidArgumentException("The $arff file must be an arff extensiton");
    }

    if (empty($svm)) {
        $svm = str_replace('.arff', '.svm', $arff);
    }

    $transform = array('true' => '+1', 'false' => '-1');
    $beginToRead = false;

    // Open stream files.
    $svmHandle = fopen($svm, 'a');
    $arffHandle = fopen($arff, 'r');

    // Read and write data.
    while (! feof($arffHandle)) {
        $line = trim(fgets($arffHandle));

        if ($beginToRead && ! empty($line)) {
            $dataList = explode(',', $line);

            // Take last element of $dataList.
            $last = array_pop($dataList);

            switch ($last) {
                case 'yes':
                case 'true':
                    $last = '+1';
                    break;

                default:
                    $last = '-1';
                    break;
            }

            // Build svm format string to $line.
            $line = $last . ' ';
            foreach ($dataList as $i => $k) {
                $line .= $i+1 . ':' . $k . ' ';
            }
            $line .= "\n";

            fwrite($svmHandle, $line, 1024);
        }

        if (strpos($line, '@data') !== false) {
            $beginToRead = true;
        }
    }

    // Close handlers.
    fclose($arffHandle);
    fclose($svmHandle);
}

/**
 * Convert SVM file to array
 *
 * @param  string $path File path
 * @return array
 */
function svm2array($path)
{
    if (!file_exists($path)) {
        throw new InvalidArgumentException("The $path path is not exists");
    }

    $return = array();
    $handle = fopen($path, 'r');

    // Read and write data.
    while (! feof($handle)) {
        $line = fgets($handle);
        $explode = explode(' ', trim($line));

        if (is_numeric($explode[0])) {
            $array = array();

            foreach ($explode as $field) {
                $value = explode(':', $field);

                if (count($value) === 1) {
                    $array[] = (float) $value[0];
                } elseif (count($value) === 2) {
                    $array[intval($value[0])] = (float) $value[1];
                }
            }

            $return[] = $array;
        }
    }

    return $return;
}

/**
 * Save PHP array to svm file
 *
 * @param  array  $data SVM array
 * @param  string $path Save path
 * @return void
 */
function array2svm($data, $path)
{
    $lines = '';

    foreach ($data as $d) {
        $line = $d[0] . ' ';

        foreach ($d as $i => $k) {
            if ($i > 0) {
                $line .= $i . ':' . $k . ' ';
            }
        }

        $lines .= $line . "\n";
    }

    return file_put_contents($path, $lines);
}
