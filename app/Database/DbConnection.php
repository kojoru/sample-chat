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
    FOREIGN KEY (UserId) REFERENCES User(Id)
);

CREATE TABLE Message (
    Id            INTEGER PRIMARY KEY,
    FromUserId    INTEGER,
    ToUserId      INTEGER,
    Value         TEXT,
    SentDate      TEXT,
    FOREIGN KEY (FromUserId) REFERENCES User(Id),
    FOREIGN KEY (ToUserId) REFERENCES User(Id)
)
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

    public function addMessage(int $fromUserId, int $toUserId, string $value)
    {
        $query = $this->connection->prepare("
INSERT INTO Message (FromUserId, ToUserId, Value, SentDate) 
VALUES (:FromUserId, :ToUserId, :Value, datetime('now'));
        ");
        $query->execute(array("FromUserId" => $fromUserId, "ToUserId" => $toUserId, "Value" => $value));

        $query = $this->connection->prepare("
SELECT Id, FromUserId, ToUserId, Value, SentDate FROM Message 
WHERE Id = last_insert_rowid();
        ");
        $query->execute();
        return $query->fetch(PDO::FETCH_NAMED);
    }

    public function getMessages(int $count, int $currentUserId = null, int $otherUserId = null, string $startDate = null, string $endDate = null)
    {
        $query = $this->connection->prepare($this->build_query([
            ['SELECT Id, FromUserId, ToUserId, Value, SentDate'],
            ['FROM Message'],
            [$currentUserId, 'WHERE', '(FromUserId = :CurrentUserId OR ToUserId = :CurrentUserId)'],
            [$otherUserId, 'AND', '(FromUserId = :OtherUserId OR ToUserId = :OtherUserId)'],
            [$startDate, 'AND', 'SentDate > :StartDate'],
            [$endDate, 'AND', 'SentDate < :EndDate'],
            ['ORDER BY SentDate DESC'],
            [$count, 'LIMIT :Count']
        ]));

        $count && $query->bindParam('Count', $count);
        $currentUserId && $query->bindParam('CurrentUserId', $currentUserId);
        $otherUserId && $query->bindParam('OtherUserId', $otherUserId);
        $startDate && $query->bindParam('StartDate', $startDate);
        $endDate && $query->bindParam('EndDate', $endDate);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_NAMED);
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

    // http://www.gabordemooij.com/index.php?p=/tiniest_query_builder
    private function build_query($pieces)
    {
        $sql = '';
        $glue = NULL;
        foreach ($pieces as $piece) {
            $n = count($piece);
            switch ($n) {
                case 1:
                    $sql .= " {$piece[0]} ";
                    break;
                case 2:
                    $glue = NULL;
                    if (!is_null($piece[0])) $sql .= " {$piece[1]} ";
                    break;
                case 3:
                    $glue = (is_null($glue)) ? $piece[1] : $glue;
                    if (!is_null($piece[0])) {
                        $sql .= " {$glue} {$piece[2]} ";
                        $glue = NULL;
                    }
                    break;
            }
        }
        return $sql;
    }

}
