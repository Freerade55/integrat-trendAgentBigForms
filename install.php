<?php

    /**
     * Проверка ответа curl после запроса
     * @param mixed $out
     * @param int $code
     * @param string $amo_url
     * @param array $data
     * @param bool $end_result
     * @throws Exception
     */
    function curl_response_check($out, int $code, string $amo_url, array $data, bool $end_result = true)
    {
        $errors = [
            301 => "Moved permanently",
            400 => "Bad request",
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Not found",
            500 => "Internal server error",
            502 => "Bad gateway",
            503 => "Service unavailable"
        ];
        // Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
        if ($code != 200 && $code != 204) {
            $exception_msg = $errors[$code] ?? "$code Undescribed error";
        }
    }

    /**
     * @param string $amo_url
     * @param array $data
     * @param bool $end_result
     * @param bool $need_access
     * @param string $method
     * @return mixed
     * @throws Exception
     */
    function curl_api_send(string $amo_url, array $data = [], bool $end_result = true, bool $need_access = true, string $method = "POST")
    {
        $amo_config = [
            "subdomain" => "trendagent"
        ];
        $headers = [];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => "amoCRM-API-client/1.0",
            CURLOPT_URL => "https://{$amo_config["subdomain"]}.amocrm.ru$amo_url",
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        if ($data) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = "Content-Type: application/json";
        }
        /*if ($need_access) {
            $headers[] = "Authorization: Bearer " . file_get_contents(LC_ROOT . "/token_access.txt");
        }*/
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        //file_put_contents($log_file, "end: " . date("d.m.Y H:i:s") . "\n\n", FILE_APPEND);

        curl_response_check($out, $code, $amo_url, $data, $end_result);

        return json_decode($out, true);
    }

    $url = "/oauth2/access_token";
    $response = curl_api_send($url, [
        "client_id" => "",
        "client_secret" => "",
        "grant_type" => "authorization_code",
        "code" => "",
        "redirect_uri" => "https://hub.integrat.pro/api/trendAgent/site/integration-amoCRM/server.php"
    ], false);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);