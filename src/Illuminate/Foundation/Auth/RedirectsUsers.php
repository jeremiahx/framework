<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

trait RedirectsUsers
{
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (property_exists($this, 'redirectPath')) {
            return $this->redirectPath;
        }

		if(Auth::user()->user_type==1):
			return property_exists($this, 'redirectTo') ? $this->redirectTo : '/appraisal/manage_appraisals';
		else:
			return property_exists($this, 'redirectTo') ? $this->redirectTo : '/customer/customer_portal';
		endif;
		
    }
}
