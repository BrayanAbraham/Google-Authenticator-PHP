<?php
require_once('../vendor/autoload.php');

use Tuupola\Base32;
use \Com\Tecnick\Barcode\Barcode;

class Authenticator{

    private $base32;
    private $barcode;

    public function __construct(){
        $this->base32 = new Base32();
        $this->barcode = new Barcode();
    }

    private function generateSecret($length = 20) {
        $randomBuffer = openssl_random_pseudo_bytes($length);
        $base32Encoded = $this->base32->encode($randomBuffer);
        return str_replace('=','', $base32Encoded);
    }

    public function getQRCode($issuer, $username) {
        if(file_exists("../data/{$username}_secret.json")){
            $secret = json_decode(file_get_contents("../data/{$username}_secret.json"), true)[$username]['secret'];
        }
        if(!$secret){
            $secret = $this->generateSecret();
        }
        $url = "otpauth://totp/{$issuer}:{$username}?secret={$secret}&issuer={$issuer}";
        $image = $this->barcode->getBarcodeObj('QRCODE,H,AN,2', $url, -4, -4, 'black', array(-2, -2, -2, -2))->setBackgroundColor('#f0f0f0');
        $image = "data:image/png;base64," . base64_encode($image->getPngData());
        file_put_contents("../data/{$username}_secret.json", json_encode([$username => ['secret' => $secret]]));
        return $image;
    }

    public function verifyOTP($username, $totp) {
        $secret = json_decode(file_get_contents("../data/{$username}_secret.json"), true)[$username]['secret'];
        if(!$secret){
            return false;
        }
        return $this->verifyTOTP($totp, $secret);
    }

    private function verifyTOTP($token, $secret, $window = 1){
        // window = 10 means the totp from 5 mins ago is valid
        if(abs($window) > 10){
            return false;
        }

        for($errWindow = -$window; $errWindow <= $window; $errWindow++){
            $totp = $this->generateTOTP($secret, $errWindow);
            if($token === $totp){
                return true;
            }
        }

        return false;
    }

    private function generateTOTP($secret, $window = 0){
        $counter = floor(time()/30);
        return $this->generateHOTP($secret, $counter + $window);
    }

    private function generateHOTP($secret, $counter) {
        $decodedSecret = $this->base32->decode($secret);

        $counter = pack('N', $counter);
        $counter = str_pad($counter, 8, chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $counter, $decodedSecret, true);
        $offset = ord(substr($hash, -1)) & 0xF;

        $sub = substr($hash, $offset, $offset+4);
        $hashInt = unpack("N", $sub)[1];
        $truncatedHash = $hashInt & 0x7FFFFFFF;

        $code = str_pad($truncatedHash%(pow(10, 6)), 6, "0", STR_PAD_LEFT);
        return $code;
    }
}