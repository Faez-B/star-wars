
# Star Wars Galaxy of Heroes (SWGOH)

Star Wars Galaxy of Heroes (SWGOH) is a mobile game developed by Capital Games. The game revolves around collecting the most iconic heroes/ships from the Star Wars universe to use them in individual or collective game modes. In SWGOH, each player is identified by an ally code and can be a part of a guild. The goal here is to develop an application that enables viewing a player's hero/ship collection using their ally code and saving this information into a database.
The app is dockerised and is intented to be run that way.

## Features

- Viewing a player's info
- Viewing a player's Hero/Ship collection
- Search a player by its ally code
- Viewing a Hero/Ship's info
- Using an ally code, save player's and guild's info to the database


## Run Locally

Clone the project

```bash
  git clone https://github.com/Faez-B/star-wars.git
```

Go to the project directory

```bash
  cd star-wars
```

Run via Docker

```bash
  docker compose up --build
```


<!-- ## Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`API_KEY`

`ANOTHER_API_KEY` -->


## API Reference

#### Get player's guild info

```http
  GET /api/{allyCode}/guild
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `allyCode` | `integer` | **Required**. Player's ally code |

This route allows, using a player's ally code, to obtain various information regarding the guild to which the player belongs.

#### Returns
- Unique guild identifier
- Guild name
- Galactic Power
- Number of players in the guild.

#### Save player's and guild's info into DB

```http
  POST /api/{allyCode}/create
```
```http
  PUT /api/{allyCode}/update
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `allyCode`      | `integer` | **Required**. Player's ally code |

This route enables saving a player's and their guild's information in the database.




<!-- ## Running Tests

To run tests, run the following command

```bash
  npm run test
``` -->


## Tech Stack

**Client:** Twig, Bootstrap

**Server:** Symfony, MySQL

**Infra:** Docker
