<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/integrationDemoForm.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/functions/amoFunc.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/connectionMySQL.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL($_REQUEST);
define('CSV_PATH', __DIR__);

//$DB->DB->query("DROP TABLE IF EXISTS `csv_data`;");

//$DB->DB->query(
//    "CREATE TABLE IF NOT EXISTS `csv_data` (
//                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//                    `company_id` int(30),
//                    `resp_name` varchar(100),
//                    `status` varchar (15) DEFAULT 'unset',
//                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
//                )"
//);

$csv_file = CSV_PATH . "/ta.csv";
$csvfile = fopen($csv_file, 'r');
$theData = fgets($csvfile);
$i = 0;

while (!feof($csvfile)) {
    $csv_data[] = fgets($csvfile, 1024);
    $csv_array = explode(",", $csv_data[$i]);
    $insert_csv = array();
    $insert_csv['company_id'] = $csv_array[0];
    $insert_csv['resp_name'] = $csv_array[1];

    $DB->DB->query("INSERT INTO csv_data (company_id, resp_name) VALUES (:company_id, :resp_name)",
                  ['company_id' => $insert_csv['company_id'], 'resp_name' => $insert_csv['resp_name']]);
    $i++;
}

fclose($csvfile);
$DB->DB->closeConnection();
