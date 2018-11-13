<?php
require_once(realpath(dirname(__FILE__))."/../../../init.php");
require_once("../../servers/asciossl/lib/ProductImporter.php");


header('Content-Type: application/json');


use ascio\whmcs\ssl\Ssl;
use ascio\whmcs\ssl\Params;
use ascio\whmcs\ssl\Fqdn;
use ascio\whmcs\ssl\ProductImporter; 


$pi = new ProductImporter();
$pi->readCSV(__DIR__."/import/products.csv");
$pi->setMargin($_GET["margin"]);
$pi->setRoundStep($_GET["round"]);
$pi->setProducts($_GET["products"]);

if($_GET["action"]=="import") {
    $pi->import();
}
echo json_encode(["html" => $pi->preview()]);