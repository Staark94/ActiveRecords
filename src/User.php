<?php
namespace Staark;

class User extends ActiveRecord\Eloquent
{
    protected $fillable = ['name', 'email', 'password'];
    protected $table = 'users';

    public function __construct($attribute = null)
    {
        /*$data = [
            'id' => 1,
            'name' => 'Admin',
            'email' => '',
            'password' => '123',
            'token' => '',
            'register' => date("d-m-y h:m:s", time()),
            'admin' => 0,
            'last_login' => null
        ];

        foreach ($data as $key => $item) {
            User::__set($key, $item);
        }*/
    }

    /**
     * @throws \Exception
     */
    public static function create(array $values = []): void
    {

    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'user function to string';
    }
}
