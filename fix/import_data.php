<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/integrationDemoForm.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/functions/amoFunc.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/connectionMySQL.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL($_REQUEST);
define('CSV_PATH', __DIR__);

//$DB->DB->query("DROP TABLE IF EXISTS `csv_data`;");
//$DB->DB->query(
//    "CREATE TABLE IF NOT EXISTS `csv_data_fix` (
//                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//                    `task_id` int(30),
//                    `resp_id` varchar(100),
//                    `status` varchar (15) DEFAULT 'unset',
//                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
//                )"
//);

$csv_file = CSV_PATH . "/ta.csv";
$csvfile = fopen($csv_file, 'r');
$i = 0;

while (!feof($csvfile)) {
    $csv_data[] = fgets($csvfile, 1024);
    $csv_array = explode(",", $csv_data[$i]);
    $insert_csv = array();
    $insert_csv['task_id'] = $csv_array[0];
    $insert_csv['resp_id'] = $csv_array[1];

    $DB->DB->query("INSERT INTO csv_data_fix (task_id, resp_id) VALUES (:task_id, :resp_id)",
        ['task_id' => $insert_csv['task_id'], 'resp_id' => $insert_csv['resp_id']]);

    $i++;
}

fclose($csvfile);
$DB->DB->closeConnection();
