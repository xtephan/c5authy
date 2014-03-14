<?php
/**
 * Dashboard config controller
 * c5authy
 * @author: Stefan Fodor
 * (C) Stefan Fodor @ 2014
 */
defined('C5_EXECUTE') or die("Access Denied.");

class DashboardUsersAuthyController extends Controller {

    public function setIcons() {
        $sp = Page::getByPath('/dashboard/users/authy');
        $sp->setAttribute('icon_dashboard', "icon-cog");
    }

    /**
     * View task
     * @param string $msg
     */
    public function view( $msg = null ) {

        //sucess message if needed
        if( $msg == "success" ) {
            $this->set("message", t('Configuration updated successfully!'));
        }

        //error msg
        if( $msg == "key_error" ) {
            $this->set("error", t('API key cannot be null!'));
        }

        //grab existing configuration
        $pkg = Package::getByHandle("c5authy");
        $co = new Config();
        $co->setPackageObject($pkg);

        //send saved config to view
        $this->set( 'authy_enabled', $co->get('authy_enabled') );
        $this->set( 'authy_server_production', $co->get('authy_server_production') );
        $this->set( 'authy_api_key', $co->get('authy_api_key') );
    }

    /**
     * Task that updates Authy Configuration
     */
    public function update_config() {

        if( empty($_POST["AUTHY_KEY"]) ) {
            $this->redirect( "/dashboard/users/authy/key_error" );
        }

        //set options on package bases
        $pkg = Package::getByHandle("c5authy");
        $co = new Config();
        $co->setPackageObject($pkg);

        //should be toggle the login page?
        $to_enable = $_POST["ENABLE_AUTHY"] == "1" ? true : false;

        if( $co->get('authy_enabled') != $to_enable ) {

            $db = Loader::db();

            //should we take over the single page?
            if($to_enable) {
                $args = array(
                    $pkg->getPackageID(), //package ID
                    Page::getByPath("/login")->getCollectionID()
                );
            } else {
                $args = array(
                    '0', //concrete's default
                    Page::getByPath("/login")->getCollectionID()
                );
            }

            //update a core singlepage
            $db->query("update Pages set pkgID = ? where cID = ?", $args );
        }

        //save new changes
        $co->save('authy_enabled', $_POST["ENABLE_AUTHY"] == "1" ? true : false);
        $co->save('authy_server_production', $_POST["AUTHY_SERVER"] == "1" ? true : false);
        $co->save('authy_api_key', $_POST["AUTHY_KEY"]);

        //redirect on success
        $this->redirect( "/dashboard/users/authy/success" );
    }


    public function debug() {

        $pkg = Package::getByHandle("c5authy");

        Loader::library('authy', $pkg);

        $authy = new Authy( "f45ec9af9dcb7419dc52b05889c858e9", false );

        $authy_id = $authy->getAuthyUserId( 'stefan@hammerti.me', '71142981', '45' );

        var_dump($authy_id);

        die("free");
    }
}