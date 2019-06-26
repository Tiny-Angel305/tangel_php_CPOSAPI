<?php
class clsApiCalls
{
    function Requests($sType = ""){

        $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "No Response from API"));
        try{
            switch ($sType){
                case "auth":
                    $aReturn = $this->VerifyData();
                    break;
                case "getitems":
                    $aReturn = $this->VerifyData("GetItems");
                    break;
                case "placeorder":
                    $aReturn = $this->VerifyData("GenerateOrder");
                    break;
                default :
                    $aReturn = json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "Invalid or bad request"));
            }
        }
        catch (Exception $e)
        {
            $aReturn = json_encode(array("Response" => "FAIL", "Code" => "103", "Message" => $e->getMessage()));
        }
        return($aReturn);
    }

    function VerifyData($sCaseType = "AuthenticateMerchant"){

        try{
            if (!isset($_POST['data']))
                throw new Exception("Bad data request for auth");

            $sRequestedJson = $_POST['data'];
            $aJson = json_decode($sRequestedJson, true);
            if (json_last_error())
                throw new Exception("Json Format is not valid");

            if (sizeof($aJson) > 0)
            {
                $aOrder = array();
                $this->CheckArrayKey("api_type,merchant_id,merchant_secret", $aJson);
                $sApiType = $aJson["api_type"];
                $sMerchantId = $aJson["merchant_id"];
                $sMerchantSecret = $aJson["merchant_secret"];
                if( $sCaseType == "GenerateOrder" )
                {
                    $this->CheckArrayKey("order", $aJson); // check order array
                    $this->CheckArrayKey("items", $aJson["order"]); // check items
                    $aOrder = $aJson["order"];
                }
                $aReturn = $this->$sCaseType($sApiType, $sMerchantId, $sMerchantSecret, $aOrder);
            }
            else
                $aReturn = array("Response" => "FAIL", "Code" => "103", "Message" => "Empty JSON");
        }
        catch (Exception $e)
        {
            $aReturn = json_encode(array("Response" => "FAIL", "Code" => "103", "Message" => $e->getMessage()));
        }
        return ($aReturn);

    }

    function AuthenticateMerchant($sApiType, $sMerchantId, $sMerchantSecret, $aOrder = array()){

        switch ($sApiType){
            case "Clover":
                include "ApiTypes/clsClover.php";
                $objClover = new clsClover();
                $sReturn = $objClover->Clover($sMerchantId, $sMerchantSecret);
                break;
            default :
                $sReturn = json_encode(array("response"=> "FAIL", "Code" => "103", "Message" => "Undefined API Type"));
        }
        return($sReturn);
    }

    function GetItems($sApiType, $sMerchantId, $sMerchantSecret, $aOrder = array()){

        switch ($sApiType){
            case "Clover":
                include "ApiTypes/clsClover.php";
                $objClover = new clsClover();
                $sReturn = $objClover->Clover_Items($sMerchantId, $sMerchantSecret);
                break;
            default :
                $sReturn = json_encode(array("response"=> "FAIL", "Code" => "103", "Message" => "Undefined API Type"));
        }
        return($sReturn);
    }

    function GenerateOrder($sApiType, $sMerchantId, $sMerchantSecret, $aOrder = array()){

        switch ($sApiType){
            case "Clover":
                include "ApiTypes/clsClover.php";
                $objClover = new clsClover();
                $sReturn = $objClover->Clover_Order($sMerchantId, $sMerchantSecret, $aOrder);
                break;
            default :
                $sReturn = json_encode(array("response"=> "FAIL", "Code" => "103", "Message" => "Undefined API Type"));
        }
        return($sReturn);
    }

    function CallCurl($sUrl = "", $sHeader = "", $sPostParams = ""){

        try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$sUrl);
        
        if( $sPostParams != "" )
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sPostParams);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if( $sHeader != "" )
            curl_setopt($ch, CURLOPT_HTTPHEADER, $sHeader);

        $Server_OutPut = curl_exec($ch);
        curl_close ($ch);
        $sResponse = ($Server_OutPut);
        return ($sResponse);
        }
        catch (Exception $e)
        {
            return "Exception : ".$e->getMessage();
            die;
        }
    }

    function CheckArrayKey($sKeys = "", $aArray = array())
    {
        if ($sKeys != "") {
            $aKey = explode(",", $sKeys);

            if (sizeof($aKey) > 0) {
                foreach ($aKey as $key => $value) {

                    if (!array_key_exists(trim($value), $aArray))
                        throw new Exception("Couldn't find " . trim($value) . " in provided json...!", 1);

                    if ($aArray[trim($value)] == "")
                        throw new Exception(trim($value) . " cannot be NULL : " . $aArray[trim($value)], 1);
                }
            } else
                throw new Exception("Key couldn't be NULL in CheckArrayKey ", 1);
        } else
            throw new Exception("Key couldn't be NULL in CheckArrayKey ", 1);

    }
}