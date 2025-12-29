<?php
// New Code
namespace Mercator\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mercator\Core\Services\LicenseService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier la licence Enterprise
 *
 * Utilisé pour protéger les routes des fonctionnalités Enterprise
 */
class CheckLicense
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module = null): Response
    {
        // Vérifier si une licence valide existe
        if (!$this->licenseService->hasValidLicense()) {
            return $this->unauthorizedResponse($request, 'No valid license found');
        }

        // Si un module spécifique est requis, le vérifier
        if ($module !== null) {
            if (!$this->licenseService->hasModule($module)) {
                return $this->unauthorizedResponse(
                    $request,
                    "Module '{$module}' is not included in your license"
                );
            }
        }

        return $next($request);
    }

    /**
     * Réponse en cas de licence invalide
     *
     * @param Request $request
     * @param string $message
     * @return Response
     */
    protected function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'license_required' => true,
            ], 403);
        }

        return redirect()->route('license.required')
            ->with('error', $message);
    }
}

