<?php

echo "FASTA file path: ";
$f = trim(fgets(STDIN));
$L = file($f);
$s = "";

foreach($L as $l){
    if($l!=="" && strlen($l)>0 && $l[0]!=">") {
        $s .= trim($l);
    }
}

$s = strtoupper($s);

$w = 8;
$p = array();
$t = array();
$i = 0;

while($i<=strlen($s)-$w){
    $z = substr($s,$i,$w);
    $a = substr_count($z,'A');
    $u = substr_count($z,'T');
    $c = substr_count($z,'C');
    $g = substr_count($z,'G');
    $m = 2*($a+$u)+4*($c+$g);
    $p[] = $i; $t[]=$m;
    $i = $i+1;
}

echo "Position  Tm\n";
$j = 0;

while($j < count($t)){
    echo $p[$j]." ".str_repeat("#",(int)$t[$j])." ".$t[$j]."\n";
    $j = $j + 1;
}
