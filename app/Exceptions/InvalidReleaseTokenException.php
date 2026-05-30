<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidReleaseTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct('A valid release token is required to release this hold.');
    }

    public function render(): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'message' => 'INVALID_RELEASE_TOKEN',
                'code' => 403,
            ],
        ], 403);
    }
}