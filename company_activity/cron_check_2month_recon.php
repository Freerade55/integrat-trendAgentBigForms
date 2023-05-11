<?php

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL & ~E_NOTICE);

    require_once("require.php");

    $amoInt = new IntegrationDemoForm(null);
    $DB = new ConnectMySQL(null);

    //вытаскиваем из таблицы компании с заполненной датой второй сверки и со статусом unset
    $companies = $DB->DB->query("SELECT * FROM auto_tasks WHERE second_lead_recon is not null and two_month_lead_recon_status = ?", array("unset"));

    $date_now = strtotime(date("Y-m-d H:m:s"));

    foreach ($companies as $company_db) {

        $date_second_lead_recon = $company_db["second_lead_recon"];

        $difference = $date_now - $date_second_lead_recon;

        //если разница между текущей датой и датой второй сверки больше чем 30 дней, то
        //ставим задачу на компанию и в two_month_lead_recon_status ставим set
        //2592000 секунд - 43200 минут - 30 дней
        if ($difference > 2592000) {

            $company_amo = $amoInt->amoCRM->companies()->find($company_db["company_id"]);

            //тип задачи 2363656 - Контроль
            $type_task = getCompanyTaskTypeByCity($company_amo, 2363656);
            addTask($company_amo, $type_task, 4, "96 hours", "У АН за последний месяц нет сделок, ранее сделки были. Проверь");

            $DB->DB->query("UPDATE auto_tasks SET two_month_lead_recon_status = ? WHERE company_id = ?", array("set", $company_db["company_id"]));
        }
    }
