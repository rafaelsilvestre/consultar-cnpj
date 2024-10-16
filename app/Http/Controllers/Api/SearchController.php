<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyCollection;
use App\Models\Company;
use App\Rules\ValidateCnpj;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * @param Request $request
     * @return CompanyCollection
     */
    public function allCompanies(Request $request)
    {
        $request->validate([
            'document' => [
                'nullable',
                new ValidateCnpj()
            ],
            'fantasy_name' => 'nullable|string|max:255',
            'main_activity' => 'nullable|string|max:255',
        ]);

        $query = Company::query();

        if ($document = $request->input('document')) {
            $query = $query->where('cnpj', $document);
        }

        if ($fantasyName = $request->input('fantasy_name')) {
            $query = $query->where('fantasy_name', 'LIKE', "%$fantasyName%");
        }

        if ($mainActivity = $request->input('main_activity')) {
            $query = $query->where('main_activity', $mainActivity);
        }

        $perPage = $request->query('per_page', 100);
        $page = $request->query('page', 1);

        $company = $query->paginate($perPage, ['*'], 'page', $page);

        if (! $company) {
            abort(404, 'Company not found');
        }

        return new CompanyCollection($company);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request)
    {
        $request->validate([
            'document' => [
                'required',
                new ValidateCnpj()
            ]
        ]);

        $query = Company::query();

        if ($document = $request->input('document')) {
            $query = $query->whereCnpj($document);
        }

        if ($fantasyName = $request->input('fantasy_name')) {
            $query = $query->whereFantasyName($fantasyName);
        }

        $company = $query->first();

        if (! $company) {
            abort(404, 'Company not found');
        }

        return response()->json($company);
    }
}
