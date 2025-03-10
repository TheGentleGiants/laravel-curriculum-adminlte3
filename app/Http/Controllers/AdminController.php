<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(is_admin(), 403);

        return view('admin.show'); //
    }
}
