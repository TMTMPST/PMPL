<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\m_fasilitas as Fasilitas;
use OpenApi\Attributes as OA;

class KelolaFasilitas extends Controller
{
    #[OA\Schema(
        schema: 'KelolaFasilitasRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'search', type: 'string', example: '')
        ]
    )]

    #[OA\Schema(
        schema: 'KelolaFasilitasCreateRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'fasilitas_nama', type: 'string', example: ''),
        ]
    )]

    #[OA\Schema(
        schema: 'KelolaFasilitasUpdateRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'fasilitas_nama', type: 'string', example: ''),
        ],
    )]

    #[OA\Post(
        path: '/api/kelola-fasilitas',
        summary: 'Get list of fasilitas',
        description: 'Get list of fasilitas with pagination and search',
        tags: ['Kelola Fasilitas'],
        security: [['cookieAuth' => ['jwtToken']]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/KelolaFasilitasRequest')
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Data berhasil diambil',
                content: new OA\JsonContent(
                    example: [
                        'success' => true,
                        'message' => 'Data berhasil diambil',
                        'data' => 'data'
                    ]
                )
            ),
            new OA\Response(
                response: '401',
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Unauthorized'
                    ]
                )
            ),
            new OA\Response(
                response: '500',
                description: 'Internal Server Error',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Internal Server Error'
                    ]
                )
            )
        ]
    )]


    public function list(Request $request)
    {
        $query = Fasilitas::query();
        $dataTotal = $query->count();

        // Filter berdasarkan nama
        if ($request->filled('fasilitas_nama')) {
            $query->where('fasilitas_nama', $request->fasilitas_nama);
        }

        // Search global
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where('fasilitas_nama', 'like', '%' . $searchValue . '%');
        }

        $dataFiltered = $query->count();
        $query->offset($request->input('start', 0));
        $query->limit($request->input('length', 10));

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $dataTotal,
            'recordsFiltered' => $dataFiltered,
            'data' => $data
        ]);
    }


    #[OA\Get(
        path: '/api/kelola-fasilitas/{id}',
        summary: 'Get fasilitas detail',
        description: 'Get fasilitas detail',
        tags: ['Kelola Fasilitas'],
        security: [['cookieAuth' => ['jwtToken']]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Ruangan Role ID'
            )
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Data berhasil diambil',
                content: new OA\JsonContent(
                    example: [
                        'success' => true,
                        'message' => 'Data berhasil diambil',
                        'data' => 'data'
                    ]
                )
            ),
            new OA\Response(
                response: '401',
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Unauthorized'
                    ]
                )
            ),
            new OA\Response(
                response: '500',
                description: 'Internal Server Error',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Internal Server Error'
                    ]
                )
            ),
            new OA\Response(
                response: '404',
                description: 'Ruangan Role not found',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Ruangan Role not found'
                    ]
                )
            )
        ]
    )]


    public function detail(Request $request)
    {
        $fasilitas = Fasilitas::find($request->id);
        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $fasilitas
        ]);
    }

    #[OA\Post(
        path: '/api/kelola-fasilitas/create',
        summary: 'Create fasilitas',
        description: 'Create fasilitas',
        tags: ['Kelola Fasilitas'],
        security: [['cookieAuth' => ['jwtToken']]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/KelolaFasilitasCreateRequest')
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Data berhasil ditambahkan',
                content: new OA\JsonContent(
                    example: [
                        'success' => true,
                        'message' => 'Data berhasil ditambahkan',
                        'data' => 'data'
                    ]
                )
            ),
            new OA\Response(
                response: '401',
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Unauthorized'
                    ]
                )
            ),
            new OA\Response(
                response: '500',
                description: 'Internal Server Error',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'Internal Server Error'
                    ]
                )
            )
        ]
    )]

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'fasilitas_nama' => 'required|unique:m_fasilitas,fasilitas_nama',
            ]);
            $validatedData['created_at'] = now();
            $fasilitas = Fasilitas::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan',
                'data' => $fasilitas
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    #[OA\Put(
        path: '/api/kelola-fasilitas/{id}',
        summary: 'Update fasilitas',
        description: 'Update fasilitas',
        tags: ['Kelola Fasilitas'],
        security: [['cookieAuth' => ['jwtToken']]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID'
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/KelolaFasilitasUpdateRequest')
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Data berhasil diubah',
            ),
            new OA\Response(
                response: '401',
                description: 'Unauthorized',
            ),
            new OA\Response(
                response: '500',
                description: 'Internal Server Error',
            ),
            new OA\Response(
                response: '404',
                description: 'User not found',
            )
        ]
    )]

    public function update(Request $request)
    {
        $fasilitas = Fasilitas::find($request->id);
        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'fasilitas_nama' => 'required|unique:m_fasilitas,fasilitas_nama,' . $request->id . ',fasilitas_id',
            ]);
            
            $fasilitas->update($validated);
            $fasilitas->updated_at = now();
            $fasilitas->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $fasilitas
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    #[OA\Delete(
        path: '/api/kelola-fasilitas/{id}',
        summary: 'Delete fasilitas',
        description: 'Delete fasilitas',
        tags: ['Kelola Fasilitas'],
        security: [['cookieAuth' => ['jwtToken']]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID'
            )
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Data berhasil dihapus',
            ),
            new OA\Response(
                response: '401',
                description: 'Unauthorized',
            ),
            new OA\Response(
                response: '500',
                description: 'Internal Server Error',
            ),
            new OA\Response(
                response: '404',
                description: 'User not found',
            )
        ]
    )]

    public function delete(Request $request)
    {
        $fasilitas = Fasilitas::find($request->id);
        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas not found'
            ], 404);
        }

        // Check if fasilitas is being used in t_fasilitas_ruang
        $isMapped = DB::table('t_fasilitas_ruang')
            ->where('fasilitas_id', $request->id)
            ->exists();

        if ($isMapped) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas tidak dapat dihapus karena sudah digunakan pada ruangan'
            ], 400);
        }

        $fasilitas->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
