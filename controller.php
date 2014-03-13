<?php
/**
* Package controller
* c5authy
* @author: Stefan Fodor
* (C) Stefan Fodor @ 2014
*/

class C5authyPackage extends Package {

    //vars
    protected $pkgHandle 			= 'c5authy';
    protected $appVersionRequired	= '5.6.2';
    protected $pkgVersion 			= '0.8';

    /**
     * C5 required functions
     */
    public function getPackageDescription() {
        return t("Authy integration in C5 websites");
    }

    public function getPackageName() {
        return t("C5 Authy");
    }

    public function getPackageHandle(){
        return $this->pkgHandle;
    }

    /**
     * Install the package
     */
    public function install() {

        //callback to parent
        $pkg = parent::install();

    }

    /**
     * Uninstall the package
     */
    public function uninstall() {

        //callback to parrent
        parent::uninstall();

    }

}