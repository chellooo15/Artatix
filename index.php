<?php

use App\Middleware\Auth;

require_once __DIR__ . '/config/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    // Public routes
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/events' => [EventController::class, 'index'],
        '/events/{id}' => [EventController::class, 'show'],
        '/login' => [AuthController::class, 'showLogin'],
        '/register' => [AuthController::class, 'showRegister'],
        '/user/orders' => [UserController::class, 'orders'],
        '/user/orders/{id}' => [UserController::class, 'orderDetail'],
        '/user/tickets/{code}' => [UserController::class, 'ticketDetail'],
        '/user/settings' => [UserController::class, 'settings'],
        '/api/seats' => [EventController::class, 'seats'],
        '/checkout/{orderId}/success' => [CheckoutController::class, 'success'],
        '/invite/{token}' => [InviteController::class, 'show'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/logout' => [AuthController::class, 'logout'],
        '/checkout' => [CheckoutController::class, 'store'],
        '/checkout/{orderId}/pay' => [CheckoutController::class, 'pay'],
        '/api/check-po-code' => [UserController::class, 'checkPOCode'],
        '/user/orders/{id}/cancel' => [UserController::class, 'cancelOrder'],
        '/user/settings' => [UserController::class, 'updateSettings'],
        '/user/settings/password' => [UserController::class, 'updatePassword'],
        '/invite/{token}/accept' => [InviteController::class, 'accept'],
    ],
];

// Admin routes (require admin)
$adminRoutes = [
    'GET' => [
        '/admin' => [AdminController::class, 'dashboard'],
        '/admin/events' => [AdminEventController::class, 'index'],
        '/admin/events/create' => [AdminEventController::class, 'create'],
        '/admin/events/{id}/edit' => [AdminEventController::class, 'edit'],
        '/admin/ticket-classes' => [AdminTicketClassController::class, 'index'],
        '/admin/ticket-classes/create' => [AdminTicketClassController::class, 'create'],
        '/admin/ticket-classes/{id}/edit' => [AdminTicketClassController::class, 'edit'],
        '/admin/venues' => [AdminVenueController::class, 'index'],
        '/admin/venues/create' => [AdminVenueController::class, 'create'],
        '/admin/venues/{id}/edit' => [AdminVenueController::class, 'edit'],
        '/admin/po-codes' => [AdminPOCodeController::class, 'index'],
        '/admin/po-codes/create' => [AdminPOCodeController::class, 'create'],
        '/admin/orders' => [AdminOrderController::class, 'index'],
        '/admin/orders/{id}' => [AdminOrderController::class, 'show'],
        '/admin/payments' => [AdminPaymentController::class, 'index'],
    ],
    'POST' => [
        '/admin/events' => [AdminEventController::class, 'store'],
        '/admin/events/{id}' => [AdminEventController::class, 'update'],
        '/admin/events/{id}/delete' => [AdminEventController::class, 'destroy'],
        '/admin/events/{id}/organizers' => [AdminEventController::class, 'addOrganizer'],
        '/admin/events/{id}/organizers/{userId}/delete' => [AdminEventController::class, 'removeOrganizer'],
        '/admin/events/{id}/organizers/{userId}/permission' => [AdminEventController::class, 'setOrganizerPermission'],
        '/admin/events/{id}/invites' => [AdminEventController::class, 'createInvite'],
        '/admin/events/{id}/invites/{inviteId}/revoke' => [AdminEventController::class, 'revokeInvite'],
        '/admin/ticket-classes' => [AdminTicketClassController::class, 'store'],
        '/admin/ticket-classes/{id}' => [AdminTicketClassController::class, 'update'],
        '/admin/ticket-classes/{id}/delete' => [AdminTicketClassController::class, 'destroy'],
        '/admin/venues' => [AdminVenueController::class, 'store'],
        '/admin/venues/{id}' => [AdminVenueController::class, 'update'],
        '/admin/venues/{id}/delete' => [AdminVenueController::class, 'destroy'],
        '/admin/po-codes' => [AdminPOCodeController::class, 'store'],
        '/admin/po-codes/{id}/delete' => [AdminPOCodeController::class, 'destroy'],
        '/admin/orders/{id}/verify' => [AdminOrderController::class, 'verifyPayment'],
    ],
];

function matchRoute(string $uri, array $routes, string $method): ?array
{
    foreach ($routes[$method] ?? [] as $pattern => $handler) {
        $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches);
            $paramNames = [];
            preg_match_all('/\{(\w+)\}/', $pattern, $paramNames);
            $params = array_combine($paramNames[1] ?? [], $matches);
            return ['handler' => $handler, 'params' => $params];
        }
    }
    return null;
}

$matched = matchRoute($uri, $routes, $method);

// Admin routes (admin full access; organizer scoped per-event inside controllers)
if (!$matched && Auth::check() && Auth::isAdminOrOrganizer()) {
    $matched = matchRoute($uri, $adminRoutes, $method);
}

if ($matched) {
    [$controllerClass, $method] = $matched['handler'];

    // Pasang namespace jika belum ada prefix App\
    if (!str_starts_with($controllerClass, 'App\\')) {
        $controllerClass = 'App\\Actions\\' . $controllerClass;
    }

    $controller = new $controllerClass();
    $controller->$method(...array_values($matched['params']));
} else {
    http_response_code(404);
    require __DIR__ . '/pages/errors/404.php';
}