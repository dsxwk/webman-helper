<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Validate\Think;

use Dsxwk\Framework\TpHelper\Validate\BaseFormRequest;

abstract class BaseRequest extends BaseFormRequest
{
    /**
     * 获取当前场景
     *
     * @return string
     */
    protected function getAction(): string
    {
        return request()->action ?? '';
    }
}