<?php

/*
 * Function:
 * checkoutMaster ()
 *
 * Checkout git master branch.
 */
function checkoutMaster ()  
{
  echo shell_exec ("/usr/bin/git -C ../registry/ o master --quiet 2>&1");
}

/*
 * Function:
 * startsWith ($string, "word", $length)
 *
 * Find lines beginning with "word". Optionally
 * give the length of the string you are looking for.
 */
function startsWith ($haystack, $needle, $length = "0")
{
  if ($length <= 0 || $length > (strlen ($needle)))
    $length = strlen ($needle);

  return (substr ($haystack, 0, $length) === $needle);
}

/*
 * Function:
 * endsWith ($string, "word")
 *
 * Find lines ending with "word".
 */
function endsWith ($haystack, $needle)
{
  $length = strlen ($needle);

  if ($length == 0)
    return true;

  return (substr( $haystack, -$length) === $needle);
}

/*
 * Function:
 * trim_special_chars ($string)
 *
 * Remove special characters.
 */
function trim_special_chars ($string)
{
  return (trim ($string, " \t\n\r\0\x0B"));
}

function writeBirdConfig ($roas)
{
  $json = json_encode($roas, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

  $bird1_fq  = fopen ('roa/bird_roa_dn42.conf',  'w');
  $bird1_fq4 = fopen ('roa/bird4_roa_dn42.conf', 'w');
  $bird1_fq6 = fopen ('roa/bird6_roa_dn42.conf', 'w');

  $bird2_fq  = fopen ('roa/bird_route_dn42.conf',  'w');
  $bird2_fq4 = fopen ('roa/bird4_route_dn42.conf', 'w');
  $bird2_fq6 = fopen ('roa/bird6_route_dn42.conf', 'w');

  fwrite ($bird1_fq,  shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));
  fwrite ($bird1_fq4, shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));
  fwrite ($bird1_fq6, shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));

  fwrite ($bird2_fq,  shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));
  fwrite ($bird2_fq4, shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));
  fwrite ($bird2_fq6, shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges | sed 's/^/# /g'"));

  foreach ($roas["roas"] as $roa)
  {
    $prfx = $roa["prefix"];
    $mxLngth = $roa["maxLength"];
    $sn = $roa["asn"];

    $bird1_strng = "roa $prfx max $mxLngth as $sn;\n";
    $bird2_strng = "route $prfx max $mxLngth as $sn;\n";

    fwrite ($bird1_fq, $bird1_strng);
    fwrite ($bird2_fq, $bird2_strng);

    if (strpos ($prfx, ":") !== false)
    {
      fwrite ($bird1_fq6, $bird1_strng);
      fwrite ($bird2_fq6, $bird2_strng);
    }
    else
    {
      fwrite ($bird1_fq4, $bird1_strng);
      fwrite ($bird2_fq4, $bird2_strng);
    }
  }

  fclose ($bird1_fq);
  fclose ($bird1_fq4);
  fclose ($bird1_fq6);

  fclose ($bird2_fq);
  fclose ($bird2_fq4);
  fclose ($bird2_fq6);
}

function writeRoutinatorExceptionFile ($roas)
{
  $json = json_encode($roas, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);

  $fp = fopen('roa/export_rfc8416_dn42.json', 'w');

  fwrite($fp, $json);

  fclose($fp);
}

function writeExportJSON ($roas)
{
  $n = 0;
  foreach ($roas['roas'] as $object)
  {
    $roas['roas'][$n]['asn'] = "AS" . $roas['roas'][$n]['asn'];
    $n++;
  }

  $json = json_encode($roas, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

  $fp = fopen ('roa/export_dn42.json', 'w');

  fwrite ($fp, $json);

  fclose ($fp);
}

?>
