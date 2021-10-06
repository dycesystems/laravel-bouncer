<?php

namespace Dyce\LaravelBouncer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class NewGameToken implements Arrayable, Jsonable
{
    /**
     * The game token instance.
     *
     * @var \Dyce\LaravelBouncer\PersonalGameToken
     */
    public $gameToken;

    /**
     * The plain text version of the token.
     *
     * @var string
     */
    public $plainTextToken;

    /**
     * The plain text version of the token with ID prepend.
     *
     * @var string
     */
    public $plainTextTokenId;

    /**
     * Create a new game token result.
     *
     * @param \Dyce\LaravelBouncer\PersonalGameToken $gameToken
     * @param string $plainTextToken
     * @param string $plainTextTokenId
     */
    public function __construct(PersonalGameToken $gameToken, string $plainTextToken, string $plainTextTokenId)
    {
        $this->gameToken = $gameToken;
        $this->plainTextToken = $plainTextToken;
        $this->plainTextTokenId = $plainTextTokenId;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'gameToken' => $this->gameToken,
            'plainTextToken' => $this->plainTextToken,
            'plainTextTokenId' => $this->plainTextTokenId,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
