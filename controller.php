<?php
/**
* Package controller
* c5authy
* @author: Stefan Fodor
* Built with love by Stefan Fodor @ 2014
*/

class C5authyPackage extends Package {

    //vars
    protected $pkgHandle 			= 'c5authy';
    protected $appVersionRequired	= '5.6.0';
    protected $pkgVersion 			= '0.9.0.1';

    /**
     * Package description
     *
     * @return string
     */
    public function getPackageDescription() {
        return t("Authy integration in C5 websites");
    }

    /**
     * Package Name
     *
     * @return string
     */
    public function getPackageName() {
        return t("C5 Authy");
    }

    /**
     * Package handle
     *
     * @return string
     */
    public function getPackageHandle(){
        return $this->pkgHandle;
    }

    /**
     * on start, hook up events for user added and user modified
     */
    public function on_start() {

        // deduce where the event handler file is
        $event_file =  dirname(__FILE__) . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "event_handler.php";

        //on user added
        Events::extend(
            'on_user_add',
            'EventHandler',
            'user_added',
            $event_file
        );

        //on user updated
        Events::extend(
            'on_user_update',
            'EventHandler',
            'user_updated',
            $event_file
        );

    }

    /**
     * Install the package
     */
    public function install() {

        //callback to parent for install
        $pkg = parent::install();
		
		//and lets configure this suckers
        $this->configurePackage($pkg);
    }

    /**
     * Upgrade the package
     *
     * @throws Exception
     */
    public function upgrade() {

        //callback to parent for heavy lifting
        $pkg = parent::upgrade();

        //and lets configure this suckers
        $this->configurePackage($pkg);
    }

    /**
     * Uninstall the package
     */
    public function uninstall() {

        //clean up after ourselfs
        $this->unconfigurePackage();

        //callback to parrent
        parent::uninstall();
    }

    /**
     * Unconfigure the package
     */
    protected function unconfigurePackage() {
        $this->unconfigureAttributes();
        $this->unconfigureSinglePages();
    }

    /**
     * Configure the package
     */
    protected function configurePackage( $pkg ) {
        $this->configureAttributes($pkg);
        $this->configureSinglePages($pkg);
        $this->configureOptions($pkg);
    }

    /**
     * Configure package options
     */
    protected function configureOptions( $pkg ) {

        //set options on package bases
        $pkg = Package::getByHandle("c5authy");
        $co = new Config();
        $co->setPackageObject($pkg);

        //set default options
        if( !$co->get('authy_type') ) {
            $co->save('authy_type', "0");
        }

        if( !$co->get('authy_sms_tokens') ) {
            $co->save( 'authy_sms_tokens', "2" );
        }

        if( !$co->get('authy_server_production') ) {
            $co->save('authy_server_production', "1");
        }

        if( !$co->get('authy_api_key') ) {
            $co->save('authy_api_key', 'loremipsum');
        }

    }

    /**
     * Configures single pages for the package
     */
    protected function configureSinglePages( $pkg ) {

        //load needed models
        Loader::model('single_page');

        //dashboard singlepage for configuration
        $sp = SinglePage::add('/dashboard/users/authy', $pkg);
        
		if ($sp) {
            $sp->update(array('cName'=> 'Authy Configuration'));
			$sp->setAttribute('icon_dashboard', "icon-cog");
        }
		
	}

    /**
     * Unconfigure SinglePages
     */
    protected function unconfigureSinglePages() {
        //load needed models
        Loader::model('single_page');

        //remove the config singlepage from dashboard
        $sp = Page::getByPath('/dashboard/users/authy');
        $sp->delete();

        //make sure C5 will use the default login page
        $db = Loader::db();
        $args = array(
            '0', //concrete's default
            Page::getByPath("/login")->getCollectionID() //login page ID
        );
        $db->query("update Pages set pkgID = ? where cID = ?", $args );

        //and make sure the single page from core has the correct name
        $path = getcwd() . DIRECTORY_SEPARATOR . "concrete" . DIRECTORY_SEPARATOR . "single_pages" . DIRECTORY_SEPARATOR;
        if( file_exists( $path . "login.php.bak" )  ) {
            rename( $path . "login.php.bak", $path . "login.php" );
        }
    }

    /**
     * Create the much needed user attributes
     */
    protected function configureAttributes( $pkg ) {

        //load needed models
        Loader::model('user_attributes');

        /*
         * Phone number user attribute
         */
        if( !is_object( UserAttributeKey::getByHandle('phone_number') ) ) {
            UserAttributeKey::add(
                'text',
                array(
                    'akHandle'                  => 'phone_number',
                    'akName'                    => t('Phone Number'),
                    'akIsSearchable'            => 1,
                    'akIsEditable'              => 1,
                    'uakProfileEdit'            => 1,
                    'uakProfileEditRequired'    => 1,
                    'uakRegisterEdit'           => 1,
                    'uakRegisterEditRequired'   => 1,
                    'uakProfileDisplay'         => 1
                ),
                $pkg
            );
        }

        /*
         * Country Code user attribute
         */
        if( !is_object( UserAttributeKey::getByHandle('phone_country_code') ) ) {
            $atr_phone_country_code = UserAttributeKey::add(
                'select',
                array(
                    'akHandle'                  => 'phone_country_code',
                    'akName'                    => t('Phone Country Code'),
                    'akIsSearchable'            => 1,
                    'akIsEditable'              => 1,
                    'uakProfileEdit'            => 1,
                    'uakProfileEditRequired'    => 1,
                    'uakRegisterEdit'           => 1,
                    'uakRegisterEditRequired'   => 1,
                    'uakProfileDisplay'         => 1
                ),
                $pkg
            );

            ///add the options
            $countryCodes = $this->getCountryCodes();

            foreach( $countryCodes as $thisCode ) {
                SelectAttributeTypeOption::add( $atr_phone_country_code, $thisCode );
            }

        }


        /*
         * Authy id user attribute
         */
        if( !is_object( UserAttributeKey::getByHandle('authy_user_id') ) ) {
            UserAttributeKey::add(
                'text',
                array(
                    'akHandle'                  => 'authy_user_id',
                    'akName'                    => t('Authy User ID'),
                    'akIsSearchable'            => 1,
                    'akIsEditable'              => 1,
                    'uakProfileEdit'            => 1,
                    'uakProfileEditRequired'    => 1,
                    'uakRegisterEdit'           => 0,
                    'uakRegisterEditRequired'   => 0,
                    'uakProfileDisplay'         => 1
                ),
                $pkg
            );
        }

    }

    /**
     * Removes the attributes that the code made
     */
    protected function unconfigureAttributes() {
        UserAttributeKey::getByHandle('phone_number')->delete();
        UserAttributeKey::getByHandle('phone_country_code')->delete();
        UserAttributeKey::getByHandle('authy_user_id')->delete();
    }

    /**
     * Returns an array full of country codes
     * @return array
     */
    private function getCountryCodes() {

        $result = array();

        $file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR  . "country_codes.csv";

        if (($handle = fopen($file_path, "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $result[] = sprintf("+%s (%s)", $data[1], $data[0]);
            }
            fclose($handle);
        }

        return $result;
    }
}