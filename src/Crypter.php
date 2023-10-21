<?php
/**
 * --------------------------------
 * # RSA 加密解密
 * --------------------------------
 * 1. zhang
 * 2. 2023-10-21
 * --------------------------------
 */
namespace Swordying;

class Crypter
{
    private $salt = '$=$.salt.$=$';
    private $public_key = '/public.key';
    private $private_key = '/private.key';
    private $key_path = __DIR__;
    public function __construct($config = [])
    {
        self::check();
        if(isset($confi['salt'])){
            $this -> salt = $config['salt'];
        }
        if(isset($confi['public_key'])){
            $this -> public_key = $config['public_key'];
            $this -> key_path = '';
        }
        if(isset($confi['private_key'])){
            $this -> private_key = $config['private_key'];
            $this -> key_path = '';
        }
    }
    public function handle(string $id_card_no = '') : array
    {
        return [
            'en' => $this -> en($id_card_no),
            'md' => $this -> md($id_card_no),
        ];
    }
    public function en(string $id_card_no = '') : string
    {
        $result = '';
        openssl_public_encrypt($id_card_no, $result, file_get_contents($this -> key_path . $this -> public_key));
        return base64_encode($result);
    }
    public function de(string $base64_encode = '') : string
    {
        $result = '';
        openssl_private_decrypt(base64_decode($base64_encode), $result, file_get_contents($this -> key_path . $this -> private_key));
        return $result;
    }
    public function md(string $id_card_no = '') : string
    {
        return md5($this -> salt . md5($id_card_no) . $this -> salt);
    }
    // 生成密钥对
    static function createKeys()
    {
        self::check();
        $config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $key_pair = openssl_pkey_new($config);
        // 提取私钥
        openssl_pkey_export($key_pair, $private_key);
        // 提取公钥
        $public_key = openssl_pkey_get_details($key_pair)['key'];
        return [
            'public_key' => $public_key,
            'private_key' => $private_key,
        ];
    }
    static function check()
    {
        if (!extension_loaded('openssl')) {
            echo '请导入 openssl 库';
            exit();
        }
    }
}
