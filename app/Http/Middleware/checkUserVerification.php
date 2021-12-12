<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Image\Traits\ApiResponseTrait;

class checkUserVerification
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        if(!auth()->user()->is_verified){
            return $this->failureResponse('Your Email not verified');
        }
        return $next($request);
    }
}
