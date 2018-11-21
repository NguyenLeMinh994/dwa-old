<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\customers;
use App\Helpers\ExportPowerPoint;
use App\Helpers\ExportWord;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cookie;
use App\Helpers\CustomerCache;
use App\Valuta;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $client;
    private $token;

    protected $customer_case;
    protected $region;
    protected $currency_code;
    protected $survey_info;

    public function __construct(Request $request)
    {
        //$this->middleware('auth');
    }

    public function index()
    {
        $customers = new customers;
        $fields = array(
            'customers.id',
            'customers.name',
            'customers.email',
            'customers.created_at', 
            'customers.updated_at',
            'languages.code',
            'languages.title',
        );
        $customers = $customers->leftJoin('languages', 'customers.language_id', '=', 'languages.id');
        $customers = $customers->select($fields)->paginate(40);
        return view("customers", compact(['customers']));
    }

    public function questionaires()
    {
        $customer_case = '7b31e821-1df5-4d9e-8b6c-d692d0b6d792';
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvbWlncmF0ZTJhenVyZS52YW5kZW53aW5rZWwuY29tXC9hZG1pblwvY3VzdG9tZXItcXVlc3Rpb25uYWlyZVwvOThcL2Rhc2hib2FyZCIsImlhdCI6MTUzODczMzE4NCwiZXhwIjoxNTM5MzM4MDA2LCJuYmYiOjE1Mzg3MzMyMDYsImp0aSI6ImYxeUJ5czNRdFEwZVRrSGQiLCJzdWIiOjI0LCJwcnYiOiI4N2UwYWYxZWY5ZmQxNTgxMmZkZWM5NzE1M2ExNGUwYjA0NzU0NmFhIn0.IxdnZ9BSiLEky58aRmimrDfU1IxxncIsOL2xEOXzXFI";

        $authorization = "Authorization: Bearer ".$token;
        $ch = curl_init('https://migrate2azure.vandenwinkel.com/api/questionnaire/'.$customer_case);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_result = curl_exec($ch);
        curl_close($ch); 
        //Decode JSON response:
        $result = json_decode($json_result, true);

        echo '<pre>',
        print_r($result);
        echo '</pre>';
        // $fields = array(
        //     'customers.name',
        //     'customers.email',
        //     'questions.section_id', 
        //     'questions.id', 
        //     'questions.title',
        //     'answers.answer',
        //     'answers.remarks'
        // );

        // $sections = DB::table('sections')->get();
        // $countries = DB::table('countries')->get();
        // $questionaires = DB::table('answers')
        //     ->leftJoin('customers', 'answers.customer_id', '=', 'customers.id')
        //     ->leftJoin('questions', 'answers.question_id', '=', 'questions.id')
        //     ->select($fields)
        //     ->where('customers.id', '=', request('id'))
        //     ->groupBy('questions.id')
        //     ->get();

		// return view("questionaires", compact(['sections','questionaires','countries']));
    }

    public function surveyResults()
    {
        //dd(\Auth::user()->guid);
        $customer_setup_config = session('customer_setup_config');
        // echo '<!-- <pre>';
        // print_r($customer_setup_config);
        // echo '</pre> -->';

        $customer_case = \Auth::user()->guid; 
        $questionaires = array();
        $questionaires = \Cache::get('survey-info_'.$customer_case); 

        $customer_currency_code = "USD";
        $customer_currency_rate = 1;

        unset($questionaires['case_id']);
        $sections = array();
        foreach($questionaires as $question)
        {
            if (!in_array($question->section_uid, array_column($sections, 'section_uid')))
                $sections[] =  array('section_uid'=>$question->section_uid, 'section_title'=>$question->section_title);
            
            if ($question->uid == 'CUSTOMER_CURRENCY'){
                $customer_currency_code = $question->answer;
            }
        }

        $valuta_model = new Valuta();
        if ($customer_currency_code != 'USD')
            $customer_currency_rate = $valuta_model->changeCurrentRate($customer_currency_code);
        
        return view("survey-results", compact(['questionaires', 'sections', 'customer_currency_code', 'customer_currency_rate']));
    }
}
