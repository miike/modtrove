<?php
/* PHP implementation of the Porter Stemming Algorithm
 * Written by Iain Argent for Complinet Ltd., 17/2/00
 * Translated from the PERL version at http://www.muscat.com/~martin/p.txt
 * Version 1.1 (Includes British English endings)
 *   --Reduces words to their base stem for search engines and indexing
 */

function porter_stemmer($word)
{
  $step2list=array(
    'ational'=>'ate', 'tional'=>'tion', 'enci'=>'ence', 'anci'=>'ance', 'izer'=>'ize',
    'iser'=>'ise', 'bli'=>'ble',
    'alli'=>'al', 'entli'=>'ent', 'eli'=>'e', 'ousli'=>'ous', 'ization'=>'ize',
    'isation'=>'ise', 'ation'=>'ate',
    'ator'=>'ate', 'alism'=>'al', 'iveness'=>'ive', 'fulness'=>'ful', 'ousness'=>'ous',
    'aliti'=>'al',
    'iviti'=>'ive', 'biliti'=>'ble', 'logi'=>'log'
  );

  $step3list=array('icate'=>'ic', 'ative'=>'', 'alize'=>'al', 'alise'=>'al', 'iciti'=>'ic', 'ical'=>'ic','ful'=>'', 'ness'=>'');

  $c = "[^aeiou]"; # consonant
  $v = "[aeiouy]"; # vowel
  $C = "${c}[^aeiouy]*"; # consonant sequence
  $V = "${v}[aeiou]*"; # vowel sequence

  $mgr0 = "^(${C})?${V}${C}"; # [C]VC... is m>0
  $meq1 = "^(${C})?${V}${C}(${V})?" . '$'; # [C]VC[V] is m=1
  $mgr1 = "^(${C})?${V}${C}${V}${C}"; # [C]VCVC... is m>1
  $_v = "^(${C})?${v}"; # vowel in stem

  if (strlen($word)<3)
  {
    return $word;
  }

  $word=preg_replace("/^y/", "Y", $word);

  // Step 1a
  $word=preg_replace("/(ss|i)es$/", "\\1", $word); # sses-> ss, ies->es
  $word=preg_replace("/([^s])s$/", "\\1", $word); # ss->ss but s->null

  // Step 1b
  if (preg_match("/eed$/", $word))
  {
    $stem=preg_replace("/eed$/", "", $word);
    if (ereg("$mgr0", $stem))
    {
      $word=preg_replace("/.$/", "", $word);
    }
  }
  elseif (preg_match("/(ed|ing)$/", $word))
  {
    $stem=preg_replace("/(ed|ing)$/", "", $word);
    if (preg_match("/$_v/", $stem))
    {
      $word=$stem;
      if (preg_match("/(at|bl|iz|is)$/", $word))
      {
        $word=preg_replace("/(at|bl|iz|is)$/", "\\1e", $word);
      }
      elseif (preg_match("/([^aeiouylsz])\\1$/", $word))
      {
        $word=preg_replace("/.$/", "", $word);
      }
      elseif (preg_match("/^${C}${v}[^aeiouwxy]$/", $word))
      {
        $word.="e";
      }
    }
  }

  // Step 1c (weird rule)
  if (preg_match("/y$/", $word))
  {
    $stem=preg_replace("/y$/", "", $word);
    if (preg_match("/$_v/", $stem))
    {
      $word=$stem."i";
    }
  }

  // Step 2
  if(preg_match("/(ational|tional|enci|anci|izer|iser|bli|alli|entli|eli|ousli|ization|isation|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/",$word, $matches))
  {
    $stem=preg_replace("/(ational|tional|enci|anci|izer|iser|bli|alli|entli|eli|ousli|ization|isation|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/","", $word);
    $suffix=$matches[1];
    if (preg_match("/$mgr0/", $stem))
    {
      $word=$stem.$step2list[$suffix];
    }
  }

  // Step 3
  if (preg_match("/(icate|ative|alize|alise|iciti|ical|ful|ness)$/", $word, $matches))
  {
    $stem=preg_replace("/(icate|ative|alize|alise|iciti|ical|ful|ness)$/", "", $word);
    $suffix=$matches[1];
    if (preg_match("/$mgr0/", $stem))
    {
      $word=$stem.$step3list[$suffix];
    }
  }

  // Step 4
  if(preg_match("/(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize|ise)$/",$word, $matches))
  {
    $stem=preg_replace("/(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize|ise)$/","", $word);
    $suffix=$matches[1];
    if (preg_match("/$mgr1/", $stem))
    {
      $word=$stem;
    }
  }
  elseif (preg_match("/(s|t)ion$/", $word))
  {
    $stem=preg_replace("/(s|t)ion$/", "\\1", $word);
    if (preg_match("/$mgr1/", $stem)) $word=$stem;
  }

  // Step 5
  if (preg_match("/e$/", $word, $matches))
  {
    $stem=preg_replace("/e$/", "", $word);
    if (preg_match("/$mgr1/", $stem) | (preg_match("/$meq1/", $stem) & ~preg_match("/^${C}${v}[^aeiouwxy]$/", $stem)))
    {
      $word=$stem;
    }
  }
  if (preg_match("/ll$/", $word) & preg_match("/$mgr1/", $word))
  {
    $word=preg_replace("/.$/", "",$word);
  }
  // and turn initial Y back to y
  preg_replace("/^Y/", "y", $word);

  return $word;
}

function porter_stemmer_prime_search_inner($value)
{
  if(preg_match("/^(and|or)$/i", $value))
  {
    return $value;
  }

  // stem the word, the allow for any extension
  return porter_stemmer($value) . '*';
}

function porter_stemmer_prime_search($in)
{
  // chop up the input by space, apply the function and recombine into a string
  $out = implode(' ', array_map("porter_stemmer_prime_search_inner", preg_split("/ +/", $in) ));
  error_log("porter_stemmer_prime_search($in) => $out");
  return $out;
}

/*
$input = $argv[1];
$output = porter_stemmer_prime_search($argv[1]);
echo "$input -> $output";
*/

?>
