<?php

declare(strict_types=1);

use Cafeteria\Controllers\Admin\CategoryController;
use Cafeteria\Controllers\Admin\FulfillmentController;
use Cafeteria\Controllers\Admin\UserController;
use Cafeteria\Controllers\Auth\ForgotPasswordController;
use Cafeteria\Controllers\Auth\LoginController;
use Cafeteria\Controllers\Auth\LogoutController;
use Cafeteria\Controllers\Auth\ResetPasswordController;
use Cafeteria\Controllers\HealthController;
use Cafeteria\Controllers\Admin\ProductController;
use Cafeteria\Controllers\User\OrderController;
use Cafeteria\Controllers\User\CatalogController;
use Cafeteria\Core\Auth\AdminMiddleware;
use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Auth\GuestMiddleware;
use Cafeteria\Core\Database\ConnectionFactory;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Routing\Router;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\Session\SessionManager;
use Cafeteria\Core\Upload\SafeUploader;
use Cafeteria\Core\View\View;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Policies\OrderPolicy;
use Cafeteria\Repositories\Pdo\PdoAdminUserRepository;
use Cafeteria\Repositories\Pdo\PdoAuthUserRepository;
use Cafeteria\Repositories\Pdo\PdoCategoryRepository;
use Cafeteria\Repositories\Pdo\PdoPasswordResetTokenRepository;
use Cafeteria\Repositories\Pdo\PdoOrderCommandRepository;
use Cafeteria\Repositories\Pdo\PdoOrderQueryRepository;
use Cafeteria\Repositories\Pdo\PdoProductRepository;
use Cafeteria\Services\AuthService;
use Cafeteria\Services\CategoryService;
use Cafeteria\Services\PasswordResetService;
use Cafeteria\Services\UserService;
use Cafeteria\Services\OrderService;
use Cafeteria\Services\OrderStatusService;
use Cafeteria\Services\ProductService;
use Cafeteria\Validation\CategoryValidator;
use Cafeteria\Validation\LoginValidator;
use Cafeteria\Validation\PasswordResetValidator;
use Cafeteria\Validation\UserValidator;
use Cafeteria\Validation\PlaceOrderValidator;
use Cafeteria\Validation\ProductValidator;

require __DIR__ . '/autoload.php';

$appConfig = require dirname(__DIR__) . '/config/app.php';
$sessionConfig = require dirname(__DIR__) . '/config/session.php';
$databaseConfig = require dirname(__DIR__) . '/config/database.php';

$session = new SessionManager($sessionConfig);
$session->start();

$flash = new FlashBag($session);
$csrf = new CsrfTokenManager($session);

View::share(
    'csrfField',
    sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars(CsrfTokenManager::FIELD_NAME, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($csrf->token(), ENT_QUOTES, 'UTF-8'),
    ),
);

$authMiddleware = new AuthMiddleware($session);
$adminMiddleware = new AdminMiddleware($session);
$guestMiddleware = new GuestMiddleware($session);

$adminPolicy = new AdminPolicy();
$orderPolicy = new OrderPolicy();

$pdo = (new ConnectionFactory())->make($databaseConfig);

$authUsers = new PdoAuthUserRepository($pdo);
$resetTokens = new PdoPasswordResetTokenRepository($pdo);
$categoryRepository = new PdoCategoryRepository($pdo);
$productRepository = new PdoProductRepository($pdo);
$orderCommandRepository = new PdoOrderCommandRepository($pdo);
$orderQueryRepository = new PdoOrderQueryRepository($pdo);
$adminUserRepository = new PdoAdminUserRepository($pdo);

$loginValidator = new LoginValidator();
$passwordResetValidator = new PasswordResetValidator();
$categoryValidator = new CategoryValidator();
$productValidator = new ProductValidator();
$placeOrderValidator = new PlaceOrderValidator();
$userValidator = new UserValidator();

$authService = new AuthService($authUsers, $session);

$passwordResetService = new PasswordResetService(
    $authUsers,
    $resetTokens,
    $session,
    $pdo,
    $appConfig,
);

$profileUploadDirectory = dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'storage'
    . DIRECTORY_SEPARATOR
    . 'uploads'
    . DIRECTORY_SEPARATOR
    . 'profiles';

$productUploadDirectory = dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'storage'
    . DIRECTORY_SEPARATOR
    . 'uploads'
    . DIRECTORY_SEPARATOR
    . 'products';

$profileUploader = new SafeUploader($profileUploadDirectory);
$productUploader = new SafeUploader($productUploadDirectory);

$categoryService = new CategoryService(
    $categoryRepository,
    $categoryValidator,
    $adminPolicy,
);

$userService = new UserService(
    $adminUserRepository,
    $userValidator,
    $adminPolicy,
    $profileUploader,
);

$orderService = new OrderService(
    $productRepository,
    $orderCommandRepository,
    $placeOrderValidator,
    $pdo,
);

$orderStatusService = new OrderStatusService(
    $orderQueryRepository,
    $orderCommandRepository,
    $orderPolicy,
);

$productService = new ProductService(
    $productRepository,
    $categoryRepository,
    $productValidator,
    $adminPolicy,
    $productUploader,
);

$controllers = [
    HealthController::class => new HealthController(),
    LoginController::class => new LoginController($authService, $loginValidator, $csrf),
    LogoutController::class => new LogoutController($authService, $csrf),
    ForgotPasswordController::class => new ForgotPasswordController(
        $passwordResetService,
        $passwordResetValidator,
        $csrf,
        $flash,
    ),
    ResetPasswordController::class => new ResetPasswordController(
        $passwordResetService,
        $passwordResetValidator,
        $csrf,
    ),
    CategoryController::class => new CategoryController($categoryService, $csrf, $flash),
    UserController::class => new UserController($userService, $csrf, $flash),
    ProductController::class => new ProductController(
        $productService,
        $categoryRepository,
        $csrf,
        $flash,
    ),
    CatalogController::class => new CatalogController(
        $productRepository,
        $orderQueryRepository,
    ),
    OrderController::class => new OrderController(
        $orderService,
        $orderStatusService,
        $orderQueryRepository,
        $productRepository,
        $orderPolicy,
        $pdo,
        $csrf,
        $flash,
    ),
    FulfillmentController::class => new FulfillmentController(
        $orderQueryRepository,
        $orderStatusService,
        $csrf,
        $flash,
    ),
];

$router = new Router();

$router->setControllerFactory(
    static function (string $class) use ($controllers): object {
        return $controllers[$class] ?? new $class();
    }
);

$router->setCurrentUserResolver(
    static function () use ($session): ?AuthenticatedUser {
        $user = $session->get(AuthMiddleware::SESSION_USER_KEY);

        if (!is_array($user)) {
            return null;
        }

        return AuthenticatedUser::fromSession($user);
    }
);

$router->get('/health', [HealthController::class, 'show']);

$routesFile = dirname(__DIR__) . '/routes/web.php';

if (is_file($routesFile)) {
    require $routesFile;
}

return [
    'config' => [
        'app' => $appConfig,
        'session' => $sessionConfig,
        'database' => $databaseConfig,
    ],
    'pdo' => $pdo,
    'session' => $session,
    'flash' => $flash,
    'csrf' => $csrf,
    'middleware' => [
        'auth' => $authMiddleware,
        'admin' => $adminMiddleware,
        'guest' => $guestMiddleware,
    ],
    'policies' => [
        'admin' => $adminPolicy,
        'order' => $orderPolicy,
    ],
    'repositories' => [
        'auth_users' => $authUsers,
        'reset_tokens' => $resetTokens,
        'categories' => $categoryRepository,
        'admin_users' => $adminUserRepository,
        'products' => $productRepository,
        'orders_command' => $orderCommandRepository,
        'orders_query' => $orderQueryRepository,
    ],
    'services' => [
        'auth' => $authService,
        'password_reset' => $passwordResetService,
        'categories' => $categoryService,
        'users' => $userService,
        'products' => $productService,
        'orders' => $orderService,
        'order_status' => $orderStatusService,
    ],
    'router' => $router,
    'request_factory' => static fn (): Request => Request::fromGlobals(),
];
