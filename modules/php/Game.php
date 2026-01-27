<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * FiveMinuteDungeonDev implementation : © Remy Bouvard <remy.bouvard@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\FiveMinuteDungeonDev;

use Bga\Games\FiveMinuteDungeonDev\States\PlayerTurn;
use Bga\GameFramework\Components\Counters\PlayerCounter;
use Bga\GameFramework\Components\Deck;

class Game extends \Bga\GameFramework\Table
{
    public static array $CARD_TYPES;

    public PlayerCounter $playerEnergy;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If you want to store any type instead of int, use $this->globals instead.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initGameStateLabels([]); // mandatory, even if the array is empty

        $this->greenDeck = $this->deckFactory->createDeck("green_deck");
        $this->redDeck = $this->deckFactory->createDeck("red_deck");
        $this->yellowDeck = $this->deckFactory->createDeck("yellow_deck");
        $this->blueDeck = $this->deckFactory->createDeck("blue_deck");
        $this->purpleDeck = $this->deckFactory->createDeck("purple_deck");
        $this->monsterDeck = $this->deckFactory->createDeck("monster_deck");

        $this->playerEnergy = $this->bga->counterFactory->createPlayerCounter('energy');

        self::$CARD_TYPES = [
            'sword' => [
                'name' => clienttranslate('Sword'),
                'attributes' => ['sword']
            ],
            'scroll' => [
                'name' => clienttranslate('Scroll'),
                'attributes' => ['scroll']
            ],
            'shield' => [
                'name' => clienttranslate('Shield'),
                'attributes' => ['shield']
            ],
            'arrow' => [
                'name' => clienttranslate('Arrow'),
                'attributes' => ['arrow']
            ],
            'jump' => [
                'name' => clienttranslate('Jump'),
                'attributes' => ['jump']
            ],
            'scroll_double' => [
                'name' => clienttranslate('Double Scroll'),
                'attributes' => ['scroll', 'scroll']
            ],
            'jump_double' => [
                'name' => clienttranslate('Double Jump'),
                'attributes' => ['jump', 'jump']
            ],
            'sword_double' => [
                'name' => clienttranslate('Double Sword'),
                'attributes' => ['sword', 'sword']
            ],
            'shield_double' => [
                'name' => clienttranslate('Double Shield'),
                'attributes' => ['shield', 'shield']
            ],
            'arrow_double' => [
                'name' => clienttranslate('Double Arrow'),
                'attributes' => ['arrow', 'arrow']
            ],
            'sword_shield' => [
                'name' => clienttranslate('Sword Shield'),
                'attributes' => ['sword', 'shield']
            ],
            'sword_arrow' => [
                'name' => clienttranslate('Sword Arrow'),
                'attributes' => ['sword', 'arrow']
            ],
            'sword_jump' => [
                'name' => clienttranslate('Sword Jump'),
                'attributes' => ['sword', 'jump']
            ],
            'sword_scroll' => [
                'name' => clienttranslate('Sword Scroll'),
                'attributes' => ['sword', 'scroll']
            ],
        ];

        // self::$CARD_TYPES = [
        //     1 => [
        //         "card_name" => clienttranslate('Troll'), // ...
        //     ],
        //     2 => [
        //         "card_name" => clienttranslate('Goblin'), // ...
        //     ],
        //     // ...
        // ];

        /* example of notification decorator.
        // automatically complete notification args when needed
        $this->bga->notify->addDecorator(function(string $message, array $args) {
            if (isset($args['player_id']) && !isset($args['player_name']) && str_contains($message, '${player_name}')) {
                $args['player_name'] = $this->getPlayerNameById($args['player_id']);
            }
        
            if (isset($args['card_id']) && !isset($args['card_name']) && str_contains($message, '${card_name}')) {
                $args['card_name'] = self::$CARD_TYPES[$args['card_id']]['card_name'];
                $args['i18n'][] = ['card_name'];
            }
            
            return $args;
        });*/
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
        //       if ($from_version <= 1404301345)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "ALTER TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
        //
        //       if ($from_version <= 1405061421)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "CREATE TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas(int $currentPlayerId): array
    {
        $result = [];
        // WARNING: We must only return information visible by the current player (using $currentPlayerId).

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score` FROM `player`"
        );
        $this->playerEnergy->fillResult($result);

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        return $result;
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        $this->playerEnergy->initDb(array_keys($players), initialValue: 2);

        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.

        // Dummy content.
        // $this->tableStats->init('table_teststat1', 0);
        // $this->playerStats->init('player_teststat1', 0);

        // TODO: Setup the initial game situation here.

        $this->createBlueDeck();
        $this->createRedDeck();
        $this->createPurpleDeck();
        $this->createGreenDeck();
        $this->createYellowDeck();
        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();

        return PlayerTurn::class;
    }

    private function createBlueDeck()
    {
        $cards = [];

        // Swords (Force) – 3
        $cards[] = ['type' => 'sword', 'type_arg' => 0, 'nbr' => 3];

        // Scrolls (Magie) – 9
        $cards[] = ['type' => 'scroll', 'type_arg' => 0, 'nbr' => 9];

        // Shields (Defense) – 5
        $cards[] = ['type' => 'shield', 'type_arg' => 0, 'nbr' => 5];

        // Arrows (Distance) – 7
        $cards[] = ['type' => 'arrow', 'type_arg' => 0, 'nbr' => 7];

        // Jumps (Endurance) – 6
        $cards[] = ['type' => 'jump', 'type_arg' => 0, 'nbr' => 6];

        // Double Scroll – 2
        $cards[] = ['type' => 'scroll_double', 'type_arg' => 1, 'nbr' => 2];

        // Création et mélange
        $this->blueDeck->createCards($cards, 'deck');
        $this->blueDeck->shuffle('deck');
    }

    private function createYellowDeck()
    {
        $cards = [];

        // Swords (Force) – 6
        $cards[] = ['type' => 'sword', 'type_arg' => 0, 'nbr' => 6];

        // Scrolls (Magie) – 8
        $cards[] = ['type' => 'scroll', 'type_arg' => 0, 'nbr' => 8];

        // Shields (Defense) – 9
        $cards[] = ['type' => 'shield', 'type_arg' => 0, 'nbr' => 9];

        // Arrows (Distance) – 6
        $cards[] = ['type' => 'arrow', 'type_arg' => 0, 'nbr' => 6];

        // Jumps (Endurance) – 3
        $cards[] = ['type' => 'jump', 'type_arg' => 0, 'nbr' => 3];

        // Double shield – 2
        $cards[] = ['type' => 'shield_double', 'type_arg' => 1, 'nbr' => 2];

        // Création et mélange
        $this->yellowDeck->createCards($cards, 'deck');
        $this->yellowDeck->shuffle('deck');
    }

    private function createRedDeck()
    {
        $cards = [];

        // Swords (Force) – 5
        $cards[] = ['type' => 'sword', 'type_arg' => 0, 'nbr' => 5];

        // Scrolls (Magie) – 3
        $cards[] = ['type' => 'scroll', 'type_arg' => 0, 'nbr' => 3];

        // Shields (Defense) – 7
        $cards[] = ['type' => 'shield', 'type_arg' => 0, 'nbr' => 7];

        // Arrows (Distance) – 5
        $cards[] = ['type' => 'arrow', 'type_arg' => 0, 'nbr' => 5];

        // Jumps (Endurance) – 6
        $cards[] = ['type' => 'jump', 'type_arg' => 0, 'nbr' => 6];

        // Double Sword – 2
        $cards[] = ['type' => 'sword_double', 'type_arg' => 1, 'nbr' => 2];

        // Double Sword Arrow – 2
        $cards[] = ['type' => 'sword_arrow', 'type_arg' => 1, 'nbr' => 2];

        // Double Sword Jump – 2
        $cards[] = ['type' => 'sword_jump', 'type_arg' => 1, 'nbr' => 2];
        
        // Double Sword Scroll – 2
        $cards[] = ['type' => 'sword_scroll', 'type_arg' => 1, 'nbr' => 2];
        
        // Double Sword Shield – 2
        $cards[] = ['type' => 'sword_shield', 'type_arg' => 1, 'nbr' => 2];

        // Création et mélange
        $this->redDeck->createCards($cards, 'deck');
        $this->redDeck->shuffle('deck');
    }

    private function createPurpleDeck()
    {
        $cards = [];

        // Swords (Force) – 7
        $cards[] = ['type' => 'sword', 'type_arg' => 0, 'nbr' => 7];

        // Scrolls (Magie) – 6
        $cards[] = ['type' => 'scroll', 'type_arg' => 0, 'nbr' => 6];

        // Shields (Defense) – 5
        $cards[] = ['type' => 'shield', 'type_arg' => 0, 'nbr' => 5];

        // Arrows (Distance) – 3
        $cards[] = ['type' => 'arrow', 'type_arg' => 0, 'nbr' => 3];

        // Jumps (Endurance) – 7
        $cards[] = ['type' => 'jump', 'type_arg' => 0, 'nbr' => 7];

        // Double jump – 3
        $cards[] = ['type' => 'jump_double', 'type_arg' => 1, 'nbr' => 3];

        // Création et mélange
        $this->purpleDeck->createCards($cards, 'deck');
        $this->purpleDeck->shuffle('deck');
    }

    private function createGreenDeck()
    {
        $cards = [];

        // Swords (Force) – 4
        $cards[] = ['type' => 'sword', 'type_arg' => 0, 'nbr' => 4];

        // Scrolls (Magie) – 4
        $cards[] = ['type' => 'scroll', 'type_arg' => 0, 'nbr' => 4];

        // Shields (Defense) – 3
        $cards[] = ['type' => 'shield', 'type_arg' => 0, 'nbr' => 3];

        // Arrows (Distance) – 9
        $cards[] = ['type' => 'arrow', 'type_arg' => 0, 'nbr' => 9];

        // Jumps (Endurance) – 7
        $cards[] = ['type' => 'jump', 'type_arg' => 0, 'nbr' => 7];

        // Double arrow – 2
        $cards[] = ['type' => 'arrow_double', 'type_arg' => 1, 'nbr' => 2];

        // Création et mélange
        $this->greenDeck->createCards($cards, 'deck');
        $this->greenDeck->shuffle('deck');
    }

    /**
     * Example of debug function.
     * Here, jump to a state you want to test (by default, jump to next player state)
     * You can trigger it on Studio using the Debug button on the right of the top bar.
     */
    public function debug_goToState(int $state = 3)
    {
        $this->gamestate->jumpToState($state);
    }

    /**
     * Another example of debug function, to easily test the zombie code.
     */
    public function debug_playOneMove()
    {
        $this->bga->debug->playUntil(fn(int $count) => $count == 1);
    }

    /*
    Another example of debug function, to easily create situations you want to test.
    Here, put a card you want to test in your hand (assuming you use the Deck component).

    public function debug_setCardInHand(int $cardType, int $playerId) {
        $card = array_values($this->cards->getCardsOfType($cardType))[0];
        $this->cards->moveCard($card['id'], 'hand', $playerId);
    }
    */
}
