<?php

namespace Dyce\LaravelBouncer;

use Illuminate\Support\Str;

trait HasGameTokens
{
    /**
     * The game token the user is using for the current request.
     *
     * @var \Dyce\LaravelBouncer\Contracts\HasAbilities
     */
    protected $gameToken;

    /**
     * Get the game tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function gameTokens()
    {
        return $this->morphMany(LaravelBouncer::$personalGameTokenModel, 'tokenable');
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param  string  $ability
     * @return bool
     */
    public function gameTokenCan(string $ability)
    {
        return $this->gameToken && $this->gameToken->can($ability);
    }

    /**
     * Create a new personal game token for the user.
     *
     * @param string $name
     * @param int $provider
     * @param string $type
     * @param array $abilities
     * @return \Dyce\LaravelBouncer\NewGameToken
     */
    public function createGameToken(string $name, int $provider, string $type = 'auth', array $abilities = ['*'])
    {
        $token = $this->gameTokens()->create([
            'name' => $name,
            'provider_id' => $provider,
            'type' => $type,
            'token' => hash('sha256', $plainTextToken = Str::uuid()),
            'abilities' => $abilities,
        ]);

        return new NewGameToken($token, $plainTextToken, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Get the game token currently associated with the user.
     *
     * @return \Dyce\LaravelBouncer\Contracts\HasAbilities
     */
    public function currentGameToken()
    {
        return $this->gameToken;
    }

    /**
     * Set the current game token for the user.
     *
     * @param  \Dyce\LaravelBouncer\Contracts\HasAbilities  $gameToken
     * @return $this
     */
    public function withGameToken($gameToken)
    {
        $this->gameToken = $gameToken;

        return $this;
    }
}
