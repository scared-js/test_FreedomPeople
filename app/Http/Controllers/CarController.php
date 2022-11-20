<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Exception;

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
                'id' => 'required',
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

    }
}
