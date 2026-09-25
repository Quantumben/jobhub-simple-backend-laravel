<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Cloudinary\Uploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public Companies
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): JsonResponse {

        $query = Company::query()
            ->where(
                'status',
                'active'
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {

            $search = trim(
                (string)
                $request->input(
                    'search'
                )
            );

            $query->where(
                function ($companyQuery) use ($search) {

                    $companyQuery
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Location
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'location'
            )
        ) {

            $location = trim(
                (string)
                $request->input(
                    'location'
                )
            );

            $query->where(
                'location',
                'like',
                "%{$location}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Count Active Jobs
        |--------------------------------------------------------------------------
        */

        $query->withCount([
            'jobs as active_jobs_count' => function ($jobQuery) {

                $jobQuery->where(
                    'status',
                    'active'
                );
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $companies = $query
            ->latest()
            ->paginate(12);

        return response()->json([

            'companies' => $companies->items(),

            'pagination' => [

                'current_page' => $companies->currentPage(),

                'last_page' => $companies->lastPage(),

                'per_page' => $companies->perPage(),

                'total' => $companies->total(),

            ],

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Company Details
    |--------------------------------------------------------------------------
    */

    public function show(
        Company $company
    ): JsonResponse {

        if (
            $company->status !==
            'active'
        ) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Active Jobs
        |--------------------------------------------------------------------------
        */

        $company->load([

            'jobs' => function ($query) {

                $query
                    ->where(
                        'status',
                        'active'
                    )
                    ->latest();
            },

        ]);

        $company->loadCount([

            'jobs as active_jobs_count' => function ($query) {

                $query->where(
                    'status',
                    'active'
                );
            },

        ]);

        return response()->json([
            'company' => $company,
        ]);
    }

    public function myCompanies(
        Request $request
    ): JsonResponse {

        $companies = $request
            ->user()
            ->companies()
            ->withCount('jobs')
            ->latest()
            ->get();

        return response()->json([
            'companies' => $companies,
        ]);
    }

    public function store(
        StoreCompanyRequest $request
    ): JsonResponse {

        $validated =
            $request->validated();

        /*
    |--------------------------------------------------------------------------
    | Remove UploadedFile Before Database Insert
    |--------------------------------------------------------------------------
    */

        unset(
            $validated['logo']
        );

        /*
    |--------------------------------------------------------------------------
    | Upload Logo
    |--------------------------------------------------------------------------
    */

        if (
            $request->hasFile(
                'logo'
            )
        ) {

            $uploadedLogo = Uploader::upload(
                $request->file('logo')->getRealPath(),
                [
                    'folder' => 'jobhub/companies',
                ]
            );

            $validated['logo'] =
                $uploadedLogo['secure_url'] ?? null;

            $validated['logo_public_id'] =
                $uploadedLogo['public_id'] ?? null;
        }

        /*
    |--------------------------------------------------------------------------
    | Create Through Logged-in User
    |--------------------------------------------------------------------------
    */

        $company = $request
            ->user()
            ->companies()
            ->create(
                $validated
            );

        /*
    |--------------------------------------------------------------------------
    | Job Count For React
    |--------------------------------------------------------------------------
    */

        $company->loadCount(
            'jobs'
        );

        return response()->json([

            'message' => 'Company created successfully.',

            'company' => $company,

        ], 201);
    }
}
