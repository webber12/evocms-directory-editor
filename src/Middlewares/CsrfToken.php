<?php
namespace EvolutionCMS\EvoDirectoryEditor\Middlewares;

use Closure;

class CsrfToken
{
    public function handle($request, Closure $next)
    {
        $access = $this->checkCSRF();
        if (!$access) {
            return response()->json(['status' => 'error', 'message' => 'access denied by csrf'], 403);
        } else {
            return $next($request);
        }
    }

    protected function checkCSRF()
    {
        $flag = false;
        $token = csrf_token();
        $_token = request()->input('_token', false);
        if (!empty($token) && !empty($_token) && $token == $_token) {
            $flag = true;
        } else {
            if (request()->hasHeader('X-CSRF-TOKEN')) {
                $_token = request()->header('X-CSRF-TOKEN');
                if (!empty($token) && !empty($_token) && $token == $_token) {
                    $flag = true;
                }
            }
        }
        return $flag;
    }
}
