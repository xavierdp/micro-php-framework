<?php

class c_Api
{
  function curl($url,$a_params = [], $method = "POST")
  {
      if (!empty($a_params)) {
          $params = http_build_query($a_params);
      }

      $ch = curl_init();


      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

      if (!empty($params)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
      }

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      $data = curl_exec($ch);

      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($status != "200") {
          return [
              "message" => "ko",
              "data" => "Status Code: " . $status,
          ];
      }

      return [
          "message" => "ok",
          "data" => json_decode($data, true)
      ];
  }
}
