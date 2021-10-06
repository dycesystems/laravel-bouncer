<?php

namespace Dyce\LaravelBouncer;

use Mockery;

class LaravelBouncer
{
    /**
     * The personal access client model class name.
     *
     * @var string
     */
    public static $personalGameTokenModel = 'Dyce\\LaravelBouncer\\PersonalGameToken';

    /**
     * A callback that can add to the validation of the game token.
     *
     * @var callable|null
     */
    public static $gameTokenAuthenticationCallback;

    /**
     * Indicates if LaravelBouncer's migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Set the current user for the application with the given abilities.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\Dyce\LaravelBouncer\HasGameTokens  $user
     * @param  array  $abilities
     * @param  string  $guard
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public static function actingAs($user, $abilities = [], $guard = 'bouncer')
    {
        $token = Mockery::mock(self::personalGameTokenModel())->shouldIgnoreMissing(false);

        if (in_array('*', $abilities)) {
            $token->shouldReceive('can')->withAnyArgs()->andReturn(true);
        } else {
            foreach ($abilities as $ability) {
                $token->shouldReceive('can')->with($ability)->andReturn(true);
            }
        }

        $user->withGameToken($token);

        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        app('auth')->guard($guard)->setUser($user);

        app('auth')->shouldUse($guard);

        return $user;
    }

    /**
     * Set the personal game token model name.
     *
     * @param  string  $model
     * @return void
     */
    public static function usePersonalGameTokenModel($model)
    {
        static::$personalGameTokenModel = $model;
    }

    /**
     * Specify a callback that should be used to authenticate game tokens.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function authenticateGameTokensUsing(callable $callback)
    {
        static::$gameTokenAuthenticationCallback = $callback;
    }

    /**
     * Determine if LaravelBouncer's migrations should be run.
     *
     * @return bool
     */
    public static function shouldRunMigrations()
    {
        return static::$runsMigrations;
    }

    /**
     * Configure LaravelBouncer to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Get the token model class name.
     *
     * @return string
     */
    public static function personalGameTokenModel()
    {
        return static::$personalGameTokenModel;
    }
}
