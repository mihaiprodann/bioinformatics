<?php

const BASE_DNA_LEN = 250;

$TRANSPOSONS = array(
    "ATGCGTACGA",   // T1
    "TTACGTTACG",   // T2
    "CGTACGCGTA",   // T3
    "GATTACAGAT",   // T4
);

$base_dna = generate_random_dna(BASE_DNA_LEN);
echo "Initial sequence, length " . strlen($base_dna) . ":\n";
echo $base_dna . "\n\n";

list($dna_with_tps, $inserted_positions) = insert_transposons($base_dna, $TRANSPOSONS);

echo "Final sequence, length " . strlen($dna_with_tps) . ":\n";
echo $dna_with_tps . "\n\n";

echo "Real positions of inserted transposons:\n";
foreach ($inserted_positions as $p) {
    echo $p[0] . ": start = " . $p[1] . ", end = " . $p[2] . "\n";
}
echo "\n";

$detected = detect_transposons($dna_with_tps, $TRANSPOSONS);

echo "Detected positions by the algorithm:\n";
foreach ($detected as $d) {
    echo $d[0] . ": start = " . $d[1] . ", end = " . $d[2] . "\n";
}

function generate_random_dna($len) {
    mt_srand(42);
    $bases = array('A', 'C', 'G', 'T');

    $s = "";
    for ($i = 0; $i < $len; $i++) {
        $idx = mt_rand(0, count($bases) - 1);
        $s .= $bases[$idx];
    }
    return $s;
}

function insert_transposons($base_dna, $transposons) {
    $planned_positions = array(
        50,   // T1
        120,  // T2
        55,   // T3
        180,  // T4
    );

    $positions = array();
    $seq = $base_dna;
    $offset = 0;

    for ($i = 0; $i < count($transposons); $i++) {
        $pattern = $transposons[$i];
        $original_pos = $planned_positions[$i];
        $real_pos = $original_pos + $offset;

        $left = substr($seq, 0, $real_pos);
        $right = substr($seq, $real_pos);
        $seq = $left . $pattern . $right;

        $start = $real_pos;
        $end = $real_pos + strlen($pattern) - 1;

        $positions[] = array("T" . ($i + 1), $start, $end);

        $offset += strlen($pattern);
    }

    return array($seq, $positions);
}

function detect_transposons($dna, $transposons) {
    $results = array();

    for ($i = 0; $i < count($transposons); $i++) {
        $pattern = $transposons[$i];
        $start = 0;

        while (true) {
            $pos = strpos($dna, $pattern, $start);
            if ($pos === false) break;

            $end = $pos + strlen($pattern) - 1;
            $results[] = array("T" . ($i + 1) . " (" . $pattern . ")", $pos, $end);

            $start = $pos + 1;
        }
    }

    return $results;
}
