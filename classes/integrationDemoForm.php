<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/vendor/autoload.php';

class IntegrationDemoForm
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

    //contact and company
    public $name = null; //"Name"
    public $phone = null; //"Phone"
    public $email = null;//"Email"
    public $location_contact = null;

    // note
    public $user_id = null; //"User_id"

    public $resp_user_id = null;
    public $status_id = null;
    public $demo_status = null; //"Demo_status"

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

        //$this->amoCRM->queries->logs('/var/www/u0574215/data/www/hub.integrat.pro/api/trendAgent/site/integration-amoCRM/logs/queries');

        $this->post = $postData;

        //$this->resp_user_id = 7301734;
        $this->status_id = 41735662;

        $this->name = $postData['Name'];
        $this->phone = $postData['Phone'];
        $this->email = $postData['Email'];

        $this->user_id = $postData['User_id'];
        $this->demo_status = $postData['Demo_status'];

        $this->location = $postData['City'];

        switch ($this->location){

            case 'Москва':
                $this->tagCity = 'МСК';
                $this->resp_user_id = 7315549; //MSK Паук Артур
                break;

            case 'Санкт-Петербург':
                $this->tagCity = 'СПБ';
                $this->resp_user_id = 7315489; //SPB Andrey
                break;

            case 'Ростов-на-Дону':
                $this->tagCity = 'РНД';
                $this->resp_user_id = 7315531; //RND Кшивицкий 2
                break;

            case 'Новосибирск':
                $this->tagCity = 'НСК';
                $this->resp_user_id = 8119249; //NSK Женя Власов
                break;

            default:
                $this->tagCity = 'КРД';
                $this->resp_user_id = 7315474;// Roman KRD
                break;

        }

        // установка локация контакт
       if ( $this->location === 'Краснодар' )
            $this->location = 'Краснодарский край';
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
        $text = 'Заявка с сайта'
    )
    {
        $task = $entity->createTask( $type = 1 );
        $task->text = $text;
        $task->element_type = 2;
        $task->responsible_user_id = $entity->responsible_user_id;
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
        $newContact->name = $this->name;
        $newContact->cf( 'Телефон' )->setValue( $this->phone );
        $newContact->cf( 'Email' )->setValue( $this->email );
        $newContact->cf('Локация контакта')->setValue($this->location);
        $newContact->save();

        return $newContact;
    }

    /*
     * @params void
     * @return [ object ] newLead
     * */
    public function createLeadInContact ( $entity )
    {
        $newLead = $entity->createLead();
        $newLead->responsible_user_id = $entity->responsible_user_id;
        $newLead->status_id = $this->status_id;
        $newLead->attachTags(['ТА demo', $this->tagCity]);
        $newLead->name = 'Заявка с сайта';
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
     * @params [ object ] lead
     * @return [ int ] status
     * */
    public function changeStatusOfLead ( $lead, $status )
    {
        $lead->status_id = $status;
        $lead->save();
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

    /*
    * @params void
    * @return [ object ] company
    * */
    public function globalSearchCompanies ()
    {
        $companies = $this->amoCRM->companies()->searchByCustomField( $this->INN, 'ИНН' );
        $company = $companies->first();

        return $company;
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

        $newCompany->save();
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

}
