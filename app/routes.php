<?php

declare(strict_types=1);

use App\Controllers\BBCodeController;
use App\Controllers\CommentController;
use App\Controllers\FavoriteController;
use App\Controllers\HomeController;
use App\Controllers\RatingController;
use App\Controllers\SearchController;
use App\Controllers\StoryController;
use App\Controllers\CaptchaController;
use App\Controllers\GuestbookController;
use App\Controllers\TagController;
use App\Controllers\UploadController;
use App\Controllers\User\AdminController as UserAdminController;
use App\Controllers\User\ProfileController;
use App\Controllers\StickerController;
use App\Controllers\UserController;
use App\Controllers\UserStoryController;
use App\Middleware\CheckAdminMiddleware;
use App\Middleware\CheckUserMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /*$app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });*/

    $app->get('/', [HomeController::class, 'index'])->setName('home');
    $app->get('/docs', [HomeController::class, 'docs']);
    $app->get('/docs/versions', [HomeController::class, 'versions']);
    $app->get('/docs/commits', [HomeController::class, 'commits']);

    $app->group('/stories', function (Group $group) {
        $group->get('', [StoryController::class, 'index'])->setName('stories');
        $group->get('/{slug:[\w\-]+\-[\d]+}', [StoryController::class, 'view'])->setName('story-view');

        // For user
        $group->group('', function (Group $group) {
            $group->get('/create', [StoryController::class, 'create'])->setName('story-create');
            $group->post('/create', [StoryController::class, 'store'])->setName('story-store');
            $group->get('/{id:[0-9]+}/edit', [StoryController::class, 'edit'])->setName('story-edit');
            $group->put('/{id:[0-9]+}', [StoryController::class, 'update'])->setName('story-update');
            $group->delete('/{id:[0-9]+}', [StoryController::class, 'destroy'])->setName('story-destroy');
            $group->post('/{id:[0-9]+}/comments', [CommentController::class, 'store'])->setName('story-comment-store');
        })->add(CheckUserMiddleware::class);

        // Edit and delete comment (for admin)
        $group->group('/{id:[0-9]+}/comments/{cid:[0-9]+}', function (Group $group) {
            $group->get('/edit', [CommentController::class, 'edit'])->setName('story-comment-edit');
            $group->put('', [CommentController::class, 'update'])->setName('story-comment-update');
            $group->delete('', [CommentController::class, 'destroy'])->setName('story-comment-destroy');
        })->add(CheckAdminMiddleware::class);
    });

    // Tags
    $app->get('/tag', [TagController::class, 'tag']);
    $app->get('/tags', [TagController::class, 'index']);
    $app->get('/tags/{tag:.+}', [TagController::class, 'search']);

    // For user group
    $app->group('', function (Group $group) {
        // Upload
        $group->group('/upload', function (Group $group) {
            $group->post('', [UploadController::class, 'upload']);
            $group->delete('/{id:[0-9]+}', [UploadController::class, 'destroy']);
        });

        // Profile
        $group->group('/profile', function (Group $group) {
            $group->get('', [ProfileController::class, 'index'])->setName('profile-edit');
            $group->put('', [ProfileController::class, 'store'])->setName('profile-store');
            $group->delete('/photo', [ProfileController::class, 'destroyPhoto'])->setName('profile-photo-destroy');
        });

        // Change rating
        $group->post('/rating/{id:[0-9]+}', [RatingController::class, 'change']);
    })->add(CheckUserMiddleware::class);

    $app->get('/captcha', [CaptchaController::class, 'captcha']);
    $app->get('/stickers/modal', [StickerController::class, 'modal']);
    $app->post('/bbcode', [BBCodeController::class, 'bbcode']);

    $app->map(['GET', 'POST'], '/login', [UserController::class, 'login']);
    $app->map(['GET', 'POST'], '/register', [UserController::class, 'register']);
    $app->post('/logout', [UserController::class, 'logout']);

    $app->group('/guestbook', function (Group $group) {
        $group->get('', [GuestbookController::class, 'index']);
        $group->post('', [GuestbookController::class, 'store']);
        $group->get('/{id:[0-9]+}/edit', [GuestbookController::class, 'edit']);
        $group->put('/{id:[0-9]+}', [GuestbookController::class, 'update']);
        $group->delete('/{id:[0-9]+}', [GuestbookController::class, 'destroy']);
    });

    $app->group('/users', function (Group $group) {
        $group->get('', [UserController::class, 'index'])->setName('users');;
        $group->get('/{login:[\w\-]+}', [UserController::class, 'user']);
        $group->get('/{login:[\w\-]+}/stories', [UserStoryController::class, 'index']);

        // Edit and delete user (for admin)
        $group->group('/{login:[\w\-]+}', function (Group $group) {
            $group->get('/edit', [UserAdminController::class, 'edit'])->setName('user-edit');
            $group->put('', [UserAdminController::class, 'store'])->setName('user-store');
            $group->delete('', [UserAdminController::class, 'destroy'])->setName('user-destroy');
        })->add(CheckAdminMiddleware::class);
    });

    // Favorites
    $app->group('/favorites', function (Group $group) {
        $group->get('', [FavoriteController::class, 'index']);
        // Add/delete to favorite
        $group->post('/{id:[0-9]+}', [FavoriteController::class, 'change']);
    })->add(CheckUserMiddleware::class);

    $app->group('/search', function (Group $group) {
        $group->get('', [SearchController::class, 'index']);
    });
};
