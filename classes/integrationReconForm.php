<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/vendor/autoload.php';

class IntegrationReconForm
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
    public $tagForm = null;
    public $location_contact = null;

    //contact and company
    public $name = null;
    public $phone = null;
    public $email = null;

    public $name_agency = null;
    public $INN = null;
    public $name_client = null;
    public $developer = null; //застройщик
    public $date_signature_the_agreement = null;
    public $number_of_contract = null;
    public $price_of_apartment = null;
    public $payment_order = null;
    public $comment_on_payment = null;
    public $feedback_backoffice = null;
    public $formid = null;
    public $comment_about_TA = null;

    public $resp_user_id = null;
    public $status_id = null;

    function __construct( $postData )
    {
        $this->client_id = '';
        $this->client_secret = '';
        $this->access_code = '';

        $this->amoCRM = \Ufee\Amo\Oauthapi::setInstance([
            'domain' => 'trendagent',
            'client_id' => $this->client_id, // id приложения
            'client_secret' => $this->client_secret,
            'redirect_uri' => 'https://hub.integrat.pro/api/trendAgent/site/integration-amoCRM/server.php',
        ]);

        $this->amoCRM = \Ufee\Amo\Oauthapi::getInstance( $this->client_id );
        //$this->amoCRM->fetchAccessToken( $this->access_code );

        $this->amoCRM->queries->logs('/var/www/u0574215/data/www/hub.integrat.pro/api/trendAgent/site/integration-amoCRM/logs/log_recon_queries/queries');

        $this->post = $postData;

        $this->resp_user_id = 7301734;
        $this->status_id = 42820630;

        // contact and company
        $this->name = $postData['Name'] ?? $postData['name'];
        $this->phone = $postData['Phone'];
        $this->email = $postData['Email'];

        $this->name_agency = $postData['Рекламное_название_вашего_агентства_недвижимости'];
        $this->INN = str_replace(' ', '', $postData['ИНН_организации']);

        $this->name_client = $postData['Client'];
        $this->developer = $postData['Застройщик_']; //застройщик
        $this->date_signature_the_agreement = $postData['Дата_подписания_договора'] ?? $postData['Дата_подписания_договора_'];
        $this->number_of_contract = $postData['Номер_договора'];
        $this->price_of_apartment = $postData['Стоимость_квартиры_по_договору'];
        $this->payment_order = $postData['Платёжное_поручение_0'];
        $this->comment_on_payment = $postData['Комментарий_по_оплате_необязательно'];
        $this->feedback_backoffice = $postData['Feedback_backoffice_spb'];
        $this->formid = $postData['formid'];
        $this->comment_about_TA = $postData['Комментарий_по_работе_TrendAgent_необязательно'];
        $this->tagForm = 'Новостр';

        switch ($this->formid)
        {
            // МСК, МСК множество
            case 'form278847618':
            case 'form278856489':
                $this->resp_user_id = 7315546;
                $this->tagCity = 'МСК';
                $this->location_contact = 'Москва';
                break;

            // СПб уступки
            case 'form278848379':
                $this->resp_user_id = 7315489;
                $this->tagCity = 'СПБ';
                $this->location_contact = 'Санкт-Петербург';
                $this->tagForm = 'Уступки';
                break;

            // СПб, СПб множество
            case 'form278845412':
            case 'form278855263':
                $this->resp_user_id = 7315489;
                $this->tagCity = 'СПБ';
                $this->location_contact = 'Санкт-Петербург';
                break;

            // КРД, КРД множество
            case 'form319555735':
            case 'form319555739':
                $this->resp_user_id = 7315474;
                $this->tagCity = 'КРД';
                $this->location_contact = 'Краснодарский край';
                break;

            // НСК, НСК множество
            case 'form370479131':
            case 'form370479134':
                $this->resp_user_id = 8119249; //NSK Женя Власов
                $this->tagCity = 'НСК';
                $this->location_contact = 'Новосибирск';
                break;

            // РНД, РНД множество
            case 'form408788500':
            case 'form408788503':
                $this->resp_user_id = 7315531; //RND Кшивицкий 2
                $this->tagCity = 'РНД';
                $this->location_contact = 'Ростов-на-Дону';
                break;
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
     * @params [ object ] entity, [ object ] currentCompany
     * @return void
     * */
    public function attachCompany ( $entity, $currentCompany )
    {
        $entity->attachCompany( $currentCompany );
        $entity->save();
    }

    /*
     * @params [ object ] entity, [ string ] text
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
        $resp_id = null,
        $type_task = 1,
        $text = 'Заявка с сайта по сверке'
    )
    {
        $task = $entity->createTask( $type = $type_task );
        $task->text = $text;
        $task->element_type = 2;
        $task->responsible_user_id = is_null($resp_id) ? $entity->responsible_user_id : $resp_id;
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

        if($this->resp_user_id)
            $newContact->responsible_user_id = $this->resp_user_id;

        if($this->phone)
            $newContact->cf('Телефон')->setValue($this->phone);

        if($this->email)
            $newContact->cf('Email')->setValue($this->email);

        if($this->name)
            $newContact->name = $this->name;

        if($this->location_contact)
            $newContact->cf('Локация контакта')->setValue($this->location_contact);

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
     * Description
     *
     * @params [ object ] $entity
     * @params array $data
     * @return [ object ] newLead
     * */
    public function createLeadInContact ( $entity, $data )
    {
        $newLead = $entity->createLead();

        if($this->resp_user_id)
            $newLead->responsible_user_id = $entity->responsible_user_id;

        if($this->status_id)
            $newLead->status_id = $this->status_id;

        if($this->developer)
            $newLead->attachTags(['ТА Сверка', $this->tagCity, $this->tagForm]);
        else
            $newLead->attachTags(['ТА Сверка', $this->tagCity, $this->tagForm]);

        $newLead->name = $data['Застройщик'] . ' ' . $data['Дата_подписания_договора'];

        if($this->price_of_apartment)
            $newLead->sale = $data['Стоимость_квартиры_по_договору'];

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
