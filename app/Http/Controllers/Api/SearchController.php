<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'document' => 'required|min:14|max:14'
        ]);

        $document = $request->input('document');

        $company = Company::whereCnpj($document)
            ->firstOrFail();

        return response()->json($company);
    }
}
