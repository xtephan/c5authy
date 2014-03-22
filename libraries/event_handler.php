<?php
/**
 * Handle the user add and user modify events
 * c5authy
 * @author: Stefan Fodor
 * Built with love by Stefan Fodor @ 2014
 */

class EventHandler {

    /**
     * Handle user add by grabbing their Authy IDs
     * @param $ui
     */
    public function user_added( $ui ) {

        /*
         * So, C5 fires the on_user_add before it saved the attributes in DB
         * This sucks and requires to do a hack and rely on post data for Authy data
         */
        Loader::model('user_attributes');

        //get the phone number
        $phone_number_akID = UserAttributeKey::getByHandle('phone_number')->akID;
        $dirty_phone_number = $_POST["akID"][$phone_number_akID]["value"];

        //get email
        $email_addr = $_POST["uEmail"];

        //country code
        $phone_cc_akID = UserAttributeKey::getByHandle('phone_country_code')->akID;
        $country_code_selected_index = $_POST["akID"][$phone_cc_akID]["atSelectOptionID"][0];
        $full_country_code = SelectAttributeTypeOption::getByID($country_code_selected_index)->value;

        //request the update
        self::updateUserAuthy( $ui, $email_addr, $dirty_phone_number, $full_country_code );
    }

    /**
     * Handle user update by grabbing their new Authy IDs
     * @param $ui
     */
    public function user_updated( $ui ) {

        //fish out all the needed params
        $dirty_phone_number = $ui->getAttribute('phone_number');
        $full_country_code = (string)$ui->getAttribute('phone_country_code');
        $email_addr = $ui->getUserEmail();

        //and request the update
        self::updateUserAuthy( $ui, $email_addr, $dirty_phone_number, $full_country_code );
    }


    /**
     * Update the authy ID of the user
     *
     * @param $ui
     * @param $email_addr
     * @param $dirty_phone_number
     * @param $full_country_code
     */
    private static function updateUserAuthy( $ui, $email_addr, $dirty_phone_number, $full_country_code ) {

        //Allow only digits in the phone number
        $clean_phone_number = preg_replace("/[^0-9]/", "", $dirty_phone_number);
        if( $dirty_phone_number != $clean_phone_number ) {
            $ui->setAttribute('phone_number',$clean_phone_number);
        }

        //transform the country code in a format Authy likes
        list( $country_code, $junk ) = explode( ' ', $full_country_code );
        $country_code = ltrim( $country_code, '+' );

        //load the library
        $pkg = Package::getByHandle("c5authy");
        Loader::library('authy', $pkg);

        //init
        $authy = new Authy();

        //get the id
        $authy_id = $authy->getAuthyUserId(
            $email_addr,
            $clean_phone_number,
            $country_code
        );

        //and store it for rainny days
        $ui->setAttribute( 'authy_user_id', $authy_id );
    }

}