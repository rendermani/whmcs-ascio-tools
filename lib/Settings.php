<?
namespace ascio\whmcs\tools;
require_once("Error.php");
require_once(__DIR__."/../../../../init.php");
if(!class_exists("DnsService")) {
    require_once(__DIR__."/DnsService.php");
}
use ascio\whmcs\ssl as ssl;
use Illuminate\Database\Capsule\Manager as Capsule;
use ascio\whmcs\ssl\AscioUserException;

class Settings {
    protected $settings;
    public $Account;
    public $Password;
    public $AccountTesting;
    public $PasswordTesting;
    public $Environment;
    public $CreateDns;
    private $table; 
    public function __construct($table)
    {
        global $_POST;
        $this->table = $table; 
    }
    public function readDb() {
        $settingsResult = Capsule::table($this->table)
        ->where("role","=", "User")
        ->get();
         foreach($settingsResult as $key =>  $setting) {
            $name = $setting->name;
             $this->$name = $setting->value;
        }   
    }
    public function validate() {

    }
    public function writeDb() {
        global $_POST;
        foreach($_POST as $key => $value) {
            Capsule::table($this->table)
            ->where(["name"=> $key])
            ->update(["value"=>$value]);
        }
    }
    public function test($env) {

    }

}
class SettingsTest {
    /**
     * @var Settings $settings
     */
    protected $settings; 
    protected $sessionId;
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;   
    }
    public function login($testMode) {
       
        if($testMode) {
            $session= array(
                "Account" => $this->settings->AccountTesting,
                "Password" => $this->settings->PasswordTesting
            );
        } else {
            {
                $session= array(
                    "Account" => $this->settings->Account,
                    "Password" => $this->settings->Password
                );
            }
        }     
        //LogIn

        $logIn= array(
            "session" => $session
        );
        $client = $this->getSoapClient($testMode);
        $result = $client->logIn($logIn);
        if($result->LogInResult->ResultCode == 401) {
            throw new ssl\AscioUserException("Login: ".$result->LogInResult->Message,$result->LogInResult->ResultCode);
        } else {
            $this->sessionId = $result->sessionId;
        } 
        return $this->sessionId;        
    }
    public function availability($testMode,$sessionId) {
        $client = $this->getSoapClient($testMode);
        $availabilityCheck= array(
            "sessionId" => $sessionId,
            "domains" => ["test"],
            "tlds" => ["com"],
            "quality" => "Smart"
        );
        $result = $client->availabilityCheck($availabilityCheck);
        if($result->AvailabilityCheckResult->ResultCode == 401) {
            throw new ssl\AscioUserException("Availability Check: ". $result->AvailabilityCheckResult->Message. " .Please contact your Account-Manager",401); 
        }
    }
    public function logout($testMode,$sessionId) {
        $client = $this->getSoapClient($testMode);
        $result = $client->logOut(["sessionId" => $sessionId]);
        return $result;
    }
    public function hasCredentials($testMode) {
        if($testMode && $this->settings->AccountTesting && $this->settings->PasswordTesting) {
            return true;             
        }
        if($testMode==false && $this->settings->Account && $this->settings->Password) {
            return true;             
        }
        return false; 
    }
    public function dns () {
        if(!$this->hasCredentials(false)) {
            throw new ssl\AscioUserException("DNS needs live credentials",401);
        }          
        $client = new \DnsService($this->settings->Account,$this->settings->Password,""); 
        $getZone = new \GetZone();
        $getZone->zoneName = "test.de";
        $response = $client->GetZone($getZone); 
        if($response->GetZoneResult->StatusCode==401) {
            $message = "DNS Check: ". $response->GetZoneResult->StatusMessage. " .The AscioDNS Password must match the Account-Password. Please contact your Account-Manager for further advice.";
            throw new ssl\AscioException($message,401); 
        } else return true;             
    }
    private function getSoapClient($testMode) {
        $wsdl = $testMode ? "https://aws.demo.ascio.com/2012/01/01/AscioService.wsdl" :"https://aws.ascio.com/2012/01/01/AscioService.wsdl";
       return new \SoapClient($wsdl,array( "trace" => 1 ));
    }
}
$settings = new Settings("mod_asciossl_settings");
$settings->readDb();

$settingsTest = new SettingsTest($settings);
try {
    $sessionId = $settingsTest->login(true);
    echo "Login Testing OK\n";
    $settingsTest->availability(true,$sessionId);
    echo "Availability Check Testing OK\n";
    $settingsTest->logout(true,$sessionId);
    $sessionId = $settingsTest->login(false);
    echo "Login Live OK\n";
    $settingsTest->availability(false,$sessionId);
    $settingsTest->logout(false,$sessionId);
    echo "Availability Check Live OK\n";
    $settingsTest->dns();
    echo "DNS Live OK\n";
} catch (\Exception $e) {
    echo "Error: ".$e->getCode()." - ".$e->getMessage()."\n";
}

