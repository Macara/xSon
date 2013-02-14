<?php
require_once("xson.class.php");

$xml = '<?xml version="1.0" encoding="UTF-8" ?><base id="1"></base>';

$xml_xson = new xSon($xml); 

echo $xml_xson->to_string(JSON);

?>
