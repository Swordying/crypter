# RSA 加密解密

## 1、解决需求
- 当诸如 身份证号、银行卡号 直接存入数据库就有泄漏的风险，所以需要非对称加密后存储。但非对成加密后的密文无法用于数据库查询，所以需要在数据表中增加两个字段，1密文字段、2散列字段，从而达到字符串非对成加密后，依然可以进行数据库查询。

## 2、composer 安装

- ` $ composer require swordying/crypter `

### 1. 加密解密
```php
// 引入类文件
require __DIR__.'/vendor/autoload.php';

# 实例化
$crypter = new \Swordying\Crypter();

# 明文
$ho = 'Hello World!';

# 加密后的值
$en = $crypter -> handle($ho);

# 散列值
$md = $crypter -> md($ho);

# 解密后的值
$de = $crypter -> de($en['en']);

var_export($ho);
echo "\n";
var_export($en);
echo "\n";
var_export($md);
echo "\n";
var_export($de);
```

### 2. 生成公钥私钥
```php
$keys = \Swordying\Crypter::createKeys();
var_export($keys);
// [
//     'public_key' => '',
//     'private_key' => '',
// ];
```

## 3、备注
1. 散列盐为：` $=$.salt.$=$ `
2. 公钥文件：` ./src/public.key `
3. 密钥文件：` ./src/private.key `
