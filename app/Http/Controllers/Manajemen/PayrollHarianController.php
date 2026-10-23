<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollHarianController extends Controller
{
    public function index()
    {
        return view('manajemen.payroll_harian.index');
    }
}
