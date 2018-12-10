<?php

// Load lib files.
require ("lib/constants.php");
require ("lib/define.php");
require ("lib/functions.php");

// Define array() we are going to populate with data.
$roas["slurmVersion"] = 1;
$roas["_comments"]["modified"]["commit"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%H'");
$roas["_comments"]["modified"]["merge"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%p'");
$roas["_comments"]["modified"]["author"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%an <%ae>'");
$roas["_comments"]["modified"]["date"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%aD'");
$roas["_comments"]["modified"]["subject"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%s'");
$roas["_comments"]["modified"]["url"] = "https://git.dn42.us/dn42/registry/commit/";
$roas["_comments"]["modified"]["url"] .= $roas["_comments"]["modified"]["commit"];
$roas["validationOutputFilters"]["prefixFilters"] = array();
$roas["validationOutputFilters"]["bgpsecFilters"] = array(); 
$roas["locallyAddedAssertions"]["bgpsecAssertions"] = array();

/*
 *
 * IPv6
 *
 */

$i = 0; // Counter used with tmp $raw_array.
$raw_array = array();  // tmp array() used for storing data to be processed
foreach ($files6 as $file)
{
  $j = 0;

  /*
   * route6 with maxLength value set:
   * - fd42:5d71:219::/48
   * 
   * $ cat ../registry/data/route6/fd42:5d71:219::_48
   * route6:             fd42:5d71:219::/48
   * origin:             AS4242420119
   * max-length:         48
   * mnt-by:             JRB0001-MNT
   * source:             DN42
   */
  
  $data = file("../registry/data/route6/$file");
  
  foreach ($data as $str)
  {
    $str = trim_special_chars ($str);
    
    if     (startsWith ($str, "max",    3)) $raw_array[$i]["max"]       = $str;
    elseif (startsWith ($str, "source", 6)) $raw_array[$i]["source"]    = $str;
    elseif (startsWith ($str, "route6", 6)) $raw_array[$i]["route"]     = $str;
    elseif (startsWith ($str, "origin", 6)) $raw_array[$i]["asn"][$j++] = $str;
    elseif (startsWith ($str, "mnt",    3)) $raw_array[$i]["mnt"]       = $str;
    
    // Catch max-length not set in route object.
    if (empty ($raw_array[$i]["max"])) $raw_array[$i]["max"] = -1;
  }
  $i++;
}

$k = 0;

foreach ($raw_array as $sub_array)
{
  // Extract prefix and subnet size
  // Match prefix sizes 29-64, 80.
  $prefix = array();
  preg_match ("/([a-f0-9\:]{0,128})\/(29|[3-5][0-9]|6[0-4]|80)/",
   explode ("6: ", $sub_array["route"])[1],
   $prefix);
  
  // Extract ta information
  $source = array();
  preg_match ("/([A-Z0-4]+)/",
   explode (":", $sub_array["source"])[1],
   $source);
  
  // Try to extract max-length information
  $maxlength = array();
  if (($sub_array["max"]) != -1)
    preg_match ("/([0-9]+)/",
     explode (":", $sub_array["max"])[1],
     $maxlength);

  // Extract mnt-by information
  $mnt = array();
  preg_match ("/([A-Z0-9\-]+)/",
   explode (":", $sub_array["mnt"])[1],
   $mnt);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = ($prefix[2] < MAX_LEN_IPV6 ? MAX_LEN_IPV6 : $prefix[2]);
  
  $_mnt = $mnt[0];

  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);
    
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["asn"] = trim ($_asn[0], "AS");
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["prefix"] = $_prefix;
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["maxPrefixLength"] = ($_asn[0] != "AS0" ? $_maxlength : MAX_LEN_IPV6_AS0);
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["comment"] = "$web_registry_url/data/inet6num/$prefix[1]_$prefix[2]";
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["source"] = "$_ta";
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["mnt-by"] = "$_mnt";

    $k++;
  }
}

/*
 *
 * IPv4
 *
 */

$i = 0; // Counter used with tmp $raw_array.
$raw_array = array();  // tmp array() used for storing data to be processed 
foreach ($files4 as $file)
{
  $j = 0;

  /*
   * route with maxLength value set:
   * - 172.20.1.0/24
   * 
   * $ cat ../registry/data/route/172.20.1.0_24
   * route:              172.20.1.0/24
   * origin:             AS4242420119
   * max-length:         24
   * mnt-by:             JRB0001-MNT
   * source:             DN42
   */
  
  $data = file("../registry/data/route/$file");
  
  foreach ($data as $str)
  {
    $str = trim_special_chars ($str);
    
    if     (startsWith ($str, "max",    3)) $raw_array[$i]["max"]       = $str;
    elseif (startsWith ($str, "source", 6)) $raw_array[$i]["source"]    = $str;
    elseif (startsWith ($str, "route",  5)) $raw_array[$i]["route"]     = $str;
    elseif (startsWith ($str, "origin", 6)) $raw_array[$i]["asn"][$j++] = $str;
    elseif (startsWith ($str, "mnt",    3)) $raw_array[$i]["mnt"]       = $str;

    // Catch max-length not set in route object.
    if (empty ($raw_array[$i]["max"])) $raw_array[$i]["max"] = -1;
  }
  $i++;
}

foreach ($raw_array as $sub_array)
{
  // Extract prefix and subnet size
  // Match prefix sizes 8-32.
  $prefix = array();
  preg_match ("/([0-9\.]{7,15})\/([8-9]|[1-2][0-9]|3[0-2])/",
   explode (":", $sub_array["route"])[1],
   $prefix);
  
  // Extract ta information
  $source = array();
  preg_match ("/([A-Z0-4]+)/",
   explode (":", $sub_array["source"])[1],
   $source);
  
  // Try to extract max-length information
  $maxlength = array();
  if (($sub_array["max"]) != -1)
    preg_match ("/([0-9]+)/",
     explode (":", $sub_array["max"])[1],
     $maxlength);

  // Extract mnt-by information
  $mnt = array();
  preg_match ("/([A-Z0-9\-]+)/",
   explode (":", $sub_array["mnt"])[1],
   $mnt);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = ($prefix[2] < MAX_LEN_IPV4 ? MAX_LEN_IPV4 : $prefix[2]);

  $_mnt = $mnt[0];
  
  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);
    
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["asn"] = trim ($_asn[0], "AS");
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["prefix"] = $_prefix;
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["maxPrefixLength"] = ($_asn[0] != "AS0" ? $_maxlength : MAX_LEN_IPV4_AS0);
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["comment"] = "$web_registry_url/data/inetnum/$prefix[1]_$prefix[2]";
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["source"] = "$_ta";
    $roas["locallyAddedAssertions"]["prefixAssertions"][$k]["mnt-by"] = "$_mnt";

    $k++;
  }
}

writeRoutinatorExceptionFile($roas);

?>
