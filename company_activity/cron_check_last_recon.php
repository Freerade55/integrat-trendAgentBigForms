<?php

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL & ~E_NOTICE);

    require_once("require.php");

    $amoInt = new IntegrationDemoForm(null);
    $DB = new ConnectMySQL(null);

    //вытаскиваем из таблицы компании с заполненной датой последней сверки и со статусом unset
    $companies = $DB->DB->query("SELECT * FROM auto_tasks WHERE last_lead_recon is not null and last_lead_recon_status = ?", array("unset"));

    $date_now = date("Y-m-d H:m:s");
    $date_now = strtotime($date_now);

    foreach ($companies as $company_db) {

        $date_last_lead_recon = $company_db["last_lead_recon"];

        $difference = $date_now - $date_last_lead_recon;

        //если разница между текущей датой и датой последней сверки больше чем 60 дней, то
        //ставим задачу на компанию и в last_lead_recon_status ставим set
        //5184000 секунд - 60 дней
        if ($difference > 5184000) {

            $company_amo = $amoInt->amoCRM->companies()->find($company_db["company_id"]);

            //тип задачи 2363656 - Контроль
            $type_task = getCompanyTaskTypeByCity($company_amo, 2363656);
            addTask($company_amo, $type_task, 4, "96 hours", "У АН нет продаж 2 месяца");

            $DB->DB->query("UPDATE auto_tasks SET last_lead_recon_status = ? WHERE company_id = ?", array("set", $company_db["company_id"]));
        }
    }