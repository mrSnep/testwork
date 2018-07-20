<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Activation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ActivationCodeController;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function showRegistrationForm()
    {
        return redirect($this->redirectTo);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'g-recaptcha-response' => 'required|captcha'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        $validator = $this->validator($request->all());

        //Log::debug($validator->errors()->all());
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }



        $user = $this->create($request->all());
        //создаем код и записываем код
        $code = ActivationCodeController::generateCode(8);
        Activation::create([
            'user_id' => $user->id,
            'code' => $code,
        ]);
        //Генерируем ссылку и отправляем письмо на указанный адрес
        $url = url('/').'/activate/'.$code;
        Mail::send('auth.activation.registration', array('url' => $url), function($message) use ($request)
        {
            $message->to($request->email)
                ->subject('User Registration')
                ->from('apkens93@gmail.com', 'Test Sender');
        });

        return response()->json([
            'success' => true,
            'message' => 'Activation E-mail has been sended'
        ]);
    }

    public function activate($code){
        $check = Activation::where('code',$code)->first();
        if(!is_null($check)){
            $user = User::find($check->user_id);
            if ($user->activated == 1){
                return redirect()->to('login')->with('success',"user are already actived.");

            }
            $user->update(['activated' => 1]);
            Activation::where('code',$code)->delete();
            return redirect()->to('login')->with('success',"user active successfully.");
        }
        return redirect()->to('login')->with('Warning',"your token is invalid");
    }


}
