<?php

namespace Hongdev\MasterAdmin\Http\Controllers;

use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    /**
     * Display the main settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('master-admin::master-admin.page.settings.index');
    }
}
