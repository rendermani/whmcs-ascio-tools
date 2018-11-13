<?php
namespace ascio\whmcs\ssl;

class Versions {
    function __construct()
    {
        
    }
}
class Version {
    public $local;
    public $remote;
    public function __construct()
    {
        
    }
    public function query() {
        $this->getLocal();
        $this->getRemote();
    }
    public function needsUpdate() {
        return !($this->local < $this->remote);
    }
    public function getStatus() {
        return "Local: ".$this->local.", Remote: ".$this->remote;
    }

}

class FilesVersion extends Version {
    protected function getLocal () {
        $this->local = 0.2;
    }
    protected function getRemote () {
        $this->remote = 0.4;
    }
}
class DbVersion extends Version {
    protected function getLocal () {
        $this->local = 0.2;
    }
    protected function getRemote () {
        $this->remote = 0.4;
    }
}
