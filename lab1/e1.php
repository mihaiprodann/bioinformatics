<?php

$input = "AUBSDABSDNSBBS";

function getUniqueCharacters($str)
{
  $uniqueChars = [];
  $result = '';
  for ($i = 0; $i < strlen($str); $i++) {
    $char = $str[$i];
    if (!isset($uniqueChars[$char])) {
      $uniqueChars[$char] = true;
      $result .= $char;
    }
  }
  return $result;
}
echo getUniqueCharacters($input);
