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
     * @param int|null $game
     * @param string $type
     * @param bool $dashes
     * @param array $abilities
     * @return \Dyce\LaravelBouncer\NewGameToken
     */
    public function createGameToken(string $name, int $provider, int $game = null, string $type = 'auth', bool $dashes = true, array $abilities = ['*'])
    {
        if ($dashes) {
            $plainTextToken = Str::uuid();
        } else {
            $plainTextToken = Str::uuid()->getHex();
        }
        $token = $this->gameTokens()->create([
            'name' => $name,
            'provider_id' => $provider,
            'game_id' => $game,
            'type' => $type,
            'token' => hash('sha256', $plainTextToken),
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
