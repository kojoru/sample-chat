<?php

namespace SampleChat\Database;

use PDO;

class DbConnection
{


    /* @var PDO */
    private $connection;

    private const DATABASE_PATH = "../database/chat.sqlite";

    function __construct()
    {
        $should_initialise = !file_exists(self::DATABASE_PATH);
        $this->connection = new PDO('sqlite:' . self::DATABASE_PATH);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($should_initialise) {
            $this->initialise();
        }
    }

    private function initialise()
    {
        $this->connection->exec("
CREATE TABLE User (
    Id            INTEGER PRIMARY KEY, 
    Name          TEXT,
    CreatedDate   TEXT
);

CREATE TABLE Token (
    Id            INTEGER PRIMARY KEY, 
    UserId        INTEGER, 
    Secret        TEXT, 
    CreatedDate   TEXT, 
    LastUsedDate  TEXT, 
    FOREIGN KEY (UserId) REFERENCES Users(Id)
);
       ");
    }

    public function login(string $userName)
    {
        $user = $this->getUserByName($userName);
        if (!$user) {
            $user = $this->createUser($userName);
        }
        return $this->createToken($user['Id'])['Secret'];

    }

    public function getAllUsers()
    {
        $query = $this->connection->prepare("
SELECT User.Id, User.Name, User.CreatedDate, MAX(Token.LastUsedDate) AS LastOnlineDate
FROM User LEFT JOIN Token ON Token.UserId = User.Id
GROUP BY User.Id, User.Name, User.CreatedDate
ORDER BY LastOnlineDate DESC
        ");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_NAMED);
    }

    /**
     * Gets user if the token is valid, otherwise returns null
     * @param string $secret
     * @return mixed|null
     */
    public function authoriseToken(string $secret)
    {
        try {
            $this->connection->beginTransaction();
            $user = $this->getUserByToken($secret);
            if ($user) {
                $this->updateLastOnlineDateForToken($secret);
            }
            $this->connection->commit();
            return $user;
        } catch (\Exception $exception) {
            $this->connection->rollBack();
        }
        return null;
    }

    /**
     * Returns an existing user
     * @param string $userName
     * @return mixed
     */
    private function getUserByName(string $userName)
    {
        $query = $this->connection->prepare("
SELECT Id, Name, CreatedDate
FROM User 
WHERE Name = :UserName
        ");
        $query->execute(array("UserName" => $userName));
        return $query->fetch(PDO::FETCH_NAMED);
    }

    private function getUserByToken(string $secret)
    {
        $query = $this->connection->prepare("
SELECT User.Id, User.Name, User.CreatedDate
FROM User JOIN Token ON User.Id = Token.UserId
WHERE Token.Secret = :Secret
        ");
        $query->execute(array("Secret" => $secret));
        return $query->fetch(PDO::FETCH_NAMED);
    }

    private function updateLastOnlineDateForToken(string $secret): void
    {
        $query = $this->connection->prepare("
UPDATE Token SET LastUsedDate = datetime('now')
WHERE Secret = :Secret
        ");
        $query->execute(array("Secret" => $secret));
    }

    private function createUser(string $name)
    {
        $query = $this->connection->prepare("
INSERT INTO User (Name, CreatedDate) 
VALUES (:UserName, date('now'))
        ");
        $query->execute(array("UserName" => $name));


        $query = $this->connection->prepare("
SELECT Id, Name, CreatedDate FROM User 
WHERE Id = last_insert_rowid();
        ");
        $query->execute();
        return $query->fetch(PDO::FETCH_NAMED);
    }

    private function createToken(int $userId)
    {
        $secret = bin2hex(random_bytes(16));

        $query = $this->connection->prepare("
INSERT INTO Token (UserId, Secret, CreatedDate, LastUsedDate) 
VALUES (:UserId, :Secret, datetime('now'), datetime('now'));
        ");
        $query->execute(array("UserId" => $userId, "Secret" => $secret));

        $query = $this->connection->prepare("
SELECT Id, UserId, Secret, CreatedDate, LastUsedDate FROM Token 
WHERE Id = last_insert_rowid();
        ");
        $query->execute();
        return $query->fetch(PDO::FETCH_NAMED);
    }


}
