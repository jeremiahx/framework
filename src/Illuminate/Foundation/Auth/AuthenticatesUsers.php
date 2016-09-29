<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Session;

trait AuthenticatesUsers
{
    use RedirectsUsers;

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
		
        return $this->showLoginForm();
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {  session_start();
		if(isset($_SESSION['email1'])):
			unset($_SESSION['email1']);
		endif;
		
        $view = property_exists($this, 'loginView')
                    ? $this->loginView : 'auth.authenticate';

        if (view()->exists($view)) {
            return view($view);
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        return $this->login($request);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);
		$input=$request->all();
		
		if(isset($input['employee_Id'])):
			$user_detail1_email = User::with('parent_user')->where('id',$input['employee_Id'])->first();	
			$request['email']=$user_detail1_email['email'];
			
			if($user_detail1_email['shop_parent_id']!='' && $user_detail1_email['shop_parent_id']!=0):
				$shop_parent_user=$user_detail1_email['parent_user']['email'];
			else:
				$shop_parent_user=$request['email'];
			endif;
		endif;

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);
		
		$user_login=Auth::validate(['email' =>$credentials['email'], 'personal_password' => $credentials['password'], 'status' => 1]);
		$user_login_username=Auth::validate(['name' =>$credentials['email'], 'personal_password' => $credentials['password'], 'status' => 1]);
		
		$user_login_right=Auth::validate(['email' =>$credentials['email'], 'password' => $credentials['password'], 'status' => 1]);
		$user_login_username_right=Auth::validate(['name' =>$credentials['email'], 'password' => $credentials['password'], 'status' => 1]);
		//print_r($user_login);exit;
		if(!isset($input['employee_Id'])):
		
			if ($user_login_right==1 || $user_login_username_right==1 || $user_login==1 || $user_login_username==1):
			
				if($user_login==1):
					$user_detail1 = User::where('email',$credentials['email'])->first();	
				else:
					$user_detail1 = User::where('name',$credentials['email'])->first();
				endif;
				
				$user_detail2 = User::where('email',$credentials['email'])->first();	
				$user_detail2_username = User::where('name',$credentials['email'])->first();
				
				if($user_login!=1 && $user_login_username!=1):
					if(isset($user_detail2) && $user_login_right==1 && $user_detail2['user_type']==1):
						return redirect()->back()
							->withErrors(['Sorry, These credentials do not match our records.'
							]);
					elseif(isset($user_detail2) && $user_login_username_right==1 && $user_detail2_username['user_type']==1):
						return redirect()->back()
							->withErrors(['Sorry, These credentials do not match our records.'
							]);
					endif;
				endif;
				
				if($user_detail1['user_type']==1 || $user_detail1['user_type']==4):
					if ($user_login==1 || $user_login_username==1):
						if(($user_detail1['user_type']==1 || $user_detail1['user_type']==4) && $user_detail1['status']==1):
							session::put('shop_id', $user_detail1['email']);
							return redirect('/second_login');
						elseif(($user_detail1['user_type']==1 || $user_detail1['user_type']==4) && $user_detail1['status']==0):
								return redirect()->back()
								->withErrors(['You are an inactive shop'
							]);
						elseif($user_detail1['user_type']==2):
							 return redirect()->back()
						->withErrors(['Sorry, you are an employee, you have to login with shop credentials.'
						]);
						endif;
					else:
							 return redirect()->back()
						->withErrors(['Sorry, These credentials do not match our records.'
						]);
					endif;
				elseif($user_detail1['user_type']==2 || $user_detail1['user_type']==1):
						 return redirect()->back()
						->withErrors(['Sorry, These credentials do not match our records.'
						]);
				endif;
			endif;
		else:
			session::put('shop_id', $shop_parent_user);
			$registrar_admin_id=User::where('email',$input['email'])->first();			
			session::put('registrar_admin_id',$registrar_admin_id['id']);
			session::put('shop^id',$input['email']);
			session::put('employee_email', $request['email']);
				if(($user_detail1_email['user_type']==2 || $user_detail1_email['user_type']==5) && $user_detail1_email['status']==0):
				
				 return redirect()->back()
					->withErrors(['Sorry, You are an inactive appraiser.'
					]);
			endif;
		endif;
		
		$user_detail1_email=User::where('email',$credentials['email'])->first();
		
		if(($user_detail1_email['user_type']==1 || $user_detail1_email['user_type']==4) && $user_detail1_email['status']==0):
			if($user_detail1_email['user_type']==1):
				return redirect()->back()
					->withErrors(['Sorry, You are an inactive Shop.'
				]);
            else:
				return redirect()->back()
					->withErrors(['Sorry, You are an inactive Register .'
				]);
			endif;
		endif;		
        if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
					$user_detail1 = User::where('email',$credentials['email'])->first();	
					if($user_detail1['user_type']==2 && $user_detail1['status']==1):
						return redirect('/shop/appraiser_portal');
					elseif($user_detail1['user_type']==3):
						return redirect('/customer/customer_portal');
					elseif($user_detail1['user_type']==1):
						return redirect('/shop/manage_appraisers');
					elseif($user_detail1['user_type']==4):						
						return redirect('/shopregistrar/shop_registrar_portal');
					elseif($user_detail1['user_type']==5):						
						return redirect('/registrar/registrar_portal');
					else:
						return redirect('/main_home');
					endif;
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles && ! $lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $throttles
     * @return \Illuminate\Http\Response
     */
    protected function handleUserWasAuthenticated(Request $request, $throttles)
    {
        if ($throttles) {
            $this->clearLoginAttempts($request);
        }

        if (method_exists($this, 'authenticated')) {
            return $this->authenticated($request, Auth::guard($this->getGuard())->user());
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only($this->loginUsername(), 'remember'))
            ->withErrors([
                $this->loginUsername() => $this->getFailedLoginMessage(),
            ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
                ? Lang::get('auth.failed')
                : 'These credentials do not match our records.';
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        return $request->only($this->loginUsername(), 'password');
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        return $this->logout();
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::guard($this->getGuard())->logout();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function loginUsername()
    {
        return property_exists($this, 'username') ? $this->username : 'email';
    }

    /**
     * Determine if the class is using the ThrottlesLogins trait.
     *
     * @return bool
     */
    protected function isUsingThrottlesLoginsTrait()
    {
        return in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : null;
    }
}
