<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (PostTooLargeException $exception, Request $request) {
            return response()->view('errors.413', [
                'joinRequest' => $request->is('join'),
            ], 413);
        });

        $this->renderable(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419 || ! $request->is('member/*')) {
                return null;
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('member.index')->withErrors([
                'session' => 'Your sign-in page expired. Please try again.',
            ]);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
