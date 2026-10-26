<?php

namespace App\Services\Gateway;

use App\Models\ActiveSession;
use App\Models\Voucher;

class GatewayResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $errorCode = null,
        public readonly array $data = []
    ) {}

    public static function success(string $message, array $data = []): self
    {
        return new self(
            success: true,
            message: $message,
            errorCode: null,
            data: $data
        );
    }

    public static function failure(string $message, string $errorCode, array $data = []): self
    {
        return new self(
            success: false,
            message: $message,
            errorCode: $errorCode,
            data: $data
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getVoucher(): ?Voucher
    {
        return $this->data['voucher'] ?? null;
    }

    public function getSession(): ?ActiveSession
    {
        return $this->data['session'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'data' => $this->data,
        ];
    }
}
