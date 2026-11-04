<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

// Único jeito de criar usuário do painel (não há cadastro público).
Artisan::command('admin:criar', function () {
    $name = text('Nome', required: true);
    $email = text('E-mail', required: true, validate: ['email' => 'email|unique:users,email']);
    $password = password('Senha (mín. 12, com maiúscula, número e símbolo)', required: true, validate: ['password' => Password::defaults()]);

    User::create(compact('name', 'email', 'password'));

    $this->info('Usuário criado. No primeiro login em /admin o painel pede para configurar o 2FA.');
})->purpose('Cria um usuário do painel /admin com senha forte');
