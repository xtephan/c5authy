<?php
/**
 * Dashboard config controller
 * c5authy
 * @author: Stefan Fodor
 * Built with love by Stefan Fodor @ 2014
 */
defined('C5_EXECUTE') or die("Access Denied.");

class DashboardUsersAuthyController extends DashboardBaseController {

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
            $this->error = t('API key cannot be null!');
        }

        //error msg
        if( $msg == "token_error" ) {
            $this->error = t('Invalid security token!');
        }

        //grab existing configuration
        $pkg = Package::getByHandle("c5authy");
        $co = new Config();
        $co->setPackageObject($pkg);

        //send saved config to view
        $this->set( 'authy_type', $co->get('authy_type') );
        $this->set( 'authy_server_production', $co->get('authy_server_production') );
        $this->set( 'authy_sms_tokens', $co->get('authy_sms_tokens') );
        $this->set( 'authy_api_key', $co->get('authy_api_key') );

    }

    /**
     * Task that updates Authy Configuration
     */
    public function update_config() {

        if ($this->token->validate("update_authy_config")) {
            if ($this->isPost()) {

                //we need a good api key
                $api_key = $this->post("AUTHY_KEY");
                if( empty( $api_key ) ) {
                    $this->redirect( "/dashboard/users/authy/key_error" );
                }

                //set options on package bases
                $pkg = Package::getByHandle("c5authy");
                $co = new Config();
                $co->setPackageObject($pkg);

                //save the config
                $co->save('authy_type', $this->post("AUTH_TYPE") );
                $co->save('authy_server_production', $this->post("AUTHY_SERVER") );
                $co->save('authy_sms_tokens', $this->post("AUTHY_SMS") );
                $co->save('authy_api_key', $this->post("AUTHY_KEY"));

                $this->redirect( "/dashboard/users/authy/success" );
            }
        } else {
            $this->redirect( "/dashboard/users/authy/token_error" );
        }
    }

}