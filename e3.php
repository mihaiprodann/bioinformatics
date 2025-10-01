<?php

function getSequenceFromFasta($filename)
{
    $sequence = '';
    $handle = fopen($filename, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '' || $line[0] === '>') {
                continue;
            }
            $sequence .= $line;
        }
        fclose($handle);
    }
    return $sequence;
}

function getSymbolPercentages($seq) {
    $length = strlen($seq);
    $counts = array_count_values(str_split($seq));
    $percentages = [];
    foreach ($counts as $symbol => $count) {
        $percentages[$symbol] = round(($count / $length) * 100, 2);
    }
    return $percentages;
}

$fastaFile = 'yourfile.fasta';
$sequence = getSequenceFromFasta($fastaFile);
$percentages = getSymbolPercentages($sequence);

foreach ($percentages as $symbol => $percent) {
  echo $symbol . ': ' . $percent . "%\n";
}
