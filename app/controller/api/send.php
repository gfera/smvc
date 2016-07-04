<?php

function enviarMail($to, $subject, $body) {

    App::loadLibrary("PHPMailer/class.phpmailer.php");

    $mail = new PHPMailer(true); //New instance, with exceptions enabled
    $mail->IsSMTP();                            // tell the class to use SMTP
    $mail->Host = "localhost";
    $mail->SMTPAuth = true;
    $mail->Username = 'no-reply@ofipel.com.ar';
    $mail->Password = 'V{+cVr-@UBsS';

    $mail->CharSet = "utf-8";
    $mail->From = "no-reply@ofipel.com.ar";
    $mail->FromName = "BasseGraf | Sitio Web";
    $mail->AddAddress($to);
    $mail->Subject = $subject;
    $mail->WordWrap = 80; // set word wrap	
    $mail->MsgHTML($body);
    $mail->SMTPDebug = false;
    $mail->IsHTML(true); // send as HTML

    try {
        $mail->Send();
    } catch (phpmailerException $e) {
        echo "*" . $e->errorMessage(); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        echo "**" . $e->getMessage(); //Boring error messages from anything else!
    }
}

$cuerpo = Template::loadViewHTML("email/contacto.php");

$cuerpo = str_replace("%AYN%", $_POST["o"]["nombre"], $cuerpo);
$cuerpo = str_replace("%EMAIL%", $_POST["o"]["email"], $cuerpo);
$cuerpo = str_replace("%TELEFONO%", $_POST["o"]["telefono"], $cuerpo);
$cuerpo = str_replace("%CELULAR%", $_POST["o"]["celular"], $cuerpo);
$cuerpo = str_replace("%RAZON%", $_POST["o"]["empresa"], $cuerpo);
$cuerpo = str_replace("%SECTOR%", $_POST["o"]["sector"], $cuerpo);
$cuerpo = str_replace("%COMENTARIO%", $_POST["o"]["comentario"], $cuerpo);
$cuerpo = str_replace("%IP%", $_SERVER['REMOTE_ADDR'], $cuerpo);

enviarMail("nykolasvs@gmail.com", "BASSEGRAF - Contacto", $cuerpo);

