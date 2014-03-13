<?php
/**
* login controller
* c5authy
* @author: Stefan Fodor
* (C) Stefan Fodor @ 2014
*/

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Login controller
 * @author Stefan Fodor (stefan@unserialized.dk)
 */
class LoginController extends Concrete5_Controller_Login {

    /**
     * Do login. Check the token authenticity
     * and callback to parent for password verification
     */
    public function do_login() {

        $vs = Loader::helper('validation/strings');

        if ( !$vs->notempty($this->post('uToken')) ) {
            throw new Exception(t('A token is required.'));
        }

        /*
         * Validate token here
         */
        $valid_token = true;

        //if toke not valid, throw Exception
        if( $valid_token == false ) {
            throw new Exception(t('Token expired/invalid.'));
        }

        //let C5 do its magic to authenticate the user
        parent::do_login();
    }

}