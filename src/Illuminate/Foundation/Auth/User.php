<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Cashier\Billable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword,Billable;
	
	public function user_details(){
		return $this->hasOne('App\Http\Models\UserDetails', 'users_id', 'id');
	}	
	public function user_template()
	{
		return $this->hasMany('App\Http\Models\UserTemplate', 'user_id', 'id');
	}
	
	public function customer_appraisers(){
		return $this->hasOne('App\Http\Models\CustomerAppraiser', 'customer_id', 'customer_id');
	}
	
	public function scopeByCustomerappraisers($query, $value){
		
			return $query->whereHas('customer_appraisers', function($query) use ($value){
				$query->where('appraiser_id',$value);				
			});
	}	
	
	 public function getCutCurrencyAttribute($value)
    {
    	$explode=explode('%',$this->currency);
    	if(isset($explode[0])): return $explode[0]; else: return ''; endif;
    }
	
	 public function getCutCurrency1Attribute($value)
    {
    	$explode=explode('%',$this->currency);
    	if(isset($explode[1])): return $explode[1]; else: return ''; endif;
    }
	
	public function customer_appraisals(){
		$logged_in=(Auth::user());
		$pids=array();
		
		if($logged_in->user_type==1):
			$logged_in=$logged_in->id;
		else:
			$logged_in=$logged_in->shop_parent_id;
		endif;
			
			$pid=User::select('id')->where('shop_parent_id',$logged_in)->get();
			
			foreach($pid as $id):
				$pids[]=$id->id;			
			endforeach;
		
		if(Auth::user()->user_type==0):	
			return $this->hasMany('App\Http\Models\Appraisals', 'customer_id', 'id')->where('draft', 1);
		else:
			return $this->hasMany('App\Http\Models\Appraisals', 'customer_id', 'id');
		endif;
	}	
	
	public function parent_user(){
		return $this->hasOne('Illuminate\Foundation\Auth\User', 'id', 'shop_parent_id');
	}
	public function registrar_name(){
		return $this->hasOne('App\Http\Models\CustomerAppraiser', 'appraiser_id','id');
	}
	public function employee_parent_user(){
		return $this->hasOne('Illuminate\Foundation\Auth\User', 'id', 'employee_parent_id');
	}
}
