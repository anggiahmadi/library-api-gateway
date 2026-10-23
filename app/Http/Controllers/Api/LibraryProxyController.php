<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;

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
        return $this->forward(
            $request,
            config('services.member.url'),
            '/api/members'
        );
    }

    public function lendings(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->forward(
                $request,
                config('services.member.url'),
                '/api/lendings'
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
                    \App\Jobs\SendLendingNotification::dispatch(
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Member Service tidak dapat dihubungi.',
            ], 503);
        }
    }

    private function forward(Request $request, string $baseUrl, string $path)
    {
        try {
            $url = rtrim($baseUrl, '/') . $path;

            $http = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(10)
                ->retry(3);

            $options = [];

            if ($request->isMethod('GET')) {
                $response = $http->get(
                    $url,
                    $request->query()
                );
            } elseif ($request->isMethod('POST')) {
                $response = $http->post(
                    $url,
                    $request->all()
                );
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
}
