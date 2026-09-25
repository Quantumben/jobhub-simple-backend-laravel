<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Application;
use Cloudinary\Uploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobController extends Controller
{
    public function store(StoreJobRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $imageUrl = null;

        $imagePublicId = null;

        if ($request->hasFile('image')) {

            $uploadedImage = Uploader::upload(
                $request->file('image')->getRealPath(),
                [
                    'folder' => 'jobhub/jobs',
                ]
            );

            $imageUrl = $uploadedImage['secure_url'] ?? null;

            $imagePublicId = $uploadedImage['public_id'] ?? null;
        }

        unset($validated['image']);

        $job = $request
            ->user()
            ->jobs()
            ->create([
                ...$validated,

                'image' => $imageUrl,

                'image_public_id' => $imagePublicId,
            ]);

        return response()->json([
            'message' => 'Job created successfully.',

            'job' => $job,
        ], 201);
    }

    public function myJobs(Request $request): JsonResponse
    {

        $jobs = $request
            ->user()
            ->jobs()
            ->latest()
            ->get();

        return response()->json([
            'jobs' => $jobs,
        ]);
    }

    public function myJob(
        Request $request,
        Application $job
    ): JsonResponse {

        if (
            $request->user()->id !==
            $job->user_id
        ) {
            abort(403);
        }

        return response()->json([
            'job' => $job,
        ]);
    }

    public function myJobID(
        Request $request,
        int $id
    ): JsonResponse {

        $job = $request
            ->user()
            ->jobs()
            ->whereKey($id)
            ->firstOrFail();

        return response()->json([
            'job' => $job,
        ]);
    }

    public function update(UpdateJobRequest $request, Application $job): JsonResponse
    {

        $validated = $request->validated();

        $oldImagePublicId = $job->image_public_id;

        $newImagePublicId = null;

        if ($request->hasFile('image')) {

            $uploadedImage = Uploader::upload(
                $request->file('image')->getRealPath(),
                [
                    'folder' => 'jobhub/jobs',
                ]
            );

            $validated['image'] =
                $uploadedImage['secure_url'] ?? null;

            $validated['image_public_id'] =
                $uploadedImage['public_id'] ?? null;

            $newImagePublicId =
                $validated['image_public_id'];
        }

        $job->update($validated);

        if (
            $newImagePublicId &&
            $oldImagePublicId
        ) {

            Uploader::destroy(
                $oldImagePublicId,
                [
                    'invalidate' => true,
                ]
            );
        }

        return response()->json([
            'message' => 'Job updated successfully.',

            'job' => $job->fresh(),
        ]);
    }

    public function destroy(Application $job): JsonResponse
    {
        $imagePublicId = $job->image_public_id;

        /*
    |--------------------------------------------------------------------------
    | Delete Job
    |--------------------------------------------------------------------------
    */

        $job->delete();

        /*
    |--------------------------------------------------------------------------
    | Delete Image From Cloudinary
    |--------------------------------------------------------------------------
    */

        if ($imagePublicId) {

            try {

                $result = Uploader::destroy(
                    $imagePublicId,
                    [
                        'resource_type' => 'image',
                        'invalidate' => true,
                    ]
                );

                Log::info('Cloudinary job image deleted.', [
                    'public_id' => $imagePublicId,
                    'result' => $result,
                ]);
            } catch (Throwable $error) {

                Log::warning('Cloudinary job image deletion failed.', [
                    'public_id' => $imagePublicId,
                    'error' => $error->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Job deleted successfully.',
        ]);
    }

    public function index(
        Request $request
    ): JsonResponse {

        $query = Application::query()
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

            $search =
                trim(
                    $request->string(
                        'search'
                    )
                );

            $query->where(
                function ($jobQuery) use ($search) {

                    $jobQuery
                        ->where(
                            'title',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'company',
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

            $location =
                trim(
                    $request->string(
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
    | Job Type
    |--------------------------------------------------------------------------
    */

        if (
            $request->filled(
                'job_type'
            )
        ) {

            $query->where(
                'job_type',
                $request->string(
                    'job_type'
                )
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Work Mode
    |--------------------------------------------------------------------------
    */

        if (
            $request->filled(
                'work_mode'
            )
        ) {

            $query->where(
                'work_mode',
                $request->string(
                    'work_mode'
                )
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

        $jobs = $query
            ->latest()
            ->paginate(9);

        return response()->json([

            'jobs' => $jobs->items(),

            'pagination' => [

                'current_page' => $jobs->currentPage(),

                'last_page' => $jobs->lastPage(),

                'per_page' => $jobs->perPage(),

                'total' => $jobs->total(),

            ],

        ]);
    }

    public function show(
        Application $job
    ): JsonResponse {

        if ($job->status !== 'active') {
            abort(404);
        }

        return response()->json([
            'job' => $job,
        ]);
    }
}
