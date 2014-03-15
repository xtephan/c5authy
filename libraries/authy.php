<?php
/**
* Authy main library
* c5authy
* @author: Stefan Fodor
 * Built with love by Stefan Fodor @ 2014
*/

class Authy {

    //class wide variabled
    protected $api_key              = null;     //!< authy api key
    protected $server               = null;     //!< URL to RESTful server

    //addresses for authy servers
    const LIVE_SERVER       = "https://api.authy.com";
    const SANDBOX_SERVER    = "http://sandbox-api.authy.com";

    //various constants
    const FORMAT = "json"; //!< we dont want xml, do we?

    /**
     * Create a new object. Set the API key and the server
     *
     * @param $api_key
     * @param bool $production
     * @throws Exception
     */
    public function __construct( $api_key = null, $production =false ) {

        //load the data from config of is being passed to us?
        if( empty($api_key) ) {

            //get pachage and configuration
            $pkg = Package::getByHandle("c5authy");
            Loader::library('authy', $pkg);

            $co = new Config();
            $co->setPackageObject($pkg);

            $production = ( $co->get('authy_server_production') == "1" ? true : false );

            //set the values
            $this->api_key = $co->get('authy_api_key');
            $this->server = $production ? self::LIVE_SERVER : self::SANDBOX_SERVER;

        } else {
            //set the values
            $this->api_key = $api_key;
            $this->server = $production ? self::LIVE_SERVER : self::SANDBOX_SERVER;
        }

    }

    /**
     * Returns the Authy ID of a certain user
     *
     * @param string $email
     * @param string $phone_number
     * @param string $country_code
     */
    public function getAuthyUserId( $email, $phone_number, $country_code ) {

        //sanity checks
        if( empty( $email ) ) {
            throw new Exception( t('Invalid email address') );
        }

        if( empty( $phone_number ) ) {
            throw new Exception( t('Invalid phone number') );
        }

        if( empty( $country_code ) ) {
            throw new Exception( t('Invalid country code') );
        }

        //build the payload
        $payload = http_build_query( array(
            'user[email]'           => $email,
            'user[cellphone]'       => $phone_number,
            'user[country_code]'    => $country_code
        ));

        //and ask authy
        $got = $this->req( '/users/new', $payload , true );

        //are we ok?
        if( $got->success == false ) {
            throw new Exception( t('Authy error when creating/updating a user') );
        }

        //if ok, safe navigate to user id
        if( $got->success == true ) {
            if( is_object( $got->user ) ) {
                return $got->user->id;
            }
        }

        //one should not find itself here
        throw new Exception( t('Unexpected Authy reponse') );
    }

    /**
     * Validates a given token
     *
     * @param $token
     * @param $auth_id
     * @return boolean
     * @throws Exception
     */
    public function validToken( $token, $auth_id ) {

        if( empty($token) ) {
            throw new Exception( t('Invalid token!') );
        }

        if( empty($auth_id) ) {
            throw new Exception( t('Invalid authy ID!') );
        }

        return false;
    }

    /**
     * Sends a request to Authy servers and resturns the response decoded
     *
     * @param string $path
     * @param array $payload
     * @param bool $post
     * @return mixed
     */
    private function req( $path, $payload, $post = false ) {

        //compose the req url
        $url = sprintf( '%s/protected/%s%s?api_key=%s', $this->server, self::FORMAT, $path, $this->api_key );

        //curl handler
        $ch = curl_init();

        //url to curl
        curl_setopt($ch, CURLOPT_URL, $url);

        //receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // post data
        if( $post ) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload );
        }

        //ruun
        $server_output = curl_exec($ch);

        //always close the sockets
        curl_close ($ch);

        //perfect
        return json_decode( $server_output );
    }

}