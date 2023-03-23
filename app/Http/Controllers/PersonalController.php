<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PersonalController extends Controller
{
    public function reg(Request $request)
    {
        $errs = [];

        if (!$request->phone)
            $errs['phone'] = 'Поле телефон обязательное';

        // if (strlen(str_replace("+", "", $request->phone)) != 10)
        // $errs['phone'] = 'Телефон должен состоять из 10 цифр';
        if (User::where("phone", str_replace("+", "", $request->phone))->first())
            $errs['phone'] = 'Данный телефон уже кем-то используется';
        if (!preg_match("/^[1-9][0-9]{10}$/", str_replace("+", "", $request->phone)))
            $errs['phone'] = 'Не верный формат телефона';
        if (!preg_match('/^([а-яА-ЯЁё]+)$/u', $request->name))
            $errs['name'] = 'Имя только кириллица без цифр';
        if (!preg_match('/^([а-яА-ЯЁё]+)$/u', $request->surname))
            $errs['surname'] = 'Фамилия только кириллица без цифр';
        if (strlen($request->pass) <= 8)
            $errs['pass'] = 'Пароль минимум 8 любых символов';

        if ($errs) {
            return response(json_encode([
                'Status' => '403 forbidden',
                'Data' => $errs,
            ]), 403)->header("Content-type", "application/json");
        }

        $user = new User();
        $user->phone = str_replace("+", "", $request->phone);
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->password = $request->pass;
        $user->save();

        return response(json_encode([
            'Status' => '200 OK',
            'Data' => [
                'status' => 'OK'
            ],
        ]), 200)->header("Content-type", "application/json");
    }


    public function auth(Request $request)
    {
        // return ; 
        $errs = [];
        $phone = str_replace("+", "", $request->phone);
        if (!$phone)
            $errs['phone'] = 'Поле телефон обязательно для заполнения';
        if (!$request->pass)
            $errs['pass'] = 'Поле Пароль обязательно для заполнения';
        if ($errs) {

            if ($errs) {
                return response(json_encode([
                    'Status' => '403 forbidden',
                    'Data' => $errs,
                ]), 403)->header("Content-type", "application/json");
            }
        }
        // return User::where("password", '2')->get();
        if (Auth::attempt(['password' => $request->pass, 'phone' => $phone])) {
            $token = Auth::user()->id . date("H:i:s:u ", time());
            $token = sha1($token);
            $user = User::find(Auth::user()->id);
            $user->token = $token;
            $user->save();
            return response(json_encode([
                'Status' => '200 OK',
                'Data' => [
                    'status' => 'OK',
                    'token' => $token,
                ],
            ]), 200)->header("Content-type", "application/json");
        } else {
            return response(json_encode([
                'Status' => '403 forbidden',
                'Data' => [
                    'user' => 'Пользователь не найден'
                ],
            ]), 403)->header("Content-type", "application/json");
        }
    }


    public function katalog(Request $request)
    {
        $products = Product::select();
        // return $products->toJson();


        if ($request->has('kateg') && $request->kateg) $products->where("category", "LIKE",  "%{$request->kateg}%");
        if ($request->has('sort') && $request->sort) {
            switch ($request->sort) {
                case 'popul':
                    $products->orderBy("rating", "desc");
                    break;
                case 'cell_up':
                    $products->orderBy("price");
                    break;
                case 'cell_down':
                    $products->orderBy("price", "desc");
                    break;
                case 'new':
                    $products->orderBy("id", "desc");
                    break;

                default:
                    break;
            }
        }
        if ($request->has("range_from") && $request->range_from) $products->where("price", ">", $request->range_from);
        if ($request->has("range_to") && $request->range_to) $products->where("price", "<", $request->range_to);
        $products = $products->get();

        return response(json_encode([
            'Status' => '200 OK',
            'Data' => [
                'status' => 'OK',
                'products' => $products,
            ],
        ]), 200)->header("Content-type", "application/json");
    }
}
