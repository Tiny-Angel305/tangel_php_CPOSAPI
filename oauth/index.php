<?php
include ('../clsApiCalls.php');
$objAPI = new clsApiCalls();
print($objAPI->Requests('auth'));