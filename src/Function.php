<?php

declare(strict_types=1);

use Dsxwk\Framework\Annotation\Enums\ErrCodeEnum;
use Dsxwk\Framework\Annotation\Enums\Interface\ErrCodeInterface;
use Dsxwk\Framework\Utils\Exception\CodeException;
use Dsxwk\Framework\Utils\Query\Handle;
use Dsxwk\Framework\Utils\Trace\Trace;
use Dsxwk\Framework\WebmanHelper\Validate\Think\BaseRequest;
use support\Response;

// 公共函数
if (!function_exists('apiResponse')) {
    /**
     * 公共响应返回
     *
     * @param mixed                $data
     * @param ErrCodeInterface|int $code
     * @param string               $msg
     * @param int                  $httpCode
     *
     * @return Response
     */
    function apiResponse(mixed $data = [], ErrCodeInterface|int $code = 0, string $msg = '', int $httpCode = 200): Response
    {
        if (empty($msg)) $msg = 'success';

        if ($code instanceof ErrCodeInterface) {
            $codeOld = $code;
            $code    = $codeOld->getErrCode();
            $msg     = (!empty($msg) && $msg !== 'success') ? $msg : $codeOld->getErrMsg();
            unset($codeOld);
        }

        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        if (config('app.debug')) {
            $result['debug']['traceId'] = Trace::get();
            $result['debug']['mysql']   = Handle::getSqlRecord();
            $result['debug']['redis']   = Handle::getRedisRecord();
            Handle::clear();
        }

        $result = json_encode($result, JSON_UNESCAPED_UNICODE);

        return response($result)->withStatus($httpCode);
    }
}

if (!function_exists('jsonResponse')) {
    /**
     * json 响应
     *
     * @param array $data
     *
     * @return Response
     */
    function jsonResponse(array $data): Response
    {
        return response(json_encode($data, JSON_UNESCAPED_UNICODE))
            ->withHeaders(
                [
                    'Content-Type' => 'application/json;charset=utf-8',
                    'X-Trace-Id'   => Trace::get(),
                ]
            );
    }
}

if (!function_exists('throwError')) {
    /**
     * 抛出异常
     *
     * @param ErrCodeInterface|int $code
     * @param string               $msg
     * @param array                $data
     * @param                      $previous
     *
     * @return mixed
     * @throws CodeException
     */
    function throwError(ErrCodeInterface|int $code, string $msg = '', array $data = [], $previous = null): mixed
    {
        if ($previous instanceof CodeException) {
            throw $previous;
        }

        if ($code instanceof ErrCodeInterface) {
            $codeOld = $code;
            $code    = $codeOld->getErrCode();
            $msg     = !empty($msg) ? $msg : $codeOld->getErrMsg();
            unset($codeOld);
        }

        throw new CodeException($code, $msg, $data, $previous);
    }
}

if (!function_exists('validated')) {
    /**
     * think validate 公共验证
     *
     * @param $validateRequest
     *
     * @return array
     * @throws CodeException
     */
    function validated($validateRequest): array
    {
        /**
         * @var BaseRequest $validate
         */
        $validate = new $validateRequest;
        $result   = $validate->checked(request()->all());
        if (!$result) {
            throwError(ErrCodeEnum::PARAM_ERROR, $validate->getError());
        }

        return $result;
    }
}