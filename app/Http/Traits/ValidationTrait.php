<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Validator;

trait ValidationTrait
{
    static private $error_messages = [];

    static private $attribute_names = [];

    static protected function validation(array $data,array $rules)
    {
        $validator = Validator::make($data, $rules, self::$error_messages);
        $validator->setAttributeNames(self::$attribute_names);
        if($validator->fails()){
            return implode ("\n",$validator->errors()->all());
        }
    }
}
