<?php

function parse_fasta($content) {
    $seq = "";
    $lines = preg_split("/\r\n|\n|\r/", $content);
    foreach ($lines as $line) {
        if ($line === "") continue;
        if (strlen($line) > 0 && $line[0] === '>') continue;

        $line = strtoupper($line);
        $n = strlen($line);
        for ($i = 0; $i < $n; $i++) {
            $ch = $line[$i];
            if ($ch === 'A' || $ch === 'C' || $ch === 'G' || $ch === 'T') {
                $seq .= $ch;
            }
        }
    }
    return $seq;
}

function read_dna_from_stdin() {
    $input = stream_get_contents(STDIN);
    if ($input === false) die("cannot read from stdin\n");
    return parse_fasta($input);
}

function find_cuts($dna, $enzyme) {
    $site = $enzyme['site'];
    $site_len = strlen($site);
    $cuts = array();

    $dna_len = strlen($dna);
    if ($site_len === 0 || $site_len > $dna_len) return $cuts;

    for ($i = 0; $i + $site_len <= $dna_len; $i++) {
        if (substr($dna, $i, $site_len) === $site) {
            $cuts[] = $i + $enzyme['cut_offset'];
        }
    }

    sort($cuts);
    $cuts = array_values(array_unique($cuts));
    return $cuts;
}

function compute_fragments($seq_len, $cuts) {
    $boundaries = array(0);
    foreach ($cuts as $c) $boundaries[] = $c;
    $boundaries[] = $seq_len;

    sort($boundaries);
    $boundaries = array_values(array_unique($boundaries));

    $fragments = array();
    for ($i = 0; $i < count($boundaries) - 1; $i++) {
        $start = $boundaries[$i];
        $end = $boundaries[$i + 1];
        if ($end > $start) $fragments[] = $end - $start;
    }

    return $fragments;
}

function simulate_gel($results) {
    if (count($results) === 0) {
        echo "Nu exista rezultate pentru gel.\n";
        return;
    }

    $largest_lane_height = 30;

    $min_len = PHP_INT_MAX;
    $max_len = 0;

    foreach ($results as $lane) {
        $frags = $lane[1];
        foreach ($frags as $f) {
            if ($f < $min_len) $min_len = $f;
            if ($f > $max_len) $max_len = $f;
        }
    }

    $lane_width = 12;

    $matrix = array();
    for ($r = 0; $r < $largest_lane_height; $r++) {
        $matrix[$r] = array_fill(0, $lane_width * count($results), ' ');
    }

    foreach ($results as $lane_index => $lane) {
        $frags = $lane[1];

        foreach ($frags as $f) {
            $relative = 0.0;
            if ($max_len > $min_len) {
                $relative = ($f - $min_len) / ($max_len - $min_len);
            }
            $y = (int)($relative * ($largest_lane_height - 2));

            $row = ($largest_lane_height - 2) - $y;
            $col_start = $lane_index * $lane_width + 2;

            for ($x = 0; $x < 6; $x++) {
                if ($col_start + $x < count($matrix[$row])) {
                    $matrix[$row][$col_start + $x] = '#';
                }
            }
        }
    }

    echo "\nGel of DNA fragments (ASCII simulation)\n\n";
    echo "        DNA migration\n";
    echo "      +\n";

    foreach ($matrix as $row) {
        echo "|";
        foreach ($row as $ch) echo $ch;
        echo "|\n";
    }

    echo "|";
    for ($i = 0; $i < $lane_width * count($results); $i++) echo "=";
    echo "|\n";

    foreach ($results as $i => $lane) {
        $name = $lane[0];
        $pad = $lane_width * $i + 2;
        for ($s = 0; $s < $pad; $s++) echo " ";
        echo $name . "\n";
    }

    echo "\n";
}


$fasta_path = $argv[1];
$fasta_content = file_get_contents($fasta_path);
if ($fasta_content === false) die("cannot read FASTA file\n");

$dna = parse_fasta($fasta_content);
$len = strlen($dna);

echo "Sequence length: " . $len . " nucleotides\n\n";

$enzymes = array(
    array('name' => 'EcoRI',   'site' => 'GAATTC', 'cut_offset' => 1),
    array('name' => 'BamHI',   'site' => 'GGATCC', 'cut_offset' => 1),
    array('name' => 'HindIII', 'site' => 'AAGCTT', 'cut_offset' => 1),
    array('name' => 'TaqI',    'site' => 'TCGA',   'cut_offset' => 1),
    array('name' => 'HaeIII',  'site' => 'GGCC',   'cut_offset' => 2),
);

$all_results = array();

foreach ($enzymes as $enzyme) {
    $cuts = find_cuts($dna, $enzyme);
    $fragments = compute_fragments($len, $cuts);

    echo "=== " . $enzyme['name'] . " ===\n";
    echo "Site: " . $enzyme['site'] . "\n";
    echo "Cuts: " . count($cuts) . "\n";

    $pos1 = array();
    foreach ($cuts as $c) $pos1[] = $c + 1;
    echo "Positions: " . json_encode($pos1) . "\n";
    echo "Fragments: " . json_encode($fragments) . "\n\n";

    $all_results[] = array($enzyme['name'], $fragments);
}

echo "=== Simulate gel ===\n\n";
simulate_gel($all_results);
