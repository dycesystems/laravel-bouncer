<?php

namespace Dyce\LaravelBouncer;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Guard
{
    /**
     * The authentication factory implementation.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The number of minutes tokens should be allowed to remain valid.
     *
     * @var int
     */
    protected $expiration;

    /**
     * The provider name.
     *
     * @var string
     */
    protected $provider;

    /**
     * Create a new guard instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @param  int  $expiration
     * @param  string  $provider
     * @return void
     */
    public function __construct(AuthFactory $auth, $expiration = null, $provider = null)
    {
        $this->auth = $auth;
        $this->expiration = $expiration;
        $this->provider = $provider;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        foreach (Arr::wrap(config('bouncer.guard', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                return $this->supportsTokens($user)
                    ? $user->withGameToken(new TransientToken)
                    : $user;
            }
        }

        if ($token = $request->get('token')) {
            $model = LaravelBouncer::$personalGameTokenModel;

            $gameToken = $model::findToken($token);

            if (! $this->isValidGameToken($gameToken)) {
                return;
            }

            return $this->supportsTokens($gameToken->tokenable) ? $gameToken->tokenable->withGameToken(
                tap($gameToken->forceFill(['last_used_at' => now()]))->save()
            ) : null;
        }
    }

    /**
     * Determine if the tokenable model supports game tokens.
     *
     * @param  mixed  $tokenable
     * @return bool
     */
    protected function supportsTokens($tokenable = null)
    {
        return $tokenable && in_array(HasGameTokens::class, class_uses_recursive(
            get_class($tokenable)
        ));
    }

    /**
     * Determine if the provided game token is valid.
     *
     * @param  mixed  $gameToken
     * @return bool
     */
    protected function isValidGameToken($gameToken): bool
    {
        if (! $gameToken) {
            return false;
        }

        $expiration = config('bouncer.expiration.'.$gameToken->type);

        $isValid =
            (! $expiration || $gameToken->created_at->gt(now()->subMinutes($expiration)))
            && $this->hasValidProvider($gameToken->tokenable);

        if (is_callable(LaravelBouncer::$gameTokenAuthenticationCallback)) {
            $isValid = (bool) (LaravelBouncer::$gameTokenAuthenticationCallback)($gameToken, $isValid);
        }

        return $isValid;
    }

    /**
     * Determine if the tokenable model matches the provider's model type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $tokenable
     * @return bool
     */
    protected function hasValidProvider($tokenable)
    {
        if (is_null($this->provider)) {
            return true;
        }

        $model = config("auth.providers.{$this->provider}.model");

        return $tokenable instanceof $model;
    }
}
