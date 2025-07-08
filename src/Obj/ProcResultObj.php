<?php

declare(strict_types=1);

namespace App\Obj;

use App\Obj\BaseProcResult;
use JsonSerializable;

class ProcResultObj extends BaseProcResult implements JsonSerializable
{
    /**
     * @var int $status
     * The status of the result, 1 for success, 0 for failure.
     * Default is 1, which indicates a successful operation.
     */
    private int $status = 1;

    /**
     * @var int $code
     * The result code, it maps to ProcResultInterface for the result code.
     * Default is PROC_OK (0), which indicates a successful operation.
     */
    private int $code;

    /**
     * @var array $data
     * The data returned from the operation, default is an empty array.
     */
    private array $data;

    /**
     * @var string $message
     * A message that provides additional information about the result.
     * Default is an empty string.
     */
    private string $message;

    /**
     * 
     * @param boolean $status
     * @param int $code
     * @param array $data
     * @param string $message
     */
    public function __construct(
        bool $status = true,
        int $code = BaseProcResult::PROC_OK,
        array $data = [], 
        string $message = ''
    ) {
        $this->status = $status ? 1 : 0;
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * for JsonSerializable interface
     * 
     * {@inheritdoc}
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'status'  => $this->status,
            'code'    => $this->code,
            'data'    => $this->data,
            'message' => $this->message
        ];
    }
}
