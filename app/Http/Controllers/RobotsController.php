<?php

namespace App\Http\Controllers;

class RobotsController extends Controller
{
    public function index()
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /api',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }
}
