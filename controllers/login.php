<?php
/**
* login controller
* c5authy
* @author: Stefan Fodor
 * Built with love by Stefan Fodor @ 2014
*/

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Login controller
 * @author Stefan Fodor (stefan@unserialized.dk)
 */
class LoginController extends Concrete5_Controller_Login {

    /**
     * Do login.
     */
    public function do_login() {

        $ip = Loader::helper('validation/ip');
        $vs = Loader::helper('validation/strings');

        $loginData['success']=0;

        try {
            if(!$_COOKIE[SESSION]) {
                throw new Exception(t('Your browser\'s cookie functionality is turned off. Please turn it on.'));
            }

            if (!$ip->check()) {
                throw new Exception($ip->getErrorMessage());
            }

            if ( (!$vs->notempty($this->post('uName'))) || (!$vs->notempty($this->post('uPassword')))) {
                if (USER_REGISTRATION_WITH_EMAIL_ADDRESS) {
                    throw new Exception(t('An email address and password are required.'));
                } else {
                    throw new Exception(t('A username and password are required.'));
                }
            }

            if ( !$vs->notempty($this->post('uToken')) ) {
                throw new Exception(t('A token is required.'));
            }

            $u = new User($this->post('uName'), $this->post('uPassword'));

            if ($u->isError()) {
                switch($u->getError()) {
                    case USER_NON_VALIDATED:
                        throw new Exception(t('This account has not yet been validated. Please check the email associated with this account and follow the link it contains.'));
                        break;
                    case USER_INVALID:
                        if (USER_REGISTRATION_WITH_EMAIL_ADDRESS) {
                            throw new Exception(t('Invalid email address or password.'));
                        } else {
                            throw new Exception(t('Invalid username or password.'));
                        }
                        break;
                    case USER_INACTIVE:
                        throw new Exception(t('This user is inactive. Please contact us regarding this account.'));
                        break;
                }
            } else {

                //Start verifing the token
                //load the library
                $pkg = Package::getByHandle("c5authy");
                Loader::library('authy', $pkg);

                //UI
                $ui = UserInfo::getByID( $u->getUserID() );

                //authy
                $authy = new Authy();

                if( ! $authy->validToken( $this->post('uToken'), $ui->getAttribute('authy_user_id') ) ) {
                    $loginData['msg']=t('Invalid token.');
                    throw new Exception(t('Invalid token.'));
                }

                $loginData['success']=1;
                $loginData['msg']=t('Login Successful');
                $loginData['uID'] = intval($u->getUserID());
            }

            $loginData = $this->finishLogin($loginData);

        } catch(Exception $e) {
            $ip->logSignupRequest();
            if ($ip->signupRequestThreshholdReached()) {
                $ip->createIPBan();
            }
            $this->error->add($e);
            $loginData['error']=$e->getMessage();
        }

        if ($_REQUEST['format']=='JSON') {
            $jsonHelper=Loader::helper('json');
            echo $jsonHelper->encode($loginData);
            die;
        }
    }

    /**
     * Make a request to send an SMS token to a user
     */
    public function request_sms() {

        //sanity check
        if( !$this->isPost() ) {
            throw new Exception( t('Invalid call.') );
        }

        //get and parse the phone number
        $phone = $this->post('phone');


        //remove and non valid chars
        $phone = preg_replace("/[^0-9]/", "", $phone);

        //remove leading country code 00es
        if( substr($phone,0,2) == "00" ) {
            $phone = substr($phone,2);
        }

        //last sanity check after parsing
        if( empty($phone) ) {
            echo json_encode( array( "status" => "FAIL", "msg" => t("Invalid phone number") ) );
            exit();
        }

        //find the user with the phone number
        Loader::model('user_list');
        $ul = new UserList();

        //filter by the phone number
        $ul->filterByAttribute('phone_number', $phone);

        //and get the first and only result
        $users = $ul->get(1);

        //no users found, maybe he added the prefix to the phone number
        if( count($users) == 0 ) {

            //adjust the phone
            $phone = substr($phone,2);

            //and try a new search
            $ul = new UserList();
            $ul->filterByAttribute('phone_number', $phone);
            $users = $ul->get(1);
        }

        //if still no result, we dont have this number in DB
        if( count($users) == 0 ) {
            echo json_encode( array( "status" => "FAIL", "msg" => t("Non existing phone number") ) );
            exit();
        }

        $ui = UserInfo::getByID( $users[0]->getUserID() );

        //Last sanity check, i promisse
        $authy_id = $ui->getAttribute('authy_user_id');
        if( empty($authy_id) ) {
            echo json_encode( array( "status" => "FAIL", "msg" => t("Invalid Authy Account") ) );
            exit();
        }


        //load the library
        $pkg = Package::getByHandle("c5authy");
        Loader::library('authy', $pkg);
        $authy = new Authy();

        //request the SMS
        if( $authy->requestSMS( $authy_id ) ) {
            //report that everything is ok
            echo json_encode( array( "status" => "OK" ) );
        } else {
            //small problem
            echo json_encode( array( "status" => "FAIL", "msg" => "Error while requesting SMS token" ) );
        }

        //end exit
        exit();
    }

}