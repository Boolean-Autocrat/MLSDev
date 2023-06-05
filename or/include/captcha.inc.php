<?php

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

/**
 * captcha
 * This class is abstraction layer class for recaptcha and secureimage
 */
class captcha
{
    public function show()
    {
        global $conn, $config, $misc, $lang, $jscript;
        $display = '';
        if ($config['captcha_system'] == 'securimage') {
            $builder = new CaptchaBuilder;
            $builder->build();
            $_SESSION['phrase'] = trim($builder->getPhrase());
            $display .= '<div class="captcha_outer"><div class="captcha_inner"><img src="' . $builder->inline() . '" /></div><input type="input" name="captcha_code" /></div>';
        } else {
            // new v2 reCaptcha
            // add following var to api url to force language,  it is supposed to auto-detect. ?hl='.$config["lang"].'
            $jscript .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

            $sql = 'SELECT controlpanel_recaptcha_sitekey 
				FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $sitekey = $recordSet->fields('controlpanel_recaptcha_sitekey');

            $display .= '<div class="captcha_outer">
						     <div class="g-recaptcha" data-sitekey="' . $sitekey . '"></div>
						</div>';
        }

        return $display;
    }

    public function validate()
    {
        global $conn, $config, $misc;

        $correct_code = false;

        if ($config['captcha_system'] == 'securimage') {
            if (isset($_POST['captcha_code']) && isset($_SESSION['phrase'])) {
                $builder = new CaptchaBuilder;
                if (PhraseBuilder::comparePhrases(strtolower($_SESSION['phrase']), strtolower($_POST['captcha_code']))) {
                    $correct_code = true;
                }
            }
        } else {
            //V2 reCaptcha
            if (isset($_POST['g-recaptcha-response'])) {
                $sql = 'SELECT controlpanel_recaptcha_secretkey 
								FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $secretkey = $recordSet->fields('controlpanel_recaptcha_secretkey');

                $data = [
                    'secret' => $secretkey,
                    'response' => $_POST['g-recaptcha-response'],
                    'remoteip' => $_SERVER['REMOTE_ADDR'],
                ];

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_VERBOSE, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 6);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl, CURLOPT_TIMEOUT, 60);

                $response = curl_exec($curl);

                $captcha_success = json_decode($response);

                curl_close($curl);
                if ($captcha_success->success == true) {
                    $correct_code = true;
                }
            }
        }
        //print_r($response);
        //echo $correct_code.'zz';
        return $correct_code;
    }
}
