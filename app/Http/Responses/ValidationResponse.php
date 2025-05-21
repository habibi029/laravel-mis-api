<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;

class ValidationResponse implements Responsable
{

  public function __construct(protected MessageBag $validation, protected int $code = Response::HTTP_UNPROCESSABLE_ENTITY, protected array $headers = [], protected $cookie = null) { }

  public function toResponse($request)
  {
    $response = [];

    if ($this->validation != []) {
      $response['message'] = $this->validation->getMessages();
    }

    $json = response()->json($response, $this->code, $this->headers);

    if ($this->cookie) {
      $json->cookie($this->cookie);
    }

    return $json;
  }
}
