<?php

namespace App\Http\Responses;

use App\Facades\LoggerService;
use App\Models\ErrorLog;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorResponse implements Responsable
{

  public function __construct(protected string $message = '', protected int $code = Response::HTTP_BAD_REQUEST, protected array $headers = [], protected mixed $e = null, protected? string $target_id = null, protected? string $module_function = null, protected? string $target_resource = null) {
    // LoggerService::storeErrorLog($message, $e, $target_resource, $target_id, $module_function, $code);
  }

  public function toResponse($request)
  {
    if ($this->message == null)
      $this->message = 'Something went wrong!';

    $response = [
      'data' => null,
      'meta' => [
        'message' => $this->message
      ]
    ];

    if ($this->e) {
      Log::error($this->e?->getMessage());
    }

    if ($this->e && config('app.debug')) {
        $response['debug'] = [ 'message' => $this->e->getMessage() ];
    }

    return response()->json($response, $this->code, $this->headers);
  }

  public function unauthorize()
  {
    return response()->json([
      'data' => null,
      'meta' => [
        'message' => 'Unauthorized access'
      ]
    ], Response::HTTP_UNAUTHORIZED, $this->headers);
  }
}
