<?php
// app/Http/Controllers/Manajemen/KodeShiftController.php
namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Manajemen\MstKodeShift;

class KodeShiftController extends Controller
{
    public function index()
    {
        return view('manajemen.kode_shift.index', ['shifts' => MstKodeShift::orderBy('id')->get()]);
    }
}