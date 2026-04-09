We're building a real-time browser game inspired by the popular YouTube travel show: JetLag the Game.

## Core game loop

Our goal is to compete with one or more players to claim as many train stations as possible on a map (it could be a real country or a randomly generated map, TBD). 

Claiming a station is done by visiting that station and depositing a number of coins to it. The other player can claim the station as their own by depositing a larger amount of coins into it. 

Each player starts with a number of coins, and additional coins are collected by traveling the raillines to complete challenges along the way.

### Traveling

You can only travel from one station to connected ones (there may be some intersections where multiple lines connect, but in general we're often dealing with long lines of connected stations). Travel to another station also takes time (seconds, at most).

When traveling to a station, every visited station must be claimed. This means you can't freely navigate the whole map, but instead are limited by the amount of coins you have.

### Claiming stations

A station can be claimed by depositing coins into it. In order to do so, the player must be present at the station they want to claim. The more coins that are deposited into a station, the stronger its position to guard against other players. Players can only travel through a claimed station by claiming it as their own, which means depositing a larger number of coins into the station. Coin counters persist between claims, so when the original player wants to reclaim a station, they can do so by adding enough coins on top of their original deposit. 

Let's say that one station has 5 coins deposited by player A, then player B can only travel through it by depositing 6 coins or more into it. Let's say that player B deposited 6 coins, then player A can reclaim this station by depositing an additional 2 coins (on top of their original 5).

Players can never deposit more than 5 coins on top of the highest coin count for any given station. They also cannot deposit more into a station they already own. This prevents stations from becoming so expensive that they would completely block off other players.

### Coin collecting

Each player starts with a number of coins, and they can collect more coins by completing challenges. For now, we'll keep the challenge part very simple, and say that when they visit a challenge, they will complete it.

Visiting a challenge means as much as visiting a train station with an active challenge. Whenever a challenge is completed, one to three new challenges will appear at other random stations.

In the future, we will improve this part, but for now we'll keep it very simple.

## Technical specs

This game runs in the browser, but ideally runs fully on stateless HTTP requests. All game logic is handled on the server in the backend (PHP and Tempest framework). Since it's a rather slow-paced game with short waiting periods between train stations (which can be considered as "loading time in disguise"), we don't need any sub-second real-time communication between players.

One fun idea is to be able to procudurally generate the maps, based on a seed and the amount of players playing a game. 

When it comes to visuals on the client-side, I'm completely clueless. The game is essentially a railroad map (similar to subway maps). Think of it as a tabletop game visually. 