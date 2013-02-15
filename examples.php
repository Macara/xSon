<?php
require_once("xson.class.php");

//Basic XML-JSON conversion
echo "Basic XML-JSON conversion: \n";

$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<base id="1">
				<case id="2"></case>
				<case id="3"></case>
			</base>';

$xml_xson = new xSon($xml); 
echo "\n XML input: \n \n".$xml;
echo "\n \n JSON Output: \n \n".$xml_xson->to_string(JSON);

echo "Basic JSON-XML conversion: \n";

//Basic JSON-XML conversion
$json = '{"menu": {
   "id": "file",
   "value": "File",
   "popup": {
     "menuitem": [
       {"value": "New", "onclick": "CreateNewDoc()"},
       {"value": "Open", "onclick": "OpenDoc()"},
       {"value": "Close", "onclick": "CloseDoc()"}
     ],
     "menus" : [
	   {"value": "New", "onclick": "CreateNewDoc()"},
       {"value": "Open", "onclick": "OpenDoc()"},
       {"value": "Close", "onclick": "CloseDoc()"}
	 ]
   }
 }
}';


$json_xson = new xSon($json);
echo "\n \n JSON input: \n \n".$json;
echo "\n XML Output: \n \n".$json_xson->to_string(XML);

?>
