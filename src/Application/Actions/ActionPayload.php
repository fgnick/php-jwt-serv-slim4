<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionPayload implements JsonSerializable
{
    private int $statusCode;

    private $data;

    private ?ActionError $error;

    public function __construct(
        int $statusCode = 200,
        $data = null,
        ?ActionError $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->error = $error;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array|object|null
     */
    public function getData() 
    {
        return $this->data;
    }

    public function getError(): ?ActionError
    {
        return $this->error;
    }

    /**
     * [\ReturnTypeWillChange]
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'httpStatus' => $this->statusCode,
        ];
        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }
        return $payload;
    }
}
