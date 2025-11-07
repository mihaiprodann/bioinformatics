<?php

function read_fasta_first_record($path) {
    $content = file_get_contents($path);
    if ($content === false) {
        die("cant read file\n");
    }

    $header = "";
    $seq = "";

    $lines = preg_split("/\r\n|\n|\r/", $content);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "") continue;

        if ($line[0] === '>') {
            if ($seq === "") {
                $header = substr($line, 1);
            } else {
                break;
            }
            continue;
        }

        if ($line[0] === ';') continue; // comentariu

        $n = strlen($line);
        for ($i = 0; $i < $n; $i++) {
            $ch = strtoupper($line[$i]);
            if ($ch === 'A' || $ch === 'C' || $ch === 'G' || $ch === 'T' || $ch === 'N') {
                $seq .= $ch;
            }
        }
    }

    return array($header, $seq);
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

function draw_gel($fragments) {
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
        foreach ($fragments as $f) $lens[] = $f['len'];
        $min_bp = min($lens);
        $max_bp = max($lens);
    }

    foreach ($fragments as $f) {
        $y = len_to_position($f['len'], $min_bp, $max_bp, $gel_height);
        if ($y < 0) $y = 0;
        if ($y > $gel_height - 1) $y = $gel_height - 1;

        for ($x = 2; $x < $gel_width - 2; $x++) {
            $canvas[$y][$x] = '=';
        }
    }

    $out = "";
    for ($y = 0; $y < $gel_height; $y++) {
        for ($x = 0; $x < $gel_width; $x++) {
            $out .= $canvas[$y][$x];
        }
        $out .= "\n";
    }
    return $out;
}


if ($argc < 2) {
    die("lab6 <path_to_fasta>\n");
}

mt_srand(42);

$path = $argv[1];
list($header, $genome) = read_fasta_first_record($path);

$gen_len = strlen($genome);
if ($gen_len === 0) {
    die("empty FASTA sequence\n");
}

$fragments = array();

for ($k = 0; $k < 10; $k++) {
    $max_len_here = max(100, min(3000, $gen_len));
    $len = mt_rand(100, $max_len_here);

    $start_max = $gen_len - $len;
    if ($start_max < 0) $start_max = 0;

    $start = ($start_max === 0) ? 0 : mt_rand(0, $start_max);
    $seq = substr($genome, $start, $len);

    $fragments[] = array(
        'start' => $start,
        'len' => $len,
        'seq' => $seq,
    );
}

echo "Header FASTA: " . $header . "\n";
echo "FASTA length: " . $gen_len . " nt\n";
echo "Extracted fragments: " . count($fragments) . "\n\n";

printf("%-6s %-8s %-8s\n", "Idx", "Start", "Length(bp)");
foreach ($fragments as $i => $f) {
    printf("%-6d %-8d %-8d\n", $i, $f['start'], $f['len']);
}

echo "\nGel electrophoresis (ASCII):\n\n";
echo draw_gel($fragments) . "\n";

for ($i = 0; $i < count($fragments) && $i < 10; $i++) {
    $f = $fragments[$i];
    $preview = substr($f['seq'], 0, 40);
    $suffix = ($f['len'] > 40) ? "..." : "";
    echo "Frag " . $i . " preview: " . $preview . $suffix . "\n";
}
