<?php

$g = [
  "UUU"=>"Phe","UUC"=>"Phe",
  "UUA"=>"Leu","UUG"=>"Leu","CUU"=>"Leu","CUC"=>"Leu","CUA"=>"Leu","CUG"=>"Leu",
  "AUU"=>"Ile","AUC"=>"Ile","AUA"=>"Ile",
  "AUG"=>"Met",
  "GUU"=>"Val","GUC"=>"Val","GUA"=>"Val","GUG"=>"Val",
  "UCU"=>"Ser","UCC"=>"Ser","UCA"=>"Ser","UCG"=>"Ser","AGU"=>"Ser","AGC"=>"Ser",
  "CCU"=>"Pro","CCC"=>"Pro","CCA"=>"Pro","CCG"=>"Pro",
  "ACU"=>"Thr","ACC"=>"Thr","ACA"=>"Thr","ACG"=>"Thr",
  "GCU"=>"Ala","GCC"=>"Ala","GCA"=>"Ala","GCG"=>"Ala",
  "UAU"=>"Tyr","UAC"=>"Tyr",
  "CAU"=>"His","CAC"=>"His",
  "CAA"=>"Gln","CAG"=>"Gln",
  "AAU"=>"Asn","AAC"=>"Asn",
  "AAA"=>"Lys","AAG"=>"Lys",
  "GAU"=>"Asp","GAC"=>"Asp",
  "GAA"=>"Glu","GAG"=>"Glu",
  "UGU"=>"Cys","UGC"=>"Cys",
  "UGG"=>"Trp",
  "CGU"=>"Arg","CGC"=>"Arg","CGA"=>"Arg","CGG"=>"Arg","AGA"=>"Arg","AGG"=>"Arg",
  "GGU"=>"Gly","GGC"=>"Gly","GGA"=>"Gly","GGG"=>"Gly",
  "UAA"=>"Stop","UAG"=>"Stop","UGA"=>"Stop",
];

echo "Enter DNA:\n";
$dna = trim(fgets(STDIN));
if ($dna === "") { fwrite(STDERR,"Error, empty input\n"); exit(1); }

echo "Input DNA: $dna\n";

$rna = strtoupper(strtr($dna, ["T"=>"U"]));
echo "RNA: $rna\n";

$p = strpos($rna, "AUG");
if ($p === false) { fwrite(STDERR,"Error, no AUG\n"); exit(1); }

$prot = "";
$stop = false;

for ($i = $p; $i+2 < strlen($rna); $i += 3) {
    $c = substr($rna, $i, 3);
    if (!isset($g[$c])) {
        fwrite(STDERR,"Warn, bad codon '$c' at $i, stop\n");
        break;
    }
    $aa = $g[$c];
    if ($aa === "Stop") { $stop = true; break; }
    $prot .= $aa;
}

if (!$stop) {
    if ( (strlen($rna)-$p)%3 != 0 ) {
        fwrite(STDERR,"Warn, no stop and length after start not multiple of 3\n");
    } else {
        fwrite(STDERR,"Warn, no stop codon\n");
    }
}

if ($prot === "") {
    echo "Translation failed\n";
} else {
    echo "Protein: $prot\n";
}
