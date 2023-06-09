<?php
/**
 * DestinationTable.php - Class assists in handling Destination Table related operations
 * Written by: James West - westj4@csp.edu - April, 2023
 */
include_once "Table.php";

class DestinationTable extends Table
{
    /**
     * @param DBHandler $dbh
     * @param Destination $destination
     * @return bool
     */
    public function add(DBHandler $dbh, Destination $destination): bool
    {
        $conn = $dbh->getConn();
        $sql = <<<SQL
            CALL addDestination(?, ?, ?, ?, ?, ?, ?, ?)
        SQL;
        $name = $destination->getName();
        $desc = $destination->getDescription();
        $img = $destination->getImageUrl();
        $web = $destination->getWebsite();
        $zip = $destination->getZip();
        $l1 = $destination->getLine1();
        $l2 = $destination->getLine2();
        $city = $destination->getCity();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $desc, $img, $web, $zip, $l1, $l2, $city);
        $stmt->execute();
        $stmt->close();
        $dbh->resetConn();
        return true;
    }

    private string $name = "destination";

    /**
     * @param mysqli $conn
     * @return bool
     */
    public static function addTable(mysqli $conn): bool
    {
        $sql = <<<SQL
            CREATE TABLE destination(
                destination_id int NOT NULL AUTO_INCREMENT,
                destination_name varchar(50) NOT NULL UNIQUE,
                destination_desc varchar(5000) NOT NULL,
                image_url varchar(300),
                website varchar(300),
                zip varchar(35) NOT NULL,
                line_1 varchar(95) NOT NULL,
                line_2 varchar(95) NOT NULL,
                city varchar(35) NOT NULL,
                PRIMARY KEY (destination_id)
        )
        SQL;
        return $conn->query($sql);
    }

    /**
     * @param DBHandler $dbh
     * @param Destination $destination
     * @return bool
     */
    public function delete(DBHandler $dbh, Destination $destination): bool
    {
        $conn = $dbh->getConn();
        $sql = <<<SQL
            CALL deleteDestination({$destination->getId()});
        SQL;
        $destination->clear();
        return $conn->query($sql);
    }

    /**
     * @param DBHandler $dbh
     * @param int $id
     * @return Destination
     */
    public function getById(DBHandler $dbh, int $id): Destination
    {
        $conn = $dbh->getConn();
        $stmt = $conn->prepare(
            <<<SQL
            SELECT *, LENGTH(destination_desc) AS 'len' FROM destination WHERE destination_id = ?
            SQL
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $dbh->resetConn();
        if ($data) {
            return new Destination($data['destination_id'], $data['destination_name'], $data['destination_desc'], $data['zip'],
                $data['line_1'], $data['line_2'], $data['city'], $data['image_url'], $data['website']);
        } else {
            return new Destination();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param DBHandler $dbh
     * @return array
     */
    public function getOptions(DBHandler $dbh): array
    {
        $conn = $dbh->getConn();
        $result = $conn->query("SELECT destination_id, destination_name FROM destination ORDER BY destination_name");
        if (!$result) {
            echo "<br>Destination table options error: $conn->error";
            return [];
        }
        return $result->fetch_all(1);
    }

    /**
     * @param DBHandler $dbh
     * @param Destination $destination
     * @return bool
     */
    public function update(DBHandler $dbh, Destination $destination): bool
    {
        $conn = $dbh->getConn();
        $desc = addslashes($destination->getDescription());
        $sql = <<<SQL
            CALL updateDestination(?,?,?,?,?,?,?,?,?)
        SQL;

        $stmt = $conn->prepare($sql);
        $id = $destination->getId();
        $name = $destination->getName();
        $imageUrl = $destination->getImageUrl();
        $website = $destination->getWebsite();
        $zip = $destination->getZip();
        $line1 = $destination->getLine1();
        $line2 = $destination->getLine2();
        $city = $destination->getCity();

        $stmt->bind_param("sssssssss", $id, $name, $desc, $imageUrl, $website, $zip, $line1, $line2, $city);
        if ($stmt->error) {
            $stmt->close();
            return false;
        }
        $stmt->execute();
        $stmt->close();
        $dbh->resetConn();
        return true;
    }
}