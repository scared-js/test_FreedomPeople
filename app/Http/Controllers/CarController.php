<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\PivotUserCar;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CarController extends Controller
{
    public function search()
    {
        try {
            $request = request();
            $page = $request->input('page');

            $page_size = 15;
            $first_item = ($page - 1) * $page_size;
            $last_item = $first_item + $page_size;
            $query = Car::query();
            $query->with('pivot.user');

            $rows_all = $query->count();
            $query->skip($first_item);
            $query->take($page_size);
            $rows = $query->get();

            return self::send_success([
                'cars' => $rows,
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

    public function save(){
        try {
            DB::begintransaction();
            $request = request();
            $rules = [
                'name' => 'required',
            ];

            $data = [
                'name' => $request->input('name'),
            ];

            if ($request->input('id')) {
                $data['id'] = $request->input('id');
            }

            if ($error = self::validation($data, $rules)) {
                throw new Exception($error);
            }

            if ($request->input('id')) {
                $news = Car::where('id', $data['id'])->get();
                if (!$news->count()) {
                    throw new Exception('Автомобиль не найден');
                }
                $new = $news->first();
                $new->update($data);
                $new->save();
            } else {
                $new = Car::create($data);
                $new->save();
            }

            DB::commit();
            return self::send_success();
        } catch (Exception $error) {
            DB::rollback();
            return self::send_error($error->getMessage());
        }
    }

    public function delete(){
        try {
            DB::begintransaction();
            $request = request();

            $rules = [
                'id' => 'required|exists:cars,id',
            ];

            $data = [
                'id' => $request->input('id'),
            ];

            if ($error = self::validation($data, $rules)) {
                throw new Exception($error);
            }

            $row = Car::where('id', $data['id'])->get()->first();
            $row->delete();
            DB::commit();
            return self::send_success();
        } catch (Exception $error) {
            DB::rollback();
            return self::send_error($error->getMessage());
        }
    }

    public function assign(){
        try {
            DB::begintransaction();
            $request = request();
            $rules = [
                'user_id' => 'required|exists:users,id',
                'car_id' => 'required|exists:cars,id',
            ];

            $data = [
                'user_id' => Auth::user()->id,
                'car_id' => $request->input('car_id'),
            ];

            if ($error = self::validation($data, $rules)) {
                throw new Exception($error);
            }

            /** Проверяем есть ли у пользователя другие активные машины */
            if(PivotUserCar::where('user_id', $data['user_id'])->active()->get()->count()){
                throw new Exception('У вас уже есть арендованая машина');
            }

            /** Проверяем есть ли у машины другие активные пользователи */
            if(PivotUserCar::where('car_id', $data['car_id'])->active()->get()->count()){
                throw new Exception('У машины есть активные пользователи');
            }

            PivotUserCar::create($data);

            DB::commit();
            return self::send_success();
        } catch (Exception $error) {
            DB::rollback();
            return self::send_error($error->getMessage());
        }
    }
}
