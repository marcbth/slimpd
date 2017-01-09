<?php
namespace Slimpd\Modules\Auth;
/* Copyright (C) 2017 othmar52 <othmar52@users.noreply.github.com>
 *
 * This file is part of sliMpd - a php based mpd web client
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Slimpd\Models\User;

class Auth
{
    public function user()
    {
        return User::find(@$_SESSION['user']);
    }

    public function check()
    {
        return isset($_SESSION['user']);
    }

    public function attempt($username, $password)
    {
        $user = User::where('username', $username)->first();
        if(!$user) {
            return FALSE;
        }
        if(password_verify($password, $user->password)) {
            $_SESSION['user'] = $user->uid;
            return TRUE;
        }
        return FALSE;
    }

    public function attemptQuickLogin($userUid)
    {
        $user = User::where([
            ["quickswitch", "=", "1"],
            ["uid", "=", $userUid]
        ])->first();
        if(!$user) {
            return FALSE;
        }
        $_SESSION['user'] = $user->uid;
        return TRUE;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }

    public function permissions()
    {
        $user = User::find(@$_SESSION['user']);
        if(!$user) {
            return $this->permissionsForRole('guest');
        }
        return $this->permissionsForRole($user->role);
    }

    private function permissionsForRole($rolename) {
        // TODO: move roles and permissions to database
        switch($rolename) {
            case 'guest':
                return array(
                    'media' => '0'
                );
            case 'member':
                return array(
                    'media' => '1'
                );
        }
    }

    public function hasPermissionFor($key) {
        return @$this->permissions()[$key] === '1';
    }
}