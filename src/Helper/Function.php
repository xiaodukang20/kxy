<?php
/**
 * 业务函数
 */

use App\Constants\Business;
use App\Constants\Sec;

/**
 * JWT的登录信息
 * @param float|int $exp 过期时间
 * @return array
 */
function jwtLogin(float|int $exp = Sec::TOKEN_VALID_TIME): array
{
    return [
        'iss' => 'szjzkj',  //该JWT的签发者
        'iat' => time(),  //签发时间
        'exp' => time() + $exp,  //过期时间
        'nbf' => time() - 1,  //该时间之前不接收处理该Token
        'sub' => Business::DOMAIN,  //面向的用户
        'jti' => md5(uniqid('KXY') . time())  //该Token唯一标识
    ];
}