<?php

namespace OpenCFP\Domain\Services;

use Cartalyst\Sentry\Users\UserInterface;

interface AccountManagement
{
    public function findById($userId): UserInterface;

    public function findByLogin($email): UserInterface;

    public function findByRole($role): array;

    public function create($email, $password, array $data = []): UserInterface;

    public function activate($email);

    public function promoteTo($email, $role = 'Admin');

    public function demoteFrom($email, $role = 'Admin');
}
