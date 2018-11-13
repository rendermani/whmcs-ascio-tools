<?php
namespace ascio\whmcs\ssl;

class Installer {
    /**
     * @var FilesVersion $filesVersion
     */
    public $filesVersion;
    /**
     * @var DbVersion $dbVersion
     */
    public $dbVersion;
    protected $reqiurements;
    public function __construct()
    {   
        $this->filesVersion = new FilesVersion();
        $this->dbVersion = new DbVersion();
    }
    public function showRequirements () {
        $r= new Requirements();        
        $update = $r->add($this->filesVersion->needsUpdate(),"Needs module update ".$this->filesVersion->getStatus());
        $update->setAction("update_files","Update all Files");
        $update = $r->add($this->dbVersion->needsUpdate(),"Needs database update ".$this->dbVersion->getStatus());
        $update->setAction("update_db","Update DB");
        $soap = $r->add(class_exists("SoapClient"),"PHP-SOAP installed");
        $soap->setInstructions("Please install PHP-SOAP on your server.");

        $this->reqiurements = $r;
        return $r->getHtml();
    }
    public function install () {

    }
}

Class Requirements {
    public $requirements = []; 

    public function add($valid, $text) : Requirement {
        $req = new Requirement($valid,$text);
        $this->requirements[] = $req; 
        return $req;
    }
    public function isValid () : bool {
        foreach($this->requirements as $key => $requirement) {
            if(!$requirement->isValid()) return false; 
        }
        return true; 
    }
    public function isInvalid () : bool {
        return !$this->isValid();
    }
    public function getHtml() {
        $html = "";
        foreach($this->requirements as $key => $requirement) {
            $html .= $requirement->getHtml(); 
        }
        $html .= !$this->isValid() ? "<p><br/>Please fix requirements before continuing.</p>" :"";
        return $html; 
    }
}
class Requirement {
    private $valid; 
    private $text;
    private $instructions = false;
    private $action = false;
    private $actionButton = false;
    public function __construct($valid,$text) 
    {   
        $this->text = $text; 
        $this->valid = $valid;
    }
    public function isValid () : bool {
        return $this->valid;
    }
    public function isInvalid (): bool {
        return !$this->valid;
    }
    public function getHtml () {
        if($this->isValid()) {
            $icon =  "ok";
            $color = "darkgreen";
        } else {
             $icon = "remove";
             $color = "darkred";
        }
        return  '
            <div class="row" >
                <div class="col-sm-1" style="width:20px;color:'.$color.'"><span class="glyphicon glyphicon-'.$icon.'"> </span></div>
                <div class="col-sm-3" style="height:45px;color:'.$color.'">'.$this->text.'</div>
                '.$this->getAction().$this->getInstructions().'
            </div>
            ';    
    }
    public function setAction($action,$text) {
        $this->action = $action;
        $this->actionButton  = $text; 
    }
    public function setInstructions($text) {
        $this->instructions  = $text; 
    }
    private function getAction () {
        if($this->action && $this->isInvalid()) {
            return '
                <div class="col-sm-5">
                    
                        <button class="btn btn-alert btn-sm" role="button" id="'.$this->action.'">'.$this->actionButton.'</button>
                   
                </div>';
        }
        return ""; 
    }
    private function getInstructions () {
        if($this->instructions && $this->isInvalid()) {
           return '<div class="col-sm-5">'.$this->instructions.'</div>';
        }
        return "";
    }
}