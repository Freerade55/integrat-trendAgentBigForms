<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/vendor/autoload.php';

class IntegrationBigForm
{
    private $client_id = null;
    private $client_secret = null;
    private $access_code = null;
    public $amoCRM = null;
    private $post = null;

    //lead
    public $location = null;
    public $rawTag = null;
    public $tagCity = null;
    public $location_contact = null;

    //contact and company
    public $name = null;
    public $phone = null;
    public $email = null;

    // company
    public $web = null;
    public $name_company = null;
    public $site = null;
    public $legal_entity_name = null;
    public $legal_address = null;
    public $tax_system = null;
    public $INN = null;
    public $OGRN_OGRNIP = null;
    public $checking_account = null;
    public $BIK = null;
    public $email_for_mailing = null;
    public $actual_office_address = null;
    public $phone_for_courier = null;

    // note
    public $logo = null;
    public $form_style = null;
    public $electr_doc_manage = null;
    public $certificate_INN = null;
    public $certificate_OGRN_OGRNIP = null;
    public $agreement = null;

    public $resp_user_id = null;
    public $resp_user_id_for_task = null;
    public $status_id = null;
    public $form_id = null;
    public $legal_form = null;
    public $task_type = null;

    //самозанятый
    public $certificate_registr = null; //Справка_о_постановке_на_налоговый_учет_снятии_с_учета_0
    public $income_statement = null; //Справка_о_доходах_за_текущий_год_0
    public $profile_screen = null; //Скриншот_профиля_из_приложения_Мой_налог_0
    public $proof_exp = null; //Подтверждение_опыта_работы_на_рынке_недвижимости_0
    public $copy_passport = null; //Копия_паспорта_генерального_директора_0

    function __construct( $postData )
    {
        $this->client_id = '';
        $this->client_secret = '';
        $this->access_code = '';

        $this->amoCRM = \Ufee\Amo\Oauthapi::setInstance([
            'domain' => 'trendagent',
            'client_id' => $this->client_id, // id приложения
            'client_secret' => $this->client_secret,
            'redirect_uri' => '',
        ]);
        $this->amoCRM = \Ufee\Amo\Oauthapi::getInstance( $this->client_id );
        //$this->amoCRM->fetchAccessToken( $this->access_code );


        $this->post = $postData;

        //$this->resp_user_id = 7301734; // ТрендАгент
        //$this->resp_user_id_for_task = 7301734; // ТрендАгент Вероника Василенко -7315525
        $this->status_id = 41787490;
        $this->form_id = $postData['formid'];

        // contact and company
        $this->name =  $postData['Name'];
        $this->phone = $postData['Phone'];
        $this->email = $postData['Email'];

        // company
        $this->web =          $postData['Адреса_сайта'];
        $this->name_company = $postData['agency'];
        $this->location =     $postData['city'];

        $this->legal_entity_name = $postData['Полное_наименование_юридического_лица_в_соответствии_с_данными_из_ЕГРЮЛ'] ??
                                   $postData['Полное_наименование_ИП_в_соответствии_с_данными_из_ЕГРИП'];

        $this->legal_address = $postData['Юридический_адрес_организации'] ?? $postData['Юридический_адрес_регистрации_ИП'];
        $this->tax_system =  $postData['Система_налогообложения'];
        $this->INN =         str_replace(' ', '', $postData['Идентификационный_номер_налогоплательщика_ИНН']);
        $this->OGRN_OGRNIP = $postData['Основной_государственный_регистрационный_номер_индивидуального_предпринимателя_ОГРНИП'] ??
                             $postData['Основной_государственный_регистрационный_номер_ОГРН'];

        $this->checking_account =      $postData['расчетного_счета'];
        $this->BIK =                   $postData['Банковский_идентификационный_код_БИК'];
        $this->email_for_mailing =     $postData['E-mail_для_рассылки_финансовой_информации_закрывающие_документы'];
        $this->actual_office_address = $postData['Фактический_адрес_офиса'] ?? $postData['Фактический_адрес_для_доставки_документов'];
        $this->phone_for_courier =     $postData['Phone_2'];

        // note
        $this->logo =                    $postData['Логотип__0'];
        $this->form_style =              $postData['Фирменный_стиль'];
        $this->electr_doc_manage =       $postData['Работает_ли_ваша_организация_с_ЭДО_электронный_документооборот'];
        $this->certificate_INN =         $postData['Свидетельство_о_постановке_на_учет_в_налоговом_органе_0'];
        $this->certificate_OGRN_OGRNIP = $postData['Свидетельство_о_государственной_регистрации_ОГРН_0'] ??
                                         $postData['Свидетельство_о_государственной_регистрации_ОГРНИП_0'];
        $this->agreement =               $postData['Согласие_о_соблюдении_правил_продажи_объектов_недвижимости_0'];

        // определение параметров
        switch ($this->location) {

            case 'Москва':
                $this->tagCity = 'МСК';
                $this->resp_user_id = 7315549; // Артур Пак
                $this->location_contact = $this->location;
                $this->resp_user_id_for_task = 8119201; // СП
                $this->task_type = 2696320;
            break;

            case 'Санкт-Петербург':
                $this->tagCity = 'СПБ';
                $this->resp_user_id = 7315489; // Глушенко Андрей
                $this->location_contact = $this->location;
                $this->resp_user_id_for_task = 8119201; // Вероника Максимова
                $this->task_type = 2696323;
            break;

            case 'Новосибирск':
                $this->tagCity = 'НСК';
                $this->resp_user_id = 8119249; //NSK Женя Власов
                $this->location_contact = $this->location;
                $this->resp_user_id_for_task = 7315537; //Мария Маценко
                $this->task_type = 2696326;
                break;

            case 'Ростов-на-Дону':
                $this->tagCity = 'РНД';
                $this->resp_user_id = 7315531; // Лямзин РНД
                $this->location_contact = $this->location;
                $this->resp_user_id_for_task = 8119201; //СП
                $this->task_type = 2696329;
                break;


            case 'Казань':
                $this->tagCity = 'КЗН';
                $this->resp_user_id = 7301734;
                $this->location_contact = $this->location;
                $this->resp_user_id_for_task = 7301734;
                $this->task_type = 2885586;
                break;

            default:
                $this->tagCity = 'КРД';
                $this->resp_user_id = 7315474; // Роман
                $this->location_contact = 'Краснодарский край';
                $this->resp_user_id_for_task = 7315537; //Мария Маценко
                $this->task_type = 2696332;
        }

        switch ($this->tax_system) {

            case 'Упрощенная система налогообложения (Доходы)':
                $this->tax_system = 'УСН Д';
                break;

            case 'Упрощенная система налогообложения (Доходы минус расходы)':
                $this->tax_system = 'УСН Д-Р';
                break;

            case 'Общая система налогообложения (ОСН)':
                $this->tax_system = 'ОСН';
                break;

            case 'Налог на профессиональный доход (НПД)':
                $this->tax_system = 'НПД';
                break;

            default:
                $this->tax_system = '---';
        }

        switch ($this->form_id) {

            // ООО
            case 'form450970112':
                $this->legal_form = 'ООО/АО';
                break;

            // ИП
            case 'form450973763':
                $this->legal_form = 'ИП';
                break;

            // самозанятый
            case 'form450979959':
                $this->legal_form = 'Самозанятые';
                $this->certificate_registr = $postData['Справка_о_постановке_на_налоговый_учет_снятии_с_учета_0'];
                $this->income_statement =    $postData['Справка_о_доходах_за_текущий_год_0'];
                $this->profile_screen =      $postData['Скриншот_профиля_из_приложения_Мой_налог_0'];
                $this->proof_exp =           $postData['Подтверждение_опыта_работы_на_рынке_недвижимости_0'];
                $this->copy_passport =       $postData['Копия_паспорта_0'];
                break;

            default:
                $this->legal_form = 'Другое';

        }
    }

    /*
     * @params void
     * @return [ object ] currentContact
     * */
    public function getCurrentContact ()
    {
        $contacts = $this->amoCRM->contacts()->searchByPhone( $this->phone );
        $currentContact = $contacts->first();

        return $currentContact;
    }

    /*
     * @params void
     * @return [ object ] company
     * */

    public function globalSearchCompanies ()
    {
        $companies = $this->amoCRM->companies()->searchByCustomField( $this->INN, 1423075 );
        $company = $companies->first();

        if($company)
            return $company;
        else {
            $company = $this->globalSearchCompanies2();
            return $company;
        }
    }

    /*
  * @params void
  * @return [ object ] company
  * */
    public function globalSearchCompanies2 ()
    {
        $companies = $this->amoCRM->companies()->searchByCustomField( $this->INN, 1423279 );
        $company = $companies->first();

        if($company)
            return $company;
        else{
            $company = $this->globalSearchCompanies3();
            return $company;
        }
    }

    /*
     * @params void
     * @return [ object ] company
     * */
    public function globalSearchCompanies3 ()
    {
        $companies = $this->amoCRM->companies()->searchByCustomField( $this->INN, 1423281 );
        $company = $companies->first();

        if($company)
            return $company;
        else
            return null;
    }

    /*
     * @params [ object ] entity
     * @return void
     * */
    public function createCompanyOnEntity ( $entity )
    {
        $newCompany = $entity->createCompany();

        $newCompany->responsible_user_id = $this->resp_user_id;
        $newCompany->name = $this->name_company;

//        $newCompany->cf('Телефон')->setValue($this->phone);
        $newCompany->cf('Email')->setValue($this->email);
        $newCompany->cf('Web')->setValue($this->web);
        $newCompany->cf('Имя юр. лица')->setValue($this->legal_entity_name);
        $newCompany->cf('Юр. адрес')->setValue($this->legal_address);
        $newCompany->cf('Факт. адрес')->setValue($this->actual_office_address);
        $newCompany->cf('Система налогообл.')->setValue($this->tax_system);
        $newCompany->cf('ИНН')->setValue($this->INN);
        $newCompany->cf('ОГРН/ОГРНИП')->setValue(str_replace(' ', '', $this->OGRN_OGRNIP));
        $newCompany->cf('Расчетный счет')->setValue($this->checking_account);
        $newCompany->cf('БИК')->setValue($this->BIK);
        $newCompany->cf('ЭДО')->setValue($this->electr_doc_manage);
        $newCompany->cf('Email для рассылки')->setValue($this->email_for_mailing);
        $newCompany->cf()->byId(838263)->setValue( (string)$this->phone_for_courier ); //Контакт для курьера
        $newCompany->cf('Локация-компания')->setValue($this->location_contact);
        $newCompany->cf()->byId(1421139)->setValue($this->legal_form); //Тип юр. или физ. лица
        $newCompany->cf('ИНН')->setValue($this->INN);
        $newCompany->cf()->byId(1387309)->setValue($this->logo); //Логотип__0
        $newCompany->cf()->byId(1387311)->setValue($this->form_style); //Фирменный_стиль

        $newCompany->save();

        return $newCompany;
    }

    /*
     * @params void
     * @return [ object ] entity
     * */
    public function createCompany ()
    {
        $newCompany = $this->amoCRM->companies()->create();

        $newCompany->responsible_user_id = $this->resp_user_id;
        $newCompany->name = $this->name_company;

//        $newCompany->cf('Телефон')->setValue($this->phone);
        $newCompany->cf('Email')->setValue($this->email);
        $newCompany->cf('Web')->setValue($this->web);
        $newCompany->cf('Имя юр. лица')->setValue($this->legal_entity_name);
        $newCompany->cf('Юр. адрес')->setValue($this->legal_address);
        $newCompany->cf('Факт. адрес')->setValue($this->actual_office_address);
        $newCompany->cf('Система налогообл.')->setValue($this->tax_system);
        $newCompany->cf('ИНН')->setValue($this->INN);
        $newCompany->cf('ОГРН/ОГРНИП')->setValue(str_replace(' ', '', $this->OGRN_OGRNIP));
        $newCompany->cf('Расчетный счет')->setValue($this->checking_account);
        $newCompany->cf('БИК')->setValue($this->BIK);
        $newCompany->cf('ЭДО')->setValue($this->electr_doc_manage);
        $newCompany->cf('Email для рассылки')->setValue($this->email_for_mailing);
        $newCompany->cf()->byId(838263)->setValue($this->phone_for_courier);
        $newCompany->cf('Локация-компания')->setValue($this->location_contact);
        $newCompany->cf()->byId(1421139)->setValue($this->legal_form); //Тип юр. или физ. лица
        $newCompany->cf()->byId(1387309)->setValue($this->logo); //Логотип__0
        $newCompany->cf()->byId(1387311)->setValue($this->form_style); //Фирменный_стиль

        $newCompany->save();

        return $newCompany;
    }

    /*
     * @params [ object ] entity, [ object ] currentCompany
     * @return void
     * */
    public function attachCompany ( $entity, $currentCompany )
    {
        $entity->attachCompany( $currentCompany );
        $entity->save();
    }

    /*
     * @params [ object ] entity
     * return void
     * */
    public function addNote ( $entity, $text )
    {
        $note = $entity->createNote( $type = 4 );
        $note->text = $text;
        $note->element_type = 1;
        $note->element_id = $entity->id;
        $note->save();
    }

    /*
     * @params [ object ] entity
     * return void
     * */
    public function addTask (
        $entity,
        $expireTime,
        $text,
        $ruid = 0
    )
    {
        $task = $entity->createTask( $type = 2575222 ); //обработать
        $task->text = $text;
        $task->element_type = 2;
        if ($ruid) {
            $task->responsible_user_id = $ruid;
        } else {
            $task->responsible_user_id = $this->resp_user_id_for_task;
        }

        $fw = fopen(__DIR__ . "/../logs/resp.txt", "a");
        fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Uncorrect form'."\n");
        fwrite($fw, print_r($this->resp_user_id_for_task, true));
        fclose($fw);

        $task->complete_till_at = $expireTime;
        $task->element_id = $entity->id;
        $task->save();
    }

    /*
     * @params void
     * @return [ object ] newContact
     * */
    public function createContact ()
    {
        $newContact = $this->amoCRM->contacts()->create();
        $newContact->responsible_user_id = $this->resp_user_id;
        $newContact->name = $this->name; // FIXME take from this->post
        $newContact->cf( 'Телефон' )->setValue( $this->phone );
        $newContact->cf( 'Email' )->setValue( $this->email );
        $newContact->cf('Локация контакта')->setValue($this->location_contact);
        $newContact->cf('Должность')->setValue("Директор агентства недвижимости");

        $newContact->save();

        return $newContact;
    }

    /*
     * @params [ object ] lead
     * @return [ int ] status
     * */
    public function changeStatusOfLead ( $lead, $status )
    {
        $lead->status_id = $status;
        $lead->save();
    }

    /*
     * @params void
     * @return [ object ] newLead
     * */
    public function createLeadInContact ( $entity, $cian = null)
    {
        $newLead = $entity->createLead();
        $newLead->responsible_user_id = $entity->responsible_user_id;
        $newLead->status_id = $this->status_id;

        $newLead->attachTags(['ТА agency', $this->tagCity]);

        $newLead->name = 'Заявка с сайта'; // FIXME take from this->post
        $newLead->save();

        return $newLead;
    }

    /*
     * @params [ object ] contact
     * @return [ array ] leads
     * */
    public function getListOfLeads ( $contact )
    {
        $leads = $contact->leads;
        return $leads;
    }

    /*
    * @params [ object ] contact
    * @return [ array ] companies or [ object ] company
    * */
    public function getCompany ( $contact )
    {
        $company = $contact->company;

        return $company;
    }
}
