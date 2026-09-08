<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    /**
     * Show the API documentation or lock screen.
     */
    public function index()
    {
        $serviceTypes = ServiceType::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ServiceType $serviceType) => [
                'label' => $serviceType->name,
                'value' => $serviceType->slug,
            ])
            ->values();

        return view('api-docs', compact('serviceTypes'));
    }

    /**
     * Show integration flow documentation or lock screen.
     */
    public function flows()
    {
        return view('api-flows');
    }

    /**
     * Authenticate the user for viewing API documentation with the password tazaka123.
     */
    public function auth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $redirectTo = $request->input('redirect_to');
        $hasSafeRedirect = is_string($redirectTo) && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//');

        if ($request->password === 'tazaka123') {
            session(['api_docs_authenticated' => true]);

            if ($hasSafeRedirect) {
                return redirect($redirectTo);
            }

            return redirect()->route('api-docs.index');
        }

        if ($hasSafeRedirect) {
            return redirect($redirectTo)->with('error', 'Kunci akses salah! Silakan coba lagi.');
        }

        return redirect()->route('api-docs.index')->with('error', 'Kunci akses salah! Silakan coba lagi.');
    }

    /**
     * Clear the API documentation session (lock the screen).
     */
    public function logout()
    {
        session()->forget('api_docs_authenticated');
        return redirect()->route('api-docs.index');
    }
}
