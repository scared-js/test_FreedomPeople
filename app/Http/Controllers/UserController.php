<?php

namespace App\Http\Controllers;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login()
    {
        try {
            $request = request();

            $rules = [
                'email' => 'required|email|exists:users,email',
                'password' => 'required',
            ];

            $data = [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ];


            if ($error = self::validation($data, $rules)) {
                throw new Exception($error);
            }

            if (Auth::attempt($data)) {
                $user = Auth::user();
                $token = Str::random(80);
                $user->api_token = hash('sha256', $token);
                $user->save();
                return self::send_success(['token' => $token]);
            } else {
                return self::send_error('Пароль или почта неверная');
            }
        } catch (Exception $error) {
            return self::send_error($error->getMessage());
        }
    }

    public function save(){
        try {
            DB::begintransaction();
            $request = request();
            $rules = [
                'name' => 'required|string',
            ];

            $data = [
                'name' => $request->input('name'),
            ];

            if ($error = self::validation($data, $rules)) {
                throw new Exception($error);
            }

            $user = Auth()->user();
            $user->update($data);
            $user->save();

            DB::commit();
            return self::send_success();
        } catch (Exception $error) {
            DB::rollback();
            return self::send_error($error->getMessage());
        }
    }

    public function search(){
        try {
            $request = request();
            $page = $request->input('page');

            $page_size = 15;
            $first_item = ($page - 1) * $page_size;
            $last_item = $first_item + $page_size;
            $query = User::query();

            $rows_all = $query->count();
            $query->skip($first_item);
            $query->take($page_size);
            $rows = $query->get();

            return self::send_success([
                'users' => $rows,
                'page' => $page,
                'count_all' => $rows_all,
                'first_item' => $first_item + 1,
                'last_item' => $last_item,
                'last_page' => ceil(($rows_all ? $rows_all : 1) / $page_size),
            ]);
        } catch (Exception $error) {
            return self::send_error($error->getMessage());
        }
    }
}
