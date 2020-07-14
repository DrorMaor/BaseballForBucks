<?php

    function printr_to_JSON($printr) {
        $json = "{";
        foreach($printr as $row => $cols) {
            $json.='"'.$cols.'": ';
            $json.='"'.$row[$cols].'", ';
        }
        $json .= "}";
        echo $json;
        /*
        {
          "first name": "John",
          "last name": "Smith",
          "age": 25,
          "address": {
            "street address": "21 2nd Street",
            "city": "New York",
            "state": "NY",
            "postal code": "10021"
          },
          "phone numbers": [
            {
              "type": "home",
              "number": "212 555-1234"
            },
            {
              "type": "fax",
              "number": "646 555-4567"
            }
          ],
          "sex": {
            "type": "male"
          }
        }


        schedule Object ( [team] => 36 [year] => 2012 [W] => 84 [L] => 78 )

        */
    }
?>
