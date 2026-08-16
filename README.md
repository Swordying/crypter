# AES-GCM 加密解密

## 1、解决需求
- 当诸如 身份证号、银行卡号 直接存入数据库就有泄漏的风险，所以需要加密后存储。但加密后的密文无法用于数据库查询，所以需要在数据表中增加两个字段，1 密文字段、2 散列字段，从而达到字符串加密后，依然可以进行数据库查询。
- 采用 AES-GCM 对称加密算法（认证加密），既能保证密文机密性，又能通过认证标签（Tag）校验密文完整性，防止密文被篡改。
- 支持配置 `AES-128-GCM` 与 `AES-256-GCM` 两种加密强度，按需选用。

## 2、composer 安装

- ` $ composer require swordying/crypter `

## 3、加密解密

```php
## 引入类文件
require __DIR__.'/vendor/autoload.php';

## 实例化（默认 AES-256-GCM）
$crypter = new \Swordying\Crypter();

## 明文
$ho = 'Hello World!';

## 加密后的值（包含 iv + tag + ciphertext 的 base64 串）
$en = $crypter -> handle($ho);

## 散列值（用于数据库等值查询）
$md = $crypter -> md($ho);

## 解密后的值
$de = $crypter -> de($en['en']);

var_export($ho);
echo "\n";
var_export($en);
echo "\n";
var_export($md);
echo "\n";
var_export($de);
```

## 4、配置加密方法与密钥

通过构造函数传入配置数组，可自定义加密方法、密钥、密钥文件路径及散列盐值。

```php
$crypter = new \Swordying\Crypter([
    ## 加密方法：AES-128-GCM | AES-256-GCM（默认）
    'method'    => 'AES-256-GCM',
    ## 密钥（字符串，不传则从 key_file 读取）
    ## 注意：AES-128-GCM 需要 16 字节，AES-256-GCM 需要 32 字节
    ## 不足自动以 \0 填充，超出自动截断
    'key'       => '',
    ## 密钥文件路径（默认 ./src/aes.key）
    'key_file'  => __DIR__.'/aes.key',
    ## 散列盐值
    'salt'      => '#=#.salt.#=#',
]);
```

## 5、生成密钥

```php
## 生成 AES-256-GCM 密钥（32 字节），默认写入 ./src/aes.key
$key256 = \Swordying\Crypter::createKey();
var_export($key256); ## 十六进制字符串

## 生成 AES-128-GCM 密钥（16 字节）
$key128 = \Swordying\Crypter::createKey('AES-128-GCM');
var_export($key128);

## 指定保存路径
\Swordying\Crypter::createKey('AES-256-GCM', '/path/to/your.key');
```

## 6、密文结构

`en()` 返回的密文为 base64 编码的原始二进制，结构如下：

```
base64( iv(12 字节) + tag(16 字节) + ciphertext )
```

- `iv`：每次加密随机生成，确保相同明文产生不同密文。
- `tag`：GCM 认证标签，解密时用于校验密文完整性。
- `ciphertext`：AES-GCM 加密后的密文。

## 7、备注
1. 散列盐为：` $=$.salt.$=$ `
2. 默认密钥文件：` ./src/aes.key `（请妥善保管，切勿提交到公开仓库）
3. 默认加密方法：` AES-256-GCM `
4. 依赖 PHP 的 `openssl` 扩展
