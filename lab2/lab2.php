<?php

$B = ['A','T','G','C'];
$seq = "TACGTGCGCGCGAGCTATCTACTGACTTACGACTAGTGTAGCTGCATCATCGATCGA";

echo "Analyzing Sequence: $seq\n";
echo "Sequence Length: ".strlen($seq)."bp\n\n";

nm($seq,2,$B);
echo "\n---------------------------------------------------\n\n";
nm($seq,3,$B);

function nm($s,$n,$b){
    if(strlen($s)<$n){ echo "Too short for $n-mers\n"; return; }

    $all=[]; g($n,"",$b,$all); sort($all);

    $c=[]; foreach($all as $x){ $c[$x]=0; }

    $L=strlen($s)-$n;
    for($i=0;$i<=$L;$i++){
        $t=substr($s,$i,$n);
        if(isset($c[$t])) $c[$t]++;
    }

    $tot=strlen($s)-$n+1;
    echo "--- $n-mer Analysis ---\n";
    echo "Total possible positions in sequence: $tot\n\n";
    printf("%-15s | %-10s | %-15s\n","Combination","Count","Percentage (%)");
    echo "----------------+------------+-----------------\n";

    foreach($all as $k){
        $cnt=$c[$k];
        $pct=$tot?($cnt/$tot*100):0;
        printf("%-15s | %-10d | %-15.2f\n",$k,$cnt,$pct);
    }
}

function g($n,$p,$b,&$r){
    if($n==0){ $r[]=$p; return; }
    foreach($b as $ch){ g($n-1,$p.$ch,$b,$r); }
}
