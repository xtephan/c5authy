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
    protected $appVersionRequired	= '5.6.2';
    protected $pkgVersion 			= '0.89.1';

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
     * on start, hook up events for user added and user modified
     */
    public function on_start() {

        //on user added
        Events::extend(
            'on_user_add',
            'C5authyPackage',
            'updateUserAuthy',
            __FILE__
        );

        //on user updated
        Events::extend(
            'on_user_update',
            'C5authyPackage',
            'updateUserAuthy',
            __FILE__
        );

    }

    /**
     * Install the package
     */
    public function install() {

        $pkg = $this;

        //callback to parent for install
        parent::install();

        //and lets configure this suckers
        $this->configurePackage($pkg);
    }

    /**
     * Upgrade the package
     */
    public function upgrade() {

        $pkg = $this;

        //callback to parent for heavy lifting
        parent::upgrade();

        //and lets configure this suckers
        $this->configurePackage($pkg);
    }

    /**
     * Uninstall the package
     */
    public function uninstall() {

        //callback to parrent
        parent::uninstall();

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
        if( !$co->get('authy_enabled') ) {
            $co->save('authy_enabled',false);
        }

        if( !$co->get('authy_server_production') ) {
            $co->save('authy_server_production',false);
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
        } else {
            $sp = Page::getByPath('/dashboard/users/authy');
        }
        $sp->setAttribute('icon_dashboard', "icon-cog");
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
            $countryCodes = array(
                "+45 (Denmark)"
            );

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
                    'uakRegisterEdit'           => 1,
                    'uakRegisterEditRequired'   => 1,
                    'uakProfileDisplay'         => 1
                ),
                $pkg
            );
        }

    }

    /**
     * Callback whenever a user was updated or udded
     * @param UI $ui
     */
    public function updateUserAuthy( $ui ) {

        //load the library
        $pkg = Package::getByHandle("c5authy");
        Loader::library('authy', $pkg);

        //init
        $authy = new Authy();

        //get the country code
        //it is saved in the atribute under the format '+xx yyyyyy'
        //hence the explode
        list( $country_code, $junk ) = explode( ' ', (string)$ui->getAttribute('phone_country_code') );

        //get the id
        $authy_id = $authy->getAuthyUserId(
            $ui->getUserEmail(),
            $ui->getAttribute('phone_number'),
            $country_code
        );

        //and store it for rainny days
        $ui->setAttribute( 'authy_user_id', $authy_id );
    }
}