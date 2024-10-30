<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\CommentController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\TranslationController;
use App\Http\Middleware\ValidateUserRole;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::put('profile', [AuthController::class, 'updateProfile'])->middleware( AdminMiddleware::class); 
    Route::get('role', [AuthController::class,'role'])->middleware('auth:api');
});

Route::get('/translations/{lang}', [TranslationController::class, 'getTranslations']);


Route::middleware('auth:api')->group(function () {
    Route::prefix('tickets')->group(function () {
        Route::post('/', [TicketController::class, 'store']); 
        Route::get('/client', [TicketController::class, 'getTicketsByClient']);
        Route::get('/engineer', [TicketController::class, 'getTicketsByEngineer']);
        Route::get('/unassigned', [TicketController::class, 'getUnassignedTickets']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::get('/download/{id}', [TicketController::class,'downloadFile']);
        Route::post('/{id}/upload-file', [TicketController::class, 'uploadFile']);
        Route::post('/comment/{id}', [TicketController::class, 'createComment']);
        Route::get('/comment/{id}', [TicketController::class, 'getCommmentByTicketId']);
        Route::get('/assign-to-me/{id}', [TicketController::class,'assignToMe']);
    });

    Route::prefix('tickets/{ticketId}/comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']); 
        Route::get('/', [CommentController::class, 'index']); 
        Route::get('/{id}', [CommentController::class, 'show']); 
        Route::put('/{id}', [CommentController::class, 'update']); 
        Route::delete('/{id}', [CommentController::class, 'destroy']); 
    });
});



Route::middleware('auth:api')->group(function () {
    Route::get('clients/{id}', function ($id) {
        
        if (auth()->user()->id == $id) {
            return (new ClientController)->show($id);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    });

    Route::get('engineers/{id}', function ($id) {
        
        if (auth()->user()->id == $id) {
            return (new EngineerController)->show($id);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    });
});
