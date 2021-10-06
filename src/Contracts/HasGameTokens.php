<?php

namespace Dyce\LaravelBouncer\Contracts;

interface HasGameTokens
{
    /**
     * Get the game tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function gameTokens();

    /**
     * Determine if the current game token has a given scope.
     *
     * @param  string  $ability
     * @return bool
     */
    public function gameTokenCan(string $ability);

    /**
     * Create a new personal game token for the user.
     *
     * @param string $name
     * @param string $type
     * @param array $abilities
     * @return \Dyce\LaravelBouncer\NewGameToken
     */
    public function createGameToken(string $name, string $type = 'auth', array $abilities = ['*']);

    /**
     * Get the game token currently associated with the user.
     *
     * @return \Dyce\LaravelBouncer\Contracts\HasAbilities
     */
    public function currentGameToken();

    /**
     * Set the current game token for the user.
     *
     * @param  \Dyce\LaravelBouncer\Contracts\HasAbilities  $gameToken
     * @return \Dyce\LaravelBouncer\Contracts\HasGameTokens
     */
    public function withGameToken($gameToken);
}
