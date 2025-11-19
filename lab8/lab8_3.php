<?php

function complement($base) {
    if ($base === 'A') return 'T';
    if ($base === 'T') return 'A';
    if ($base === 'C') return 'G';
    if ($base === 'G') return 'C';
    return 'N';
}

function reverse_complement($seq) {
    $out = "";
    for ($i = strlen($seq) - 1; $i >= 0; $i--) {
        $out .= complement($seq[$i]);
    }
    return $out;
}

function read_fasta($path) {
    $content = file_get_contents($path);
    if ($content === false) die("Cannot read FASTA file\n");

    $seq = "";
    $lines = preg_split("/\r\n|\n|\r/", $content);
    foreach ($lines as $line) {
        if ($line === "") continue;
        if (strlen($line) > 0 && $line[0] === '>') continue;
        $seq .= trim($line);
    }
    return $seq;
}

function find_inverted_repeats($seq, $min_len, $max_len) {
    $n = strlen($seq);
    $max_spacer = 200;

    $count4 = 0;
    $count5 = 0;
    $count6 = 0;

    for ($len = $min_len; $len <= $max_len; $len++) {
        echo "Searching IR of length " . $len . "\n";

        for ($i = 0; $i <= $n - $len; $i++) {
            $left = substr($seq, $i, $len);
            $rc = reverse_complement($left);

            $end_j = $i + $len + $max_spacer;
            if ($end_j > $n - $len) $end_j = $n - $len;

            for ($j = $i + $len; $j <= $end_j; $j++) {
                $right = substr($seq, $j, $len);

                if ($right === $rc) {
                    echo "IR " . $len . " bp. " . $left . " at " . $i . " <-> " . $right . " at " . $j . "\n";

                    if ($len === 4) $count4++;
                    if ($len === 5) $count5++;
                    if ($len === 6) $count6++;
                }
            }
        }
    }

    echo "\n";
    echo "Summary:\n";
    echo "IR of length 4: " . $count4 . "\n";
    echo "IR of length 5: " . $count5 . "\n";
    echo "IR of length 6: " . $count6 . "\n";
}


$fasta_path = $argv[1];
$seq = read_fasta($fasta_path);
$seq_upper = strtoupper($seq);

echo "Loaded sequence with " . strlen($seq_upper) . " bases\n";
echo "Searching for inverted repeats of length 4 to 6\n";

find_inverted_repeats($seq_upper, 4, 6);
