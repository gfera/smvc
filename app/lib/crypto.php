<?php

class Crypto {

    private static $private_key = '$apr1$yvBOLDaU$I2L9sRtBiMxc.Vm/yjgan/';
    private static $hash_base = 90000000000000;


    static public function encrypt($encrypt) {
        $crypt_key = Crypto::$private_key;
        $encode = utf8_encode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($crypt_key), $encrypt, MCRYPT_MODE_CBC, md5(md5($crypt_key)))));
        return strtr($encode, "+/=", "._-");
    }

    static public function decrypt($decrypt) {
        $crypt_key = Crypto::$private_key;
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($crypt_key), base64_decode(utf8_decode(strtr($decrypt, "._-", "+/="))), MCRYPT_MODE_CBC, md5(md5($crypt_key))), "\0");
        return $decrypted;
    }

    static public function hash($in) {
        $out = '';
        $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($index);
        $in+=self::$hash_base;
        for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
            $bcp = bcpow($base, $t);
            $a = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in = $in - ($a * $bcp);
        }

        return $out;
    }

    static public function unhash($in) {
        $out = '';
        $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($index);
        $len = strlen($in) - 1;

        for ($t = $len; $t >= 0; $t--) {
            $bcp = bcpow($base, $len - $t);
            $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
        }
        
        return doubleval($out)-self::$hash_base;;
    }

}

?>