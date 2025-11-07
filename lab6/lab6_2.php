<?php

function read_fasta_sequence($path) {
    $content = file_get_contents($path);
    if ($content === false) die("cant read: $path\n");

    $seq = "";
    $lines = preg_split("/\r\n|\n|\r/", $content);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "") continue;
        if (strlen($line) > 0 && $line[0] === '>') continue;
        $seq .= $line;
    }

    return strtoupper($seq);
}

function digest_ecori($sequence) {
    $pattern = "GAATTC";
    $fragments = array();

    $start = 0;
    $search_pos = 0;
    $n = strlen($sequence);

    while (true) {
        $pos = strpos($sequence, $pattern, $search_pos);
        if ($pos === false) break;

        $cut_site = $pos + 1; // G^AATTC

        $fragments[] = array(
            'start' => $start,
            'end' => $cut_site,
            'length' => $cut_site - $start,
        );

        $start = $cut_site;
        $search_pos = $cut_site;
    }

    if ($start < $n) {
        $fragments[] = array(
            'start' => $start,
            'end' => $n,
            'length' => $n - $start,
        );
    }

    return $fragments;
}

function len_to_position($len, $min_bp, $max_bp, $gel_height) {
    $eps = 1e-9;

    $lmin = log10((float)$min_bp);
    $lmax = log10((float)$max_bp);
    $lcur = log10((float)$len);

    $norm = ($lmax - $lcur) / (($lmax - $lmin) + $eps);

    $top_margin = 2;
    $bottom_margin = 1;

    $usable = $gel_height - ($top_margin + $bottom_margin);
    if ($usable < 0) $usable = 0;

    return $top_margin + (int)round($norm * $usable);
}

function draw_gel($fragments, $title) {
    $gel_height = 34;
    $gel_width = 30;

    $canvas = array();
    for ($y = 0; $y < $gel_height; $y++) {
        $canvas[$y] = array_fill(0, $gel_width, ' ');
    }

    for ($y = 0; $y < $gel_height; $y++) {
        $canvas[$y][0] = '|';
        $canvas[$y][$gel_width - 1] = '|';
    }

    for ($x = 0; $x < $gel_width; $x++) {
        $canvas[0][$x] = ($x === 0 || $x === $gel_width - 1) ? '+' : '-';
    }

    $min_bp = 100;
    $max_bp = 3000;
    if (count($fragments) > 0) {
        $lens = array();
        foreach ($fragments as $f) $lens[] = $f['length'];
        $min_bp = min($lens);
        $max_bp = max($lens);
    }

    foreach ($fragments as $f) {
        $y = len_to_position($f['length'], $min_bp, $max_bp, $gel_height);
        if ($y > $gel_height - 1) $y = $gel_height - 1;
        if ($y < 0) $y = 0;

        for ($x = 2; $x < $gel_width - 2; $x++) {
            $canvas[$y][$x] = '=';
        }
    }

    $out = "Gel electrophoresis: " . $title . "\n\n";
    for ($y = 0; $y < $gel_height; $y++) {
        for ($x = 0; $x < $gel_width; $x++) {
            $out .= $canvas[$y][$x];
        }
        $out .= "\n";
    }

    return $out;
}


$files = glob("*.fna");
if ($files === false) die("cant read current dir\n");

foreach ($files as $file) {
    echo "\nAnalyze: " . $file . "\n";

    $seq = read_fasta_sequence($file);
    $fragments = digest_ecori($seq);

    echo "Number of fragments: " . count($fragments) . "\n";

    $lengths = array();
    foreach ($fragments as $f) $lengths[] = $f['length'];
    echo "Fragment lengths: " . json_encode($lengths) . "\n";

    $gel = draw_gel($fragments, $file);
    echo $gel . "\n";
}
