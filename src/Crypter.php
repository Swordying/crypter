<?php
/**
 * --------------------------------
 * # AES-GCM 加密解密
 * --------------------------------
 * 1. @author 乔木
 * 2. @date 2026-08-16
 * --------------------------------
 */
namespace Swordying;

class Crypter
{
    private string $salt = '#=#.salt.#=#';
    private string $method = 'AES-256-GCM';
    private string $key = '';
    private string $key_file = __DIR__ . '/aes.key';
    private int $iv_length = 12;
    private int $tag_length = 16;

    public function __construct($config = [])
    {
        self::check();
        if (isset($config['salt'])) {
            $this->salt = $config['salt'];
        }
        if (isset($config['method'])) {
            $this->method = $config['method'];
        }
        if (isset($config['key'])) {
            $this->key = $config['key'];
        }
        if (isset($config['key_file'])) {
            $this->key_file = $config['key_file'];
        }
    }

    /**
     * 加密并返回密文与散列值
     */
    public function handle(string $id_card_no = '') : array
    {
        return [
            'en' => $this->en($id_card_no),
            'md' => $this->md($id_card_no),
        ];
    }

    /**
     * AES-GCM 加密
     * 密文结构：base64( iv(12B) + tag(16B) + ciphertext )
     */
    public function en(string $id_card_no = '') : string
    {
        $iv = random_bytes($this->iv_length);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $id_card_no,
            $this->method,
            $this->getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            $this->tag_length
        );
        $payload = $iv . $tag . $ciphertext;
        return base64_encode($payload);
    }

    /**
     * AES-GCM 解密
     */
    public function de(string $base64_encode = '') : string
    {
        $payload = base64_decode($base64_encode);
        $iv = substr($payload, 0, $this->iv_length);
        $tag = substr($payload, $this->iv_length, $this->tag_length);
        $ciphertext = substr($payload, $this->iv_length + $this->tag_length);
        return openssl_decrypt(
            $ciphertext,
            $this->method,
            $this->getKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }

    /**
     * 散列值（用于数据库查询）
     */
    public function md(string $id_card_no = '') : string
    {
        return md5($this->salt . md5($id_card_no) . $this->salt);
    }

    /**
     * 获取密钥，自动适配 AES-128 / AES-256 长度
     */
    private function getKey() : string
    {
        if ($this->key !== '') {
            $raw = $this->key;
        } else {
            $raw = file_get_contents($this->key_file);
        }
        $length = $this->method === 'AES-128-GCM' ? 16 : 32;
        return substr(str_pad($raw, $length, "\0"), 0, $length);
    }

    /**
     * 生成 AES 密钥并写入文件
     * @param string $method AES-128-GCM | AES-256-GCM
     * @param string $file 保存路径，默认 ./src/aes.key
     * @return string 十六进制密钥
     */
    static function createKey(string $method = 'AES-256-GCM', string $file = '') : string
    {
        self::check();
        $length = $method === 'AES-128-GCM' ? 16 : 32;
        $key = random_bytes($length);
        $file = $file !== '' ? $file : __DIR__ . '/aes.key';
        file_put_contents($file, $key);
        return bin2hex($key);
    }

    static function check()
    {
        if (!extension_loaded('openssl')) {
            echo '请导入 openssl 库';
            exit();
        }
    }
}
