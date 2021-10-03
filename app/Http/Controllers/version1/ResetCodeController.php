<?php

namespace App\Http\Controllers\version1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\version1\ResetCode;

class ResetCodeController extends Controller
{
    public function generate_resetcode()
    {
        $str = "";
		$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < 7; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
    }

    public function saveResetCode($investor_id, $thisresetcode)
    {
        $resetcode = new ResetCode();
        $resetcode->user_investor_id = $investor_id;
        $resetcode->resetcode = $thisresetcode;
        $resetcode->resetcode_used_status = false;
        $resetcode->save();

    }

    public function updateResetCode($thisresetcode_id, $user_type, $user_id, $thisresetcode, $used_status){

        $resetcode = ResetCode::find($thisresetcode_id);
        $resetcode->resetcode_id = $thisresetcode_id; 
        $resetcode->user_type = $user_type; 
        $resetcode->user_id = $user_id;
        $resetcode->resetcode = $thisresetcode;
        $resetcode->resetcode_used_status = $used_status;
        $resetcode->save();
    }
}
