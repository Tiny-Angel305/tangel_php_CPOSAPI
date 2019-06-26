<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 6/21/2019
 * Time: 9:11 PM
 */

class clsClover
{
    public $sAPIUrl = "https://apisandbox.dev.clover.com/v3/merchants";

    function Clover($sMerchantId, $sMerchantSecret){
        global $objAPI;

        $aReturn = $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "No Response from API"));

        $headers = [
            "Authorization:Bearer $sMerchantSecret",
            "Content-Type: application/json"
        ];

        $sUrl = $this->sAPIUrl."/$sMerchantId";
        $aResponse = $objAPI->CallCurl($sUrl, $headers);
        $aResult = json_decode($aResponse, true);
        if( sizeof($aResult) > 0 )
        {
            if( isset($aResult["message"]) )
                $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => $aResult["message"]));
            else
            {
                $sMerchantName = $aResult["name"];
                $aReturn = json_encode(array("Response"=> "SUCCESS", "Code"=> "200", "Message" => "Welcome Mr. $sMerchantName"));
            }
        }

        return($aReturn);
    }

    function Clover_Items($sMerchantId, $sMerchantSecret){

        global $objAPI;
        $aReturn = $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "No Response from API"));

        $headers = [
            "Authorization:Bearer $sMerchantSecret",
            "Content-Type: application/json"
        ];

        $iLimit = 100;
        if( isset($_REQUEST['limit']) )
            $iLimit = $_REQUEST['limit'];

        $sUrl = $this->sAPIUrl."/$sMerchantId/items?limit=".$iLimit;
        $aResponse = $objAPI->CallCurl($sUrl, $headers);
        $aResult = json_decode($aResponse, true);
        if( sizeof($aResult) > 0 )
        {
            if( isset($aResult["message"]) )
                $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => $aResult["message"]));
            else
            {
                $aItems = array();
                foreach ($aResult["elements"] as $aItem)
                {
                    $sUnitName = "";
                    if( isset($aItem["unitName"]) )
                        $sUnitName = $aItem["unitName"];
                    $aItems[] = array(
                                "ItemId" => $aItem["id"],
                                "ItemName" => $aItem["name"],
                                "ItemPrice" => $aItem["price"],
                                "UnitName" => $sUnitName,
                                );
                }
                $aReturn = json_encode(array("Response"=> "SUCCESS", "Code"=> "200", "Data" => $aItems));
            }
        }

        return($aReturn);
    }

    function Clover_Order($sMerchantId, $sMerchantSecret, $aOrder){

        $aOrderPrepare = $aOrderPrepare2 = array();
        if( sizeof($aOrder) > 0 )
        {
            $aOrderPrepare["currency"] = $aOrder["currency"];
            $aOrderPrepare["state"] = "open";
            $aOrderPrepare["testMode"] = true;
            $dTotalPrice = 0;
            foreach ($aOrder["items"] as $aItem)
            {
                $iQuantity = $aItem["ItemQuantity"];
                $iLoopCounter = 1;
                $sUnitQty = "";
                if( $aItem["UnitName"] == "OZ" )
                    $sUnitQty = $iQuantity*1000;
                else
                    $iLoopCounter = $iQuantity;

                $dTotalPrice += ($aItem["ItemPrice"]/100) * $iQuantity;

                for($i= 0; $i < $iLoopCounter; $i++){
                    $aOrderPrepare2["items"][] =
                        array(
                            array( "id" => $aItem["ItemId"]),
                            "name" => $aItem["ItemName"],
                            "price" => $aItem["ItemPrice"],
                            "unitQty" => $sUnitQty,
                            "unitName" => $aItem["UnitName"]
                        );
                }
            }

            $aOrderPrepare["total"] = $dTotalPrice*100;
        }
        else
           return(json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "Empty Data for Orders")));

        global $objAPI;
        $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "No Response from API"));

        $headers = [
            "Authorization:Bearer $sMerchantSecret",
            "Content-Type: application/json"
        ];

        $sUrl = $this->sAPIUrl."/$sMerchantId/orders";

        $aResponse = $objAPI->CallCurl($sUrl, $headers, json_encode($aOrderPrepare));

        $aResult = json_decode($aResponse, true);
        if( sizeof($aResult) > 0 )
        {
            if( isset($aResult["message"]) )
                $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => $aResult["message"]));
            else
            {
                $iOrderId = $aResult["id"];
                $sUrl2 = $this->sAPIUrl."/$sMerchantId/orders/$iOrderId/bulk_line_items";
                $aResponse2 = $objAPI->CallCurl($sUrl2, $headers, json_encode($aOrderPrepare2));

                $aResult2 = json_decode($aResponse2, true);
                if( isset($aResult2["message"]) )
                    $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => $aResult2["message"]));
                else
                $aReturn = json_encode(array("Response"=> "SUCCESS", "Code"=> "200", "Message" => "Order has been placed successfully with Order Id : ".$iOrderId));

            }
        }

        return($aReturn);
    }
}