<?php
/**
 * 助手函数
 */

use App\Constants\Business;
use App\Constants\ErrorCode;
use App\Constants\Sec;
use App\Exception\BusinessException;
use App\MyCode\Abstracts\AbstractClasses;
use App\MyCode\Abstracts\AbstractFunc;
use App\MyCode\Abstracts\AbstractTimerClasses;
use App\Util\Email;
use App\Util\HuaxingSms;
use App\Util\Model;
use App\Util\MyHttp;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\View\RenderInterface;
use League\Flysystem\Filesystem;
use Psr\SimpleCache\CacheInterface;

function container(string $id = '')
{
    $container = ApplicationContext::getContainer();
    if (!$id) {
        return $container;
    }
    return $container->get($id);
}

function request(): RequestInterface
{
    return container(RequestInterface::class);
}

function response(): ResponseInterface
{
    return container(ResponseInterface::class);
}

function cache(): CacheInterface
{
    return container(CacheInterface::class);
}

function session(): SessionInterface
{
    return container(SessionInterface::class);
}

function redis(): Redis
{
    return container(Redis::class);
}

function render(): RenderInterface
{
    return container(RenderInterface::class);
}

function fileSystem(): Filesystem
{
    return container(Filesystem::class);
}

function consoleLog()
{
    return container(StdoutLoggerInterface::class);
}

function fileLog($name = 'kxy', $group = 'default')
{
    try {
        return container(LoggerFactory::class)->get($name, $group);
    } catch (\Psr\Container\NotFoundExceptionInterface | \Psr\Container\ContainerExceptionInterface $e) {
        error($e->getMessage());
    }
}

function userInfo(string $userField = ''): mixed
{
    $data = null;
    try {
        $info = session()->get(Sec::USER_INFO);
        if ($info) {
            $data = $info;
            if ($userField) {
                if (isset($info[$userField])) $data = $info[$userField];
                else $data = '';
            }
        }
    } catch (\Exception $e) {
        error('获取用户信息失败');
    }
    return $data;
}

function vdump(...$vars)
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

    $line = $trace[0]['line'];
    $pos = $trace[1]['class'] ?? $trace[0]['file'];
    if ($pos) echo "\n======  [" . times() . "] $pos ( $line 行 ) ======\n";
    ob_start();
    var_dump(...$vars);
    $string = ob_get_clean();
    echo preg_replace(["/Array[\s]*\(/", "/=>[\s]+/i"], ['Array (', ' => '], $string);
    echo "----- end -----\n\n";
}

function isDev(): bool
{
    return ($env = env('APP_ENV')) === 'dev' || $env === 'development';
}

function password($str): string
{
    return trim($str) === '' ? '' : password_hash($str, PASSWORD_DEFAULT, ['cost' => 6]);
}

function error(string $message = 'Error')
{
    throw new BusinessException(ErrorCode::SERVICE_UNAVAILABLE, $message);
}

function version()
{
    return config('version');
}

/**
 * 发送邮件
 * @param string|array $to 接收人
 * @param mixed $subject 邮件主题
 * @param mixed $msgHtml 邮件内容
 * @return bool
 */
function sendEmail(string|array $to, $subject = Business::EMAIL_CONFIG['defaultSubject'], $msgHtml = Business::EMAIL_CONFIG['defaultMsgHtml']): bool
{
    return (new Email())->send($to, $subject, $msgHtml);
}

/**
 * 发送短信
 * @param string $phone
 * @param string $templateId
 * @param string|array|null $data 为string,表示验证码; 为array|null,表示通知短信或推广短信
 * @return bool|int
 */
function sendSms(string $phone, string $templateId, string|array $data = null): bool|int
{
    isDev() && $phone = (string)Business::KXY_MOBILE;

    try {
        // 短信模板
        $conf = Business::SMS_TEMPLATE;
        if (!isset($conf[$templateId])) error('短信模板错误: ' . $templateId);

        // 短信内容
        $content = $conf[$templateId];
        $code = '';
        if ($data !== null) {
            if (is_string($data)) {
                $code = $data;
                $content = sprintf($content, $code);
            } else if (is_array($data)) {
                foreach ($data as $value) {
                    $content = sprintf($content, $value); # 循环替换模板里的变量
                }
            }
        }

        // 短信记录
        container(\App\Task\LogTask::class)->sms(au([
            'type' => $templateId,
            'code' => $code,
            'flag' => $flag = rand(1000, 9999),
            'mobile' => $phone,
            'content' => $content,
            'expireAt' => date('Y-m-d H:i:s', time() + 300),
            'createdAt' => times(),
        ]));

        // 开始发送
        $result = HuaxingSms::send($phone, $content);

        if ($result) return $flag;
        else return false;

    } catch (\Exception $e) {
        error($e->getMessage());
    }
}

/**
 * ModelTrait的简洁用法
 * @param string $model
 * @return Model
 */
function model(string $model): Model
{
    return (new Model($model));
}

/**
 * 实例化自定义类
 * @param string $className 类名
 * @param array $param 参数
 * @return mixed
 */
function myClass(string $className, array $param = []): object
{
    /** @var AbstractClasses $class */
    $class = Business::CODE_NS . "Classes\\" . $className;
    try {
        if (file_exists(BASE_PATH . '/' . Business::CODE_PATH . '/Classes/' . $className . '.php')) {
            return $class::getInstance()->_init($param);
        } else {
            throw new BusinessException(ErrorCode::CODE_FILE_NOT_FOUND, '不存在类文件 ' . $className);
        }
    } catch (\Exception $e) {
        throw new BusinessException(ErrorCode::CODE_CLASSES_ERROR, $e->getMessage());
    }
}

/**
 * 实例化定时类
 * @param string $className 类名
 * @return mixed
 */
function myTimerClass(string $className): object
{
    /** @var AbstractTimerClasses $class */
    $class = Business::CODE_NS . "TimerClasses\\" . $className;
    try {
        if (file_exists(BASE_PATH . '/' . Business::CODE_PATH . '/TimerClasses/' . $className . '.php')) {
            return $class::getInstance();
        } else {
            throw new BusinessException(ErrorCode::CODE_FILE_NOT_FOUND, '不存在定时类文件 ' . $className);
        }
    } catch (\Exception $e) {
        throw new BusinessException(ErrorCode::CODE_TIMER_CLASSES_ERROR, $e->getMessage());
    }
}

/**
 * 实例化自定义函数
 * @param string $funcId
 * @param ...$param
 * @return mixed
 */
function myFunc(string $funcId, ...$param): mixed
{
    /** @var AbstractFunc $func */
    $func = Business::CODE_NS . "Func\\" . $funcId;
    try {
        if (file_exists(BASE_PATH . '/' . Business::CODE_PATH . '/Func/' . $funcId . '.php')) {
            return $func::getInstance()->run(...$param);
        } else {
            throw new BusinessException(ErrorCode::CODE_FILE_NOT_FOUND, '不存在函数文件 ' . $funcId);
        }
    } catch (\Exception $e) {
        throw new BusinessException(ErrorCode::CODE_FUNC_ERROR, $e->getMessage());
    }
}

/**
 * 发送一个POST请求
 * @param string $url
 * @param array $param
 * @return mixed
 * @throws GuzzleException
 */
function httpPost(string $url, array $param = []): mixed
{
    return MyHttp::getInstance()->post($url, $param);
}