<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;


class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth('api')->user();
        if ($user->role === User::role_admin) {
            return $next($request);
        } else {
            if ($request->isMethod('post')) {
                return response(['message' => 'Нет доступа!', 'success' => false]);
            } else {
                abort(403, 'Unauthorized action.');
            }
        }
    }
}