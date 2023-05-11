<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

//вытаскиваем из таблицы лиды со статусом unset
$raw_leads = $DB->DB->query("SELECT * FROM hook_lead_recon WHERE lead_status = ?",['unset']);

//перебираем каждую строку лида из БД
foreach ($raw_leads as $raw_lead){

    //поиск прикрепленной компании на лиде и амо
    $company_amo = checkCompanyOnLead($amoInt, $raw_lead);

    if($company_amo){

        //если нашли компанию на лиде, ищем её в БД по id
        $company_db = searchCompanyInDB($DB, $company_amo->id);
        //форматируем дату текущей сверки в unix
        $unix_lead_date = strtotime($raw_lead['created_at']);

        //обновляем дату сверки в столбце last_lead_recon и ставим status = unset
        $DB->DB->query("UPDATE auto_tasks SET last_lead_recon = ?, last_lead_recon_status = ? WHERE id = ?",
            array($unix_lead_date, 'unset', $company_db['id']));

        //если дата первой сверки не заполнена, то заполняем её датой текущей сверки
        if($company_db['first_lead_recon'] === null){

            $DB->DB->query("UPDATE auto_tasks SET first_lead_recon = ? WHERE id = ?",
                array($unix_lead_date, $company_db['id']));
        }
        else{

            $unix_time_current_recon = strtotime($raw_lead['created_at']);

            //если дата второй сверки не заполнена, сравниваем месяц даты из f_l_r с месяцем даты текущей сверки
            if($company_db['second_lead_recon'] === null){

                $diff = getDiffBtwnDate($raw_lead['created_at'], $company_db['first_lead_recon']);
                //сравниваем месяц даты из f_l_r с месяцем даты текущей сверки
                if($diff->m === 0){

                    //если этот один и тот же месяц
                    $DB->DB->query("UPDATE auto_tasks SET first_lead_recon = ? WHERE id = ?",
                        array($unix_time_current_recon, $company_db['id']));

                    goto set;

                }
                elseif($diff->m === 1){

                    //если разница в 1 месяц
                    $DB->DB->query("UPDATE auto_tasks SET second_lead_recon = ? WHERE id = ?",
                        array($unix_time_current_recon, $company_db['id']));

                    goto set;

                }
                else{

                    //если разница больше, чем 1 месяц
                    $DB->DB->query("UPDATE auto_tasks SET first_lead_recon = ? WHERE id = ?",
                        array($unix_time_current_recon, $company_db['id']));

                    goto set;

                }
            }
            else{

                $diff = getDiffBtwnDate($raw_lead['created_at'], $company_db['second_lead_recon']);
                //сравниваем месяц даты из s_l_r с месяцем даты текущей сверки
                if($diff->m === 0){

                    //если этот один и тот же месяц
                    $DB->DB->query("UPDATE auto_tasks SET second_lead_recon = ? WHERE id = ?",
                        array($unix_time_current_recon, $company_db['id']));

                    goto set;

                }
                elseif($diff->m === 1){

                    //если разница в 1 месяц
                    $DB->DB->query("UPDATE auto_tasks SET first_lead_recon = ?, second_lead_recon = ? WHERE id = ?",
                        array($company_db['second_lead_recon'], $unix_time_current_recon, $company_db['id']));

                    goto set;
                }
                else{

                    //если разница больше, чем 1 месяц
                    $DB->DB->query("UPDATE auto_tasks SET
                                    first_lead_recon = ?,
                                    second_lead_recon = ?,
                                    two_month_lead_recon_status = ?
                                    WHERE id = ?",
                        array($unix_time_current_recon, null, 'unset', $company_db['id']));
                }
            }
        }
    }
    else{

        //компания не прикреплена к сделке, переходим к след. сделке
        goto set;
    }

    set:
    $DB->DB->query("UPDATE hook_lead_recon SET lead_status = ? WHERE id = ?",
        array('set', $raw_lead['id']));
}

$DB->DB->closeConnection();
