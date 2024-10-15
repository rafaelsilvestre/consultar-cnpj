<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\ValidateCnpj;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'document' => [
                'required',
                new ValidateCnpj()
            ]
        ]);

        $document = $request->input('document');

        $company = Company::whereCnpj($document)
            ->first();

        if (! $company) {
            abort(404, 'Company not found');
        }

        return response()->json($company);
    }
}
