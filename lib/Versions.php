<?php
namespace ascio\whmcs\tools;
require_once(realpath(dirname(__FILE__))."/../../../../init.php");
require_once("Error.php");

use ascio\whmcs\ssl\AscioSystemException;
use Illuminate\Database\Capsule\Manager as Capsule;


class Versions {
    protected $moduleConfig;
    protected $remoteModuleConfig;
    protected $versions; 
    protected $localVersion;
    protected $remoteVersion;
    protected $remoteVersions;
    protected $storageType ="fs";
    protected $gitUrl;
    function __construct($moduleId,$gitUrl)
    {
        $file = __DIR__."/../".$moduleId."/module.json";        
        $cfg = file_get_contents($file);
        if(!$cfg) throw new AscioSystemException("File not found ".$file);                   
        $this->moduleConfig = json_decode($cfg);

        $file = $gitUrl;        
        if(!$cfg) throw new AscioSystemException("File not found ".$gitUrl);                   
        $this->remoteModuleConfig = json_decode($cfg);
        
        $storageType = $this->storageType;         
        $versions = $this->remoteModuleConfig->$storageType->versions;
        foreach($versions as $key => $version) {
            $this->versions["v".$version->version] = new Version($this->localVersion,$version->version);            
        }
        $this->localVersion = reset($this->moduleConfig->$storageType->versions)->version;        
        $this->remoteVersion = reset($versions)->version;
        var_dump($this->remoteVersion);
        $this->remoteVersions = $version;
    }
    public function setGit($gitUrl) {
        $this->getUrl($gitUrl);
    }
    public function getLocalVersion () {
        return $this->localVersion;
    }
    public function needsUpdate() : bool {
        if(count($this->getUpdates()) > 0 ) return true;
        return false; 
    }
    public function getStatus() {
        $v = reset($this->versions)->remote; 

        return "Local: ".$this->localVersion.", Remote: ".$this->remoteVersion;
    }
    public function getUpdates() {
        $updates = [];
        foreach(array_reverse($this->versions) as $key => $version) {
            /**
             * @var Version $version
             */
            if($version->needsUpdate()) {
                $updates[] = $version->remote;
                echo "needs update: ".$version->getStatus()."\n";
            } else {
                echo "no update: ".$version->getStatus()."\n";
            }
        }
        return $updates;
    }
}
class DbVersions extends Versions {
    protected $storageType ="db";
    protected $settingsTable;
    protected $defaultTable; 

    public function __construct($moduleId,$gitUrl) {
        parent::__construct($moduleId,$gitUrl);
    }
    public function setTables($settingsTable,$defaultTable) {
        $this->settingsTable = $settingsTable;
        $this->defaultTable = $defaultTable; 
    }
    public function getLocalVersion () {
        if(!isset($this->settingsTable)) throw new AscioSystemException("No Settings-Table provided");
        $v =  Capsule::table($this->settingsTable)
        ->where(["name"=>"DbVersion"])
        ->first();
        if($v) {
            $this->localVersion =  $v->value;            
        } else {
            $this->localVersion = 0;            
        }
        if($this->localVersion == 0 && isset($this->defaultTable)) {
            $v =  Capsule::table("INFORMATION_SCHEMA.TABLES")
            ->where(["TABLE_NAME"=>$this->defaultTable])
            ->first();
            if($v) $this->localVersion = 0.1;
        }
        return $this->localVersion;
    }

}
class FsVersions extends Versions {
    protected $storageType ="fs";
}

class Version {
    public $local;
    public $remote;
    public function __construct($local,$remote)
    {   
        $this->local = $local;
        $this->remote = $remote;        
    }
    public function needsUpdate() {
        if(!$this->local) return true;
        return !($this->local >= $this->remote);
    }
    public function getStatus() {
        return "Local: ".$this->local.", Remote: ".$this->remote;
    }
}

$versions = new DbVersions("ssl","https://raw.githubusercontent.com/rendermani/whmcs-ascio-tools/master/ssl/module.json");
$versions->setTables("mod_asciossl_settings","mod_asciossl");
$versions->getLocalVersion();
//var_dump($versions->getUpdates());
$versions->getStatus();

$versions = new FsVersions("ssl","https://raw.githubusercontent.com/rendermani/whmcs-ascio-tools/master/ssl/module.json");
$versions->getLocalVersion();
//var_dump($versions->getUpdates());
$versions->getStatus();