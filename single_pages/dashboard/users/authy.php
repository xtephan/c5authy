<?php
/**
 * Dashboard config view
 * c5authy
 * @author: Stefan Fodor
 * Built with love by Stefan Fodor @ 2014
 */
defined('C5_EXECUTE') or die("Access Denied.");

//Set the header and dashboard theme
$title="Authy Configuration";
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($title, false, 'span10 offset1', false);
?>

<form method="post" class="form-horizontal" action="<?php  echo $this->action('update_config') ?>">
    <div class="ccm-pane-body">

        <fieldset>
            <legend style="margin-bottom: 0px"><?php  echo t('2 Factor Authentication')?></legend>
            <div class="control-group">
                <div class="controls">
                    <label class="radio">
                        <input type="radio" name="ENABLE_AUTHY" value="0" <?php if(!$authy_enabled) echo "checked"; ?> />
                        <span><?php  echo t('Off - default login system')?></span>
                    </label>
                </div>
                <div class="controls">
                    <label class="radio">
                        <input type="radio" name="ENABLE_AUTHY" value="1" <?php if($authy_enabled) echo "checked"; ?> />
                        <span><?php  echo t('On - required user to enter an Authy OTP')?></span>
                    </label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend style="margin-bottom: 0px"><?php  echo t('Authy servers')?></legend>
            <div class="control-group">
                <div class="controls">
                    <label class="radio">
                        <input type="radio" name="AUTHY_SERVER" value="0" <?php if(!$authy_server_production) echo "checked"; ?>/>
                        <span><?php  echo t('Sandbox - Good for development.')?></span>
                    </label>
                </div>
                <div class="controls">
                    <label class="radio">
                        <input type="radio" name="AUTHY_SERVER" value="1"  <?php if($authy_server_production) echo "checked"; ?> />
                        <span><?php  echo t('Production - Required for live sites.')?></span>
                    </label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend style="margin-bottom: 0px"><?php  echo t('Authy API Key')?></legend>
            <div class="control-group">
                <div class="controls">
                    <input type="text" name="AUTHY_KEY" value="<?php echo $authy_api_key ?>" style="height: 20px; width: 250px;"/>
                </div>
            </div>

        </fieldset>
    </div>
    <div class="ccm-pane-footer">
        <input type="submit" class="btn ccm-button-v2 primary ccm-button-v2-right" value="<?php echo t('Save'); ?>">
    </div>
</form>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>

