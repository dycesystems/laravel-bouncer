<?php

namespace Dyce\LaravelBouncer;

use Illuminate\Database\Eloquent\Model;
use Dyce\LaravelBouncer\Contracts\HasAbilities;

class PersonalGameToken extends Model implements HasAbilities
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'provider_id',
        'game_id',
        'type',
        'token',
        'abilities',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Get the tokenable model that the game token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @param string $type
     * @return static|null
     */
    public static function findToken(string $token, string $type = 'auth')
    {
        if (strpos($token, '|') === false) {
            return static::where('token', hash('sha256', $token))->where('type', $type)->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }
    }

    /**
     * Check the given token.
     *
     * @return bool
     */
    public function checkToken()
    {
        if (! $this->isValidGameToken()) {
            return false;
        }

        return $this->supportsTokens($this->tokenable) ? $this->tokenable->withGameToken(
            tap($this->forceFill(['last_used_at' => now()]))->save()
        ) : null;
    }

    /**
     * Determine if the token has a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function can($ability)
    {
        return in_array('*', $this->abilities) ||
               array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function cant($ability)
    {
        return ! $this->can($ability);
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
     * @return bool
     */
    protected function isValidGameToken(): bool
    {
        $expiration = config('bouncer.expiration.'.$this->type);

        $isValid = is_null($this->last_used_at) ?
            ($this->created_at->gt(now()->subMinutes($expiration))) :
            ($this->last_used_at->gt(now()->subMinutes($expiration)));

        if (is_callable(LaravelBouncer::$gameTokenAuthenticationCallback)) {
            $isValid = (bool) (LaravelBouncer::$gameTokenAuthenticationCallback)($this, $isValid);
        }

        return $isValid;
    }
}
