<?php

function calc_temp($s){
    $s=strtoupper($s);
    $a=substr_count($s,'A');
    $t=substr_count($s,'T');
    $c=substr_count($s,'C');
    $g=substr_count($s,'G');
    $l=strlen($s);
    $n=0.001;
    $m=2*($a+$t)+4*($c+$g);
    $m1=81.5+16.6*(log(10)*$n)+0.41*((($c+$g)/$l)*100)-(600/$l);
    return array($m,$m1);
}

echo "Enter DNA sequence: ";
$h=fopen("php://stdin","r");
$inp=trim(fgets($h));
$r=calc_temp($inp);
echo "The melting temperature (Tm) is: (".$r[0].", ".$r[1].") °C\n";
