<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendLendingNotification;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LibraryProxyController extends Controller
{
    public function author(Request $request, int $id)
    {
        return $this->forward(
            $request,
            config('services.book.url'),
            '/api/authors/'.$id
        );
    }

    public function authors(Request $request)
    {
        return $this->forward(
            $request,
            config('services.book.url'),
            '/api/authors'
        );
    }

    public function books(Request $request)
    {
        return $this->forward(
            $request,
            config('services.book.url'),
            '/api/books'
        );
    }

    public function members(Request $request)
    {
        if (! $request->isMethod('POST')) {
            return $this->forward(
                $request,
                config('services.member.url'),
                '/api/members'
            );
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(10)
                ->post(
                    rtrim(config('services.member.url'), '/') . '/api/members',
                    $request->all()
                );
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Member Service tidak dapat dihubungi.',
            ], 503);
        }

        // Akun login hanya dibuat setelah Member Service mengonfirmasi berhasil
        if ($response->status() === 201) {
            $this->createMemberAccount($response->json('data') ?? $response->json());
        }

        return response(
            $response->body(),
            $response->status()
        )->header(
            'Content-Type',
            $response->header('Content-Type', 'application/json')
        );
    }

    public function lendings(Request $request)
    {
        if ($request->isMethod('GET')) {
            $query = $request->query();

            // MEMBER hanya boleh melihat pinjaman miliknya sendiri.
            // Filter dipaksa di gateway; member_id dari client diabaikan.
            if ($request->user()?->role === 'MEMBER') {
                $query['member_id'] = $this->memberIdFor($request->user());
            }

            return $this->forward(
                $request,
                config('services.member.url'),
                '/api/lendings',
                $query
            );
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(10)
                ->post(
                    rtrim(config('services.member.url'), '/') . '/api/lendings',
                    $request->all()
                );

            // Antrean hanya menerima notifikasi setelah peminjaman
            // benar-benar dikonfirmasi berhasil oleh Member Service.
            if ($response->status() === 201) {
                $data = $response->json();

                $lending = $data['data'] ?? $data;

                if (
                    isset($lending['id']) &&
                    isset($lending['member_id'])
                ) {
                    SendLendingNotification::dispatch(
                        $lending['id'],
                        $lending['member_id']
                    );
                }
            }

            return response(
                $response->body(),
                $response->status()
            )->header(
                'Content-Type',
                $response->header('Content-Type', 'application/json')
            );
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Member Service tidak dapat dihubungi.',
            ], 503);
        }
    }

    /**
     * Cari id anggota di Member Service berdasarkan email akun login.
     * Gateway tidak menyentuh database Member Service, hanya lewat HTTP.
     */
    private function memberIdFor(User $user): int
    {
        return Cache::remember("member_id:{$user->id}", 600, function () use ($user) {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout(2)
                    ->timeout(10)
                    ->get(
                        rtrim(config('services.member.url'), '/') . '/api/members',
                        ['email' => $user->email]
                    );
            } catch (ConnectionException $e) {
                abort(503, 'Member Service tidak dapat dihubungi.');
            }

            $data = $response->json('data') ?? $response->json();
            $member = collect($data)->firstWhere('email', $user->email);

            abort_if(! $member, 403, 'Akun belum terhubung ke data anggota.');

            return (int) $member['id'];
        });
    }

    private function forward(
        Request $request,
        string $baseUrl,
        string $path,
        ?array $query = null
    ) {
        try {
            $url = rtrim($baseUrl, '/') . $path;

            if ($request->isMethod('GET')) {
                // Retry hanya saat koneksi gagal, bukan untuk respons 4xx/5xx
                $response = Http::acceptJson()
                    ->connectTimeout(2)
                    ->timeout(10)
                    ->retry(
                        3,
                        100,
                        fn ($e) => $e instanceof ConnectionException,
                        throw: false
                    )
                    ->get($url, $query ?? $request->query());
            } elseif ($request->isMethod('POST')) {
                // POST tidak di-retry agar data tidak tersimpan dobel
                $response = Http::acceptJson()
                    ->connectTimeout(2)
                    ->timeout(10)
                    ->post($url, $request->all());
            } else {
                return response()->json([
                    'message' => 'HTTP method belum didukung.',
                ], Response::HTTP_METHOD_NOT_ALLOWED);
            }

            return response(
                $response->body(),
                $response->status()
            )->header(
                'Content-Type',
                $response->header('Content-Type', 'application/json')
            );
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan tujuan tidak dapat dihubungi.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Buat akun login MEMBER dari data anggota yang baru didaftarkan.
     * Password awal = nomor anggota (admin dapat mereset lewat menu Reset Password).
     * Kalau email sudah dipakai akun lain, akun yang ada tidak diubah.
     */
    private function createMemberAccount(?array $member): void
    {
        if (! is_array($member) || empty($member['email'])) {
            Log::warning('Akun MEMBER tidak dibuat: email anggota kosong', [
                'member' => $member,
            ]);

            return;
        }

        try {
            User::firstOrCreate(
                ['email' => $member['email']],
                [
                    'name' => $member['name'] ?? $member['email'],
                    'password' => (string) ($member['member_number'] ?? Str::random(12)),
                    'role' => 'MEMBER',
                    'is_active' => ($member['status'] ?? 'ACTIVE') !== 'INACTIVE',
                ]
            );
        } catch (\Throwable $e) {
            // Anggota sudah tersimpan di Member Service; jangan gagalkan responsnya
            Log::error('Gagal membuat akun MEMBER', [
                'email' => $member['email'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
