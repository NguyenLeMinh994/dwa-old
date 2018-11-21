<?php

namespace App\Http\Controllers;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Valuta;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){}

    public function verifyPortalAPI(Request $request)
    {
        $reseller_portal_params = $request->all();  //dd($reseller_portal_params);
        if(isset($reseller_portal_params['guid']) && isset($reseller_portal_params['token']))
        {
            $token = $reseller_portal_params['token'];
            $guid = $reseller_portal_params['guid'];
            
            $survey_info = $this->fetchQuestionaireData($guid, $token); //dd($survey_info);
            if($survey_info != null)
            {
                $role = '';
                $caseHandler_Name = '';
                $caseHandler_Email = '';

                $userRole = $this->fetchUserRole($token);
                if($userRole != null){
                    if(isset($userRole['name'])){
                        $caseHandler_Name = $userRole['name'];
                    }

                    if(isset($userRole['email'])){
                        $caseHandler_Email = $userRole['email'];
                    }

                    if(isset($userRole['roles'][0])){
                        if(in_array('admin', $userRole['roles']))
                            $role = 'admin';
                        else
                            $role = $userRole['roles'][0];
                    }

                }

                //generate default value for new customer
                $conditions = array(
                    $guid,
                    $survey_info['CUSTOMER_NAME']->answer,
                    $survey_info['CUSTOMER_CONTACT_EMAIL']->answer,
                    $survey_info['CUSTOMER_TARGET_AZURE_PLATFORM']->answer,
                    $survey_info['CUSTOMER_CURRENCY']->answer
                );
                $checkCustomer = DB::select("CALL procedure_init_customer(?,?,?,?,?)", $conditions);
                
                //clear old session data
                $request->session()->forget('customer_setup_config');

                // Attempt login
                if (Auth::guard('web')->attempt([   //'email'         => $survey_info['RESELLER_CONTACT_PERSON_EMAIL']->answer, 
                                                    'guid'          => $guid,
                                                    'azure_region'  => $survey_info['CUSTOMER_TARGET_AZURE_PLATFORM']->answer,
                                                    'currency_code' => $survey_info['CUSTOMER_CURRENCY']->answer,
                                                    'password'      => '123456'])) 
                {
                    $customer_config = array();
                    $customer_config['token'] = $token;
                    $customer_config['azure_locale'] = trim($survey_info['CUSTOMER_TARGET_AZURE_PLATFORM']->answer);
                    
                    $customer_config['userRole'] = $role;
                    $customer_config['caseHandlerName'] = $caseHandler_Name;
                    $customer_config['caseHandlerEmail'] = $caseHandler_Email;
                    
					$customer_config['currency']['currency_code'] = trim($survey_info['CUSTOMER_CURRENCY']->answer);
                    $valuta = new Valuta();
                    $customer_config['currency']['currency_rate'] = $valuta->changeCurrentRate($survey_info['CUSTOMER_CURRENCY']->answer);
                    
                    //$customer_config['currency']['currency_code'] = "USD";
                    //$customer_config['currency']['currency_rate'] = 1;
                    Session::put('customer_setup_config', $customer_config);  
                    return redirect('/survey-results');
                }
                else {
                    abort(403, 'login failed');
                }
            }
            else{
                abort(403, 'fetch QP null');
            }
        }
        else
            abort(403, 'Token or Guid not valid');
    }

    private function fetchUserRole($token)
    {
        $authorization = "Authorization: Bearer ".$token;
        $ch = curl_init(config('app.api_url').'/api/me');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_result = curl_exec($ch);
        curl_close($ch); 

        //Decode JSON response:
        $result = json_decode($json_result, true); //dd($result);
        
        // $role = '';
        // if(isset($result['roles'][0])){
        //     $role = $result['roles'][0];
        // }
        return $result;
    }

    private function fetchQuestionaireData($guid, $token)
    {
        $authorization = "Authorization: Bearer ".$token;
        $ch = curl_init(config('app.api_url').'/api/questionnaire/'.$guid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_result = curl_exec($ch);
        curl_close($ch); 
        //Decode JSON response:
        $result = json_decode($json_result, true); //dd($result);

        if($result != null && isset($result['answers']))
        {
            $cost_field = array(
                'INFRA_NETWORK_COSTS',
                'INFRA_RELATED_COSTS',
                'INFRA_BACKUP_COSTS',
                'INFRA_POWER_COSTS',
                'INTRA_FTE_COSTS',
                'INFRA_PRIMARY_STORAGE_COSTS',
                'INFRA_AUX_BACKUP_COSTS',
                'GEN_INFRA_TOTAL_COSTS',
                'GEN_INFRA_SPECIFIC_MO_VM_COSTS',
                'GEN_INFRA_HEAVY_BATCH_COSTS',
                'GEN_INFRA_SPECIFIC_HP_VM_COSTS',
                'GEN_INFRA_SPECIFIC_GPU_VM_COSTS',
                'GEN_INFRA_TOTAL_COSTS_WINDOWS_LICENSES',
                'GEN_INFRA_TOTAL_COSTS_LINUX_LICENSES',
                'GEN_INFRA_HYPERVISOR_LICENSE_COSTS',
                'GEN_INFRA_TOTAL_COSTS_SQL_LICENSES',
                'GEN_INFRA_RDS_SERVER_COSTS',
                'GEN_INFRA_CITRIX_SERVER_COSTS',
                'SLA_DISASTER_RECOVERY_COSTS_PER_VM',
                'CONTRACT_COSTS_LABEL'
            );

            $customer_currency = '';
            $currency_rate = 1;

            foreach ($result['answers'] as $temp){
                if ($temp['uid'] == 'CUSTOMER_CURRENCY')
                {
                    $customer_currency = $temp['answer'];
                    break;
                }
            }

            $valuta_model = new Valuta();
            if ($customer_currency != 'USD')
                $currency_rate = $valuta_model->changeCurrentRate($customer_currency);
            
            $survey_info = array();
            $survey_info['case_id'] = $guid;
            foreach($result['answers'] as $item)
            {
                $survey_info[$item['uid']] = new \stdClass();
                $survey_info[$item['uid']]->id              = $item['id'];

                $survey_info[$item['uid']]->section_uid     = $item['section_uid'];
                $survey_info[$item['uid']]->section_title   = $item['section_title'];

                $survey_info[$item['uid']]->uid             = $item['uid'];
                $survey_info[$item['uid']]->title           = $item['title'];
                //convert all primary cost in survey to USD
                if(in_array($item['uid'], $cost_field) && $item['answer']!=null)
                    $survey_info[$item['uid']]->answer      = $item['answer']/$currency_rate;
                else
                    $survey_info[$item['uid']]->answer      = $item['answer'];
                
                    $survey_info[$item['uid']]->remarks         = $item['remarks'];
                $survey_info[$item['uid']]->cpu_name        = (isset($item['cpu_name']))?$item['cpu_name']:null;
                $survey_info[$item['uid']]->cpu_rating      = (isset($item['cpu_rating']))?$item['cpu_rating']:null;
                $survey_info[$item['uid']]->cpu_released    = (isset($item['cpu_released']))?$item['cpu_released']:null;
            }

            //create survey cache
            \Cache::put('survey-info_'.$guid, $survey_info, 30);
            return $survey_info;
        }
        else
            return null;
    }
}
