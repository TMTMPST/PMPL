<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\m_user as User;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Hash;

class KelolaPengguna extends Controller
{
    #[OA\Schema(
        schema: 'KelolaPenggunaRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'role', type: 'string', example: ''),
            new OA\Property(property: 'search', type: 'string', example: '')
        ]
    )]

    #[OA\Schema(
        schema: 'KelolaPenggunaCreateRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'role_id', type: 'string', example: ''),
            new OA\Property(property: 'username', type: 'string', example: ''),
            new OA\Property(property: 'fullname', type: 'string', example: ''),
            new OA\Property(property: 'password', type: 'string', example: ''),
            new OA\Property(property: 'email', type: 'string', example: ''),
            new OA\Property(property: 'no_telp', type: 'string', example: ''),
        ]
    )]

    #[OA\Schema(
        schema: 'KelolaPenggunaUpdateRequest',
        type: 'object',
        properties: [
            new OA\Property(property: 'role_id', type: 'string', example: ''),
            new OA\Property(property: 'username', type: 'string', example: ''),
            new OA\Property(property: 'fullname', type: 'string', example: ''),
            new OA\Property(property: 'email', type: 'string', example: ''),
            new OA\Property(property: 'no_telp', type: 'string', example: ''),
        ],
    )]

    #[OA\Post(
        path: '/api/kelola-pengguna',
        summary: 'Get list of users',
        description: 'Get list of users with pagination and search',
        tags: ['Kelola Pengguna'],
        security: [['cookieAuth' => ['jwtToken']]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/KelolaPenggunaRequest')
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
        $query = User::with('role');
        $dataTotal = $query->count();

        if ($request->role != '') {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('role_nama', $request->role);
            });
        }

        if ($request->search != '') {
            $query->where('username', 'like', '%' . $request->search . '%');
        }

        $dataFiltered = $query->count();

        $query->offset($request->start);
        $query->limit($request->length);

        return response()->json(
            [
                'success' => true,
                'message' => 'Data berhasil diambil',
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $dataTotal,
                'recordsFiltered' => $dataFiltered,
                'data' => $query->get()
            ]
        );
    }

    #[OA\Get(
        path: '/api/kelola-pengguna/{id}',
        summary: 'Get user detail',
        description: 'Get user detail',
        tags: ['Kelola Pengguna'],
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
                description: 'User not found',
                content: new OA\JsonContent(
                    example: [
                        'success' => false,
                        'message' => 'User not found'
                    ]
                )
            )
        ]
    )]


    public function detail(Request $request)
    {
        // Load user dengan relasi role
        $user = User::with('role')->find($request->id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Format data untuk frontend
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => [
                'user_id' => $user->user_id,
                'fullname' => $user->fullname,
                'username' => $user->username,
                'email' => $user->email,
                'no_telp' => $user->no_telp,
                'profile_picture' => $user->profile_picture,
                'role_nama' => $user->role ? $user->role->role_nama : '-' // Ambil role_nama dari relasi
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/kelola-pengguna/create',
        summary: 'Create user',
        description: 'Create user',
        tags: ['Kelola Pengguna'],
        security: [['cookieAuth' => ['jwtToken']]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/KelolaPenggunaCreateRequest')
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
                'role_id' => 'required|exists:m_role,role_id',
                'username' => 'required|string|min:3|unique:m_user,username',
                'fullname' => 'required|string|min:3',
                'password' => 'required|string|min:6',
            ]);
            
            $validatedData['created_at'] = now();
            $hashedPassword = Hash::make($request->password);
            $validatedData['password'] = $hashedPassword;
            
            $user = User::create($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    #[OA\Put(
        path: '/api/kelola-pengguna/{id}',
        summary: 'Update user',
        description: 'Update user',
        tags: ['Kelola Pengguna'],
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
                schema: new OA\Schema(ref: '#/components/schemas/KelolaPenggunaUpdateRequest')
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
        $user = User::find($request->id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'role_id' => 'required|exists:m_role,role_id',
                'username' => 'required|string|min:3|unique:m_user,username,' . $request->id . ',user_id',
                'fullname' => 'required|string|min:3',
            ]);
            
            $user->update($validated);
            $user->updated_at = now();
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    #[OA\Delete(
        path: '/api/kelola-pengguna/{id}',
        summary: 'Delete user',
        description: 'Delete user',
        tags: ['Kelola Pengguna'],
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
        $user = User::find($request->id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent deleting currently logged-in user
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_id == $request->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus user yang sedang login'
            ], 400);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
