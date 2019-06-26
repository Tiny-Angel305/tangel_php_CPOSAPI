<?php
print(json_encode(array("Response"=> "FAIL", "Code"=> "103", "Message" => "Invalid Path")));

//$ curl -s https://apisandbox.dev.clover.com/v3/merchants/[Merchant ID]/orders --header "Authorization:Bearer {API_Token}" | python -mjson.tool