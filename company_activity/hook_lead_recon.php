<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

$post = $_REQUEST;
//$fw = fopen("hook_lead_recon.txt", "a");
//fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Correct form'."\n");
//fwrite($fw, print_r($post, true));
//fclose($fw);

$lead_id = $post['leads']['add'][0]['id'];
$lead_status_id = $post['leads']['add'][0]['status_id'];

//если статус = новая сделка АН воронка продажи АН записать в таблицу id и status lead
if ($lead_status_id == 42820630){

    $DB->DB->query("INSERT INTO hook_lead_recon (lead_id, status_id)
                    VALUES (:lead_id, :status_id)",
            ['lead_id'     => $lead_id,
             'status_id'   => $lead_status_id]
    );
}

$DB->DB->closeConnection();

//создание таблицы
//$DB->DB->query(
//    "CREATE TABLE IF NOT EXISTS `hook_lead_recon` (
//                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//                    `lead_id` int(15),
//                    `status_id` int(15),
//                    `lead_status` varchar (10) DEFAULT 'unset'
//                )"
//);

