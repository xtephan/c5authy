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

}