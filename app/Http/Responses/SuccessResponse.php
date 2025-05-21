<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class SuccessResponse implements Responsable
{

  public function __construct(protected mixed $data = null, protected mixed $meta = null, protected int $code = Response::HTTP_OK, protected array $headers = [], protected $cookie = null, protected $message = null) { }

  public function toResponse($request)
  {
    $response = [];

    $response['data'] = $this->data;
    $response['meta'] = $this->meta;

    if ($this->message != null) {
      $response['meta']['message'] = $this->message;
    }

    $json = response()->json($response, $this->code, $this->headers);

    if ($this->cookie) {
      $json->cookie($this->cookie);
    }

    return $json;
  }
}
