<?php

$input = "AUBSDABSDNSBBS";

function getUniqueCharacters($str)
{
  $uniqueChars = [];
  $result = '';
  for ($i = 0; $i < strlen($str); $i++) {
    $char = $str[$i];
    if (!isset($uniqueChars[$char])) {
      $uniqueChars[$char] = 1;
    } else {
      $uniqueChars[$char]++;
    }
  }
  foreach ($uniqueChars as $char => $count) {
    $result .= $char . ': ' . round(($count / strlen($str)) * 100, 2) . "%\n";
  }
  return $result;
}
echo getUniqueCharacters($input);
