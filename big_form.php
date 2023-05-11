<?php
header("Content-Type: text/html; charset=utf-8");

require_once __DIR__ . '/classes/integrationBigForm.php';
require_once __DIR__ . '/functions/amoFunc.php';

$rawData = $_REQUEST;


$postData = $rawData;


$fw = fopen(__DIR__ . "/logs/big_form_correct.txt", "a");
fwrite($fw, json_encode($postData, JSON_UNESCAPED_UNICODE));
fclose($fw);

//
//$log = file_get_contents(__DIR__ . "/big_form_correct.json");
//$log = json_decode($log, true);
//$log[date("Y-m-d H:i:s")] = $rawData;
//
//$log = json_encode($log, JSON_UNESCAPED_UNICODE);
//file_put_contents(__DIR__ . "/big_form_correct.json", $log);

$amoInt = new IntegrationBigForm( $postData );


$targetPipelineID = 4520776;
$targetStatusID = 41787490;

$currentContact = $amoInt->getCurrentContact();



if ( !$currentContact )
{   // don`t have contact





    $newContact = $amoInt->createContact();
    $note = generateNote($postData);
    $note_self = [];
    if($postData['formid'] === 'form450979959'){

        $note_self = [
            ' Справка о постановке на нал. учет : ' . $amoInt->certificate_registr,
            '----------------------',
            ' Справка о доходах : ' . $amoInt->income_statement,
            '----------------------',
            ' Скрин профиля : ' . $amoInt->profile_screen,
            '----------------------',
            ' Подтверждение опыта работы : ' . $amoInt->proof_exp,
            '----------------------',
            ' Копия паспорта : ' . $amoInt->copy_passport,
            '----------------------',
        ];
    }

    $merge = array_merge($note, $note_self);
    $textNote = implode("\n", $merge);
    $amoInt->addNote ( $newContact, $textNote );

    $newLead = $amoInt->createLeadInContact ( $newContact );

    /*$task = $amoInt->addTask (
        $newLead,
        strtotime(today) + 84000, // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
        $text = 'Клиент заполнил форму с анкетой компании' // FIXME
    );*/
    addTask($newLead, $amoInt->task_type, 2, "23 hours", "Клиент заполнил форму с анкетой компании", 8119201);

    $company = $amoInt->globalSearchCompanies ();

    if( $company )
    {
   
        $amoInt->attachCompany ( $newLead, $company );
        $amoInt->attachCompany ( $newContact, $company );
    }
    else
    {
        // TODO create company
        $newCompany = $amoInt->createCompanyOnEntity ( $newLead );
        $amoInt->attachCompany ( $newContact, $newCompany );  // !!!!!!!!!!! не передается куда-то сюда компания NULL
    }

}
else // have contact
{
    $currentLead = null;
    //поиск сделок у контакта
    $leads = $amoInt->getListOfLeads ( $currentContact );

    if ( $leads )
    {
        $ActiveNotExist = true;

        // перебираем каждую
        foreach ( $leads->collection()->all() as $lead )
        {
            if ( // если есть активная  в целевой воронке
                $lead->status_id !== 143 &&
                $lead->status_id !== 142 &&
                $lead->pipeline_id === $targetPipelineID )
            { // если активная находится в первых пяти этапах
                if ( $lead->status_id === 41735656 ||
                     $lead->status_id === 41735659 ||
                     $lead->status_id === 41735662 ||
                     $lead->status_id === 41735665 ||
                     $lead->status_id === 42289153 )
                {
                    $amoInt->changeStatusOfLead( $lead, $targetStatusID );
                }
                elseif ( $lead->status_id === $targetStatusID ) { // если активная в целевом этапе
                    // ничего не делаем
                    $ok = 'ok';
                } else { // если активная в этапах после целевого

                    /*$task = $amoInt->addTask (
                        $lead,
                        strtotime(today) + 84000, // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
                        $text = 'Клиент заполнил форму с анкетой компании'
                    );*/
                    addTask($lead, $amoInt->task_type, 2, "23 hours", "Клиент заполнил форму с анкетой компании", 8119201);

                }

                $ActiveNotExist = false;

                $currentLead = $lead;

                break;
            }
        }

        if ( $ActiveNotExist )
        {
            $newLead = $amoInt->createLeadInContact ( $currentContact );

            $currentLead = $newLead;

            /*/ TODO create a task in the lead *
            /$task = $amoInt->addTask (
                $newLead,
                strtotime(today) + 84000, // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
                $text = 'Клиент заполнил форму с анкетой компании' // FIXME
            );*/
            addTask($newLead, $amoInt->task_type, 2, "23 hours", "Клиент заполнил форму с анкетой компании", 8119201);
        }
    }
    else
    {
        $newLead = $amoInt->createLeadInContact ( $currentContact );

        /*/ TODO create a task in the lead *
        $task = $amoInt->addTask (
            $newLead,
            strtotime(today) + 84000, // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
            $text = 'Клиент заполнил форму с анкетой компании' // FIXME
        );*/
        addTask($newLead, $amoInt->task_type, 2, "23 hours", "Клиент заполнил форму с анкетой компании", 8119201);

        $currentLead = $newLead;
    }

    // поиск компании на найденном контакте
    $currentCompanyOnContact = $amoInt->getCompany ( $currentContact );

    if ($currentCompanyOnContact){

        $inn_currentCompany = $currentCompanyOnContact->cf( 'ИНН' )->getValue();
        $id_currentCompany = $currentCompanyOnContact->id;

        $newInn = $amoInt->INN;

        if ($inn_currentCompany == $newInn){

            // инн компании на контакте соответствует инн из заявки
            $note = generateNote($postData);

            $note_self = [];
            if($postData['formid'] === 'form450979959'){

                $note_self = [
                    ' Справка о постановке на нал. учет : ' . $amoInt->certificate_registr,
                    '----------------------',
                    ' Справка о доходах : ' . $amoInt->income_statement,
                    '----------------------',
                    ' Скрин профиля : ' . $amoInt->profile_screen,
                    '----------------------',
                    ' Подтверждение опыта работы : ' . $amoInt->proof_exp,
                    '----------------------',
                    ' Копия паспорта : ' . $amoInt->copy_passport,
                    '----------------------',
                ];

            }

            $merge = array_merge($note, $note_self);
            $textNote = implode("\n", $merge);
            $amoInt->addNote ( $currentContact, $textNote );

            $amoInt->attachCompany ( $currentLead, $currentCompanyOnContact );

        } else {

            // инн компании на контакте НЕ соответствует инн из заявки
//            $task = $amoInt->addTask (
//                $currentLead,
//                strtotime(today) + 84000, // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
//                $text = 'На контакте другая компания!', // FIXME
//                8119201
//            );

            //$link_company = 'https://trendagent.amocrm.ru/companies/detail/' . $id_currentCompany;

            $note = generateNote($postData);
            $textNote = implode("\n", $note);

            $amoInt->addNote( $currentContact, $textNote );

            $newCompany = $amoInt->createCompany (); // new

            $amoInt->attachCompany ( $currentLead, $newCompany );
        }
    } else {

        $note = generateNote($postData);
        $textNote = implode("\n", $note);
        $amoInt->addNote ( $currentContact, $textNote );

        // поиск компании по полю ИНН
        $company = $amoInt->globalSearchCompanies ();

        if( $company ) // если нашли, крепим к текущему лиду и к текущему контакту
        {
            $amoInt->attachCompany ( $currentLead, $company ); // работает
            $amoInt->attachCompany ( $currentContact, $company ); // работает
        }
        else // если не нашли, создаем на текущем лиде и крепим к текущему контакту
        {
            $newCompany = $amoInt->createCompanyOnEntity ( $currentLead ); // работает
            $amoInt->attachCompany ( $currentContact, $newCompany ); // не срабатывает крепление
        }
    }
}

