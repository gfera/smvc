<?php

App::loadLibrary('PHPMailer/class.phpmailer.php');
App::loadLibrary('twig.php');

class MailNotification {

    static $last_error;

    public static function getLastError() {
        return self::$last_error;
    }

    /**
     * 
     * @return \PHPMailer
     */
    static public function getMailer() {
        $mail = new PHPMailer();
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->From = 'webmaster@funfitter.com';
        $mail->FromName = "FunFitter";
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true;  // authentication enabled
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->Username = 'webmaster@funfitter.com';
        $mail->Password = 'e3gwwUaAdLxp0arBmrF30A';

        return $mail;
    }

    static public function AdminNewFunfitter($ff_id) {
        $tg = new Twig();

        $data = array(
            "funfitter" => FfFunfitters::findById($ff_id),
            'uri' => "http://admin.funfitter.com/ffs_add.php?ffID=$ff_id"
        );

        $et = EmailTemplate::findByCode('new_funfitter_admin');

        $mlr = self::getMailer();
        $mlr->AddAddress('marcos@funfitter.com', "Marcos Dassen");
        $mlr->Subject = $et->et_subject;
        $mlr->Body = $tg->renderString($et->et_html, $data);
        $mlr->Send();
    }

    static public function NewUserEarlyBird(User $user, $extra) {
        $tg = new Twig();


        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';
        $sn = $user->us_login_type != User::LOGIN_TYPE_EMAIL;
        $mail_prefix = ($user->us_type == User::TYPE_FITTER ? 'funfitter' : 'user') . ($sn ? '-sn' : '') . "-$lang";
        $social_networks = array(
            "fb" => "Facebook",
            "li" => "LinkedIn",
            "gp" => "GooglePlus",
            "email" => ''
        );

        $data = array(
            "user" => $user,
            "extra" => $extra,
            "social_network" => $social_networks[$user->us_login_type]
        );


        $mlr = self::getMailer();
        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $lang == 'es' ? "Bienvenido a FunFitter" : "Welcome to FunFitter";
        $mlr->Body = $tg->getHTML("mail_template/earlybird/new_$mail_prefix.html", $data);
        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

    static public function NewUser(User $user, $extra) {
        $tg = new Twig();


        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';
        $sn = $user->us_login_type != User::LOGIN_TYPE_EMAIL;
        $mail_prefix = ($user->us_type == User::TYPE_FITTER ? 'funfitter' : 'user') . ($sn ? '-sn' : '') . "-$lang";
        $social_networks = array(
            "fb" => "Facebook",
            "li" => "LinkedIn",
            "gp" => "GooglePlus",
            "email" => ''
        );

        $data = array(
            "user" => $user,
            "extra" => $extra,
            "social_network" => $social_networks[$user->us_login_type]
        );


        $mlr = self::getMailer();
        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $lang == 'es' ? "Bienvenido a FunFitter" : "Welcome to FunFitter";
        $mlr->Body = $tg->getHTML("mail_template/user/new_$mail_prefix.html", $data);
        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

    static public function userNewItineraryVersion(FfFunfitters $ff, UserItineraryRequest $uir, UserItineraryVersion $uiv) {
        $user = User::findById($uir->us_id);
        if (!$user) {
            return false;
        }
    }

    static public function funfitterNewValuation(UserItineraryRequest $uir, Usuario $user) {
        $ff = FfFunfitters::findById($uir->ffID);
        if (!$ff)
            return;
    }

    static public function funfitterNewItineraryRequest(UserItineraryRequest $uir, TiposItinerarios $ti) {
        $ff = FfFunfitters::findById($uir->ffID);
        if (!$ff)
            return false;
        $user = User::findById($uir->us_id);
        $tg = new Twig();
        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';

        //// Mail para el usuario interesado
        $mlr = self::getMailer();
        $data = array(
            "user" => $user,
            "funfitter" => $ff,
            "uir" => $uir,
            "to" => "user",
            "ti" => $ti
        );
        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $lang == 'es' ? "Tu solicitud de itinerario" : "Your itinerary request";
        $mlr->Body = $tg->getHTML("mail_template/user/itinerary_request_" . $lang . ".html", $data);
        if (!$mlr->Send()) {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }


        //// Mail para el FunFitter
        $mlr = self::getMailer();
        $data = array_replace($data, array("to" => "ff"));
        $mlr->AddAddress($ff->email, $ff->name . " " . $ff->sur_name);
        $mlr->Subject = $lang == 'es' ? "Nueva solicitud de itinerario" : "New itinerary request";
        $mlr->Body = $tg->getHTML("mail_template/user/itinerary_request_" . $lang . ".html", $data);

        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

    static public function funfitterNewPayment(UserItineraryRequest $uir, $token) {

        $ff = FfFunfitters::findById($uir->ffID);
        if (!$ff)
            return false;

        $user = User::findById($uir->us_id);

        $tg = new Twig();
        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';

        $mlr = self::getMailer();

        $data = array(
            "user" => $user,
            "funfitter" => $ff,
            "uir" => $uir,
            "to" => "user"
        );

        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $lang == 'es' ? "Tu pago se realizó con exito" : "Your payment has been successfully processed";
        $mlr->Body = $tg->getHTML("mail_template/user/itinerary_request_payment_" . $lang . ".html", $data);

        if (!$mlr->Send()) {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }

        $mlr = self::getMailer();

        $data = array_replace($data, array("to" => "ff"));

        $mlr->AddAddress($ff->email, $ff->name . " " . $ff->sur_name);
        $mlr->Subject = $lang == 'es' ? "Han realizado el pago por tu itinerario " : "It has made payment for your itinerary";
        $mlr->Body = $tg->getHTML("mail_template/user/itinerary_request_payment_" . $lang . ".html", $data);

        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

    static public function userForgotPassword(User $user, $newPasswordString) {

        $tg = new Twig();
        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';

        $et = EmailTemplate::findByCode('forgot_password', $lang);

        $mlr = self::getMailer();
        $data = array(
            "user" => $user,
            "password" => $newPasswordString
        );

        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $et->et_subject;
        $mlr->Body = $tg->renderString($et->et_html, $data);

        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

    /* --------------------------------------------------------------------- */

    static public function UserDiscount(String $campaign, User $user, UserAdwordCampaign $uac) {

        $tg = new Twig();
        $lang = $user->us_lang_code == 'es' ? 'es' : 'en';

        $mlr = self::getMailer();

        $data = array(
            "user" => $user
        );

        $et = EmailTemplate::findByCode('adword_campaign_usagoogle', $lang);
        
        $mlr->AddAddress($user->us_email, $user->us_first_name . " " . $user->us_last_name);
        $mlr->Subject = $et->et_subject;
        $mlr->Body = $tg->renderString($et->et_html, $data);

        if ($mlr->Send()) {
            return true;
        } else {
            self::$last_error = $mlr->ErrorInfo;
            return false;
        }
    }

}
