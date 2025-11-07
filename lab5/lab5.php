<?php

function gs($src,$n,$min,$max){
    $out=[];
    $L=strlen($src);
    if(!$src || $L==0){ fwrite(STDERR,"Src empty!\n"); return $out; }
    if($max>$L){ fwrite(STDERR,"Warn: max>$L, clamp\n"); $max=$L; }
    if($min>$max){ fwrite(STDERR,"Err: min>max\n"); return $out; }

    for($i=0;$i<$n;$i++){
        $len = rand($min,$max);
        $ms = $L-$len;
        if($ms<0) $ms=0;
        $st = rand(0,$ms);
        $out[] = substr($src,$st,$len);
    }
    return $out;
}

$seq =
"AGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCT".
"AGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCT".
"AGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"GCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCT".
"AGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCTAGCT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"TGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCATGCAT".
"GATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGAT".
"GATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGAT".
"GATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGATCGAT";

$n = 2000; $min = 100; $max = 150;

$S = gs($seq,$n,$min,$max);

echo "--- Sequence Assembly Simulation ---\n";
echo "Original Sequence Length: ".strlen($seq)." bp\n";
echo "Generated ".count($S)." random samples.\n";
echo "Sample length range: $min-$max bp\n\n";

echo "First 5 (of ".count($S).") generated samples:\n";
for($i=0;$i<5 && $i<count($S);$i++){
    echo ($i+1).": ...".$S[$i]."...\n";
}

echo "\nSuccessfully generated and stored ".count($S)." samples in an array.\n";

echo "\n--- Main Problem with Rebuilding ---\n";
echo "Ambiguity from repetitive sequences.\n";
echo "Repeats like 'TGCATGC...' are longer than samples, decoys the assembler.\n";
echo "Reads from within repeats match in many places, create forks, and break the unique path.\n";
