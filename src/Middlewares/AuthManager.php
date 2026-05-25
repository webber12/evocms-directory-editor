<?php
namespace EvolutionCMS\EvoDirectoryEditor\Middlewares;

use Closure;

class AuthManager
{
    public function handle($request, Closure $next)
    {
        $access = $this->checkManager();
        if (!$access) {
            return response()->json(['status' => 'error', 'message' => 'access denied by auth manager'], 403);
        } else {
            return $next($request);
        }
    }

    protected function checkManager()
    {
        if (isset($_SESSION['mgrValidated']) && $_SESSION['usertype'] !== 'manager') {
            @session_destroy();
        }
        return isset($_SESSION['mgrValidated']) && $_SESSION['mgrValidated'] == 1;
    }
}
