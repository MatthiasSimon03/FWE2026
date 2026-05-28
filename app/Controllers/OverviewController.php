<?php

namespace App\Controllers;

class OverviewController extends BaseController {
    public function index(){
        return view('overview');
    }
}