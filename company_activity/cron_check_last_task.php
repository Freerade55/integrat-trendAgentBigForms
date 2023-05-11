<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

//вытаскиваем из таблицы компании с заполненной датой последней задачи и со статусом unset
$companies = $DB->DB->query("SELECT * FROM auto_tasks WHERE last_task_with_result is not null and last_task_status = ?",
    array('unset'));

$date_now = date('Y-m-d H:m:s');
$date_now = strtotime($date_now);
$i = 1;

foreach ($companies as $company_db) {

    $date_last_task_with_result = $company_db['last_task_with_result'];
    $difference = $date_now - $date_last_task_with_result;

    //если разница между текущей датой и датой последней задачи с результатом больше, чем 30 дней, то
    //ставим задачу на компанию и в last_task_status ставим set
    //2592000 секунд - 43200 минут - 30 дней
    if ($difference > 3801600 and $difference < 6393600){
        //если разница больше месяца и меньше двух месяцев, и локация компании не Москва, то ставим задачу
        $i++;
        $company_amo = $amoInt->amoCRM->companies()->find($company_db['company_id']);
        if($company_amo){

            $location_company = $company_amo->cf()->byId(838497)->getValue();

            if(empty($location_company)) continue;
            elseif ($location_company != 'Москва'){

                //тип задачи 2602267 - Частота касания
                addTask($company_amo, 2602267, 3, '72 hours', 'Этого агентства давно не касались. Проверь');

                $DB->DB->query("UPDATE auto_tasks SET last_task_status = ? WHERE company_id = ?",
                    array('set', $company_db['company_id']));
            }
        }
        else{

            $DB->DB->query("DELETE FROM auto_tasks WHERE company_id = ?",
                array($company_db['company_id']));

        }
    }
    elseif ($difference > 6393600){
        //если разница двух месяцев, и локация компании Москва, то ставим задачу
        $i++;
        $company_amo = $amoInt->amoCRM->companies()->find($company_db['company_id']);
        if($company_amo){

            $location_company = $company_amo->cf()->byId(838497)->getValue();

            if(empty($location_company)) continue;
            elseif ($location_company == 'Москва'){

                //тип задачи 2602267 - Частота касания
                addTask($company_amo, 2602267, 3, '72 hours', 'Этого агентства давно не касались. Проверь');

                $DB->DB->query("UPDATE auto_tasks SET last_task_status = ? WHERE company_id = ?",
                    array('set', $company_db['company_id']));
            }
        }
    }
    if ($i == 100) exit();
}
$DB->DB->closeConnection();

//вытаскиваем из таблицы компании с last_task_with_result = null
//$companies_null = $DB->DB->query("SELECT * FROM auto_tasks WHERE last_task_with_result is null");
//
//foreach ($companies_null as $company_null_db) {
//
//    $date_add_company_in_db = $company_null_db['created_at'];
//    $date_add_company_in_db = strtotime($date_now);
//
//    $difference = $date_now - $date_add_company_in_db;
//
//    //если разница между текущей датой и датой добавления компании в БД больше, чем 30 дней, то
//    //ставим задачу на компанию и в last_task_status ставим set
//    //2592000 секунд - 43200 минут - 30 дней
//    if ($difference > 2592000) {
//
//        $company_amo = $amoInt->amoCRM->companies()->find($company_null_db['company_id']);
//
//        //тип задачи 2363656 - контроль
//        addTask($company_amo, 2363656, 3, '48 hours', 'Этого агентства давно не касались. Проверь');
//
//        $DB->DB->query("UPDATE auto_tasks SET last_task_status = ? WHERE company_id = ?",
//            array('set', $company_null_db['company_id']));
//    }
//}



