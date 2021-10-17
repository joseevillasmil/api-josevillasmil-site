<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class History extends Model
{
	use SoftDeletes;
	
	protected $table ="history";
	
	protected $casts = [
        'checks' => 'array',
    ];
	
	function employee()
	{
		return $this->belongsTo('App\Models\Employee', 'employee_id');
	}
	
	

}