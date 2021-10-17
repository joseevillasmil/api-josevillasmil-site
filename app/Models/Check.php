<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Check extends Model
{
	use SoftDeletes;
	
	protected $table ="check";
	
	function employee()
	{
		return $this->belongsTo('App\Models\Employee', 'employee_id');
	}
	
	

}
