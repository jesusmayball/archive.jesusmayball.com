<?php

global $databaseConfig;

/**
 * A class for connecting to and querying the database.
 * It contains some more generic database functions (such as executing an sql query
 * and getting error messages), and higher-level retrieval of May Ball data.
 *
 * @author James G
 */
class Database extends PDO
{

    /**
     * This class implements the Singleton design pattern,
     * so this stores a reference to the single database object.
     * @var type
     */
    private static $dbInstance;
    private static $CLASS_ERROR_MESSAGE_TEMPLATE = "Incorrect object supplied, required %s";
    private static $CLASS_MISSING_FIELD_TEMPLATE = "%s is missing %s field";

    /**
     * This is the function which must be called before any other functions,
     * to create (or get) an instance of the database, and to make a connection.
     * @return Database
     */
    public static function getInstance()
    {
        if (!self::$dbInstance) {
            self::$dbInstance = new Database();
        }
        return self::$dbInstance;
    }

    //Connects to the database
    public function __construct()
    {
        global $databaseConfig;
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        try {
            parent::__construct('mysql:host=' . $databaseConfig["host"] . ';dbname=' . $databaseConfig["dbName"] . ';charset=utf8', $databaseConfig["username"], $databaseConfig["password"], $options);
        } catch (PDOException $e) {
            printf("<div><span style=\"font-weight:bold\">Database error:</span> %s</div>\n", "An error occurred while connecting.");
            printf("<div><span style=\"font-weight:bold\">MySQL Error</span>: %s (%s)</div>\n", $e->getCode(), $e->getMessage());
            exit("Session halted.");
        }
    }

    /**
     * Retrieve all the information related to a particular ticket application.
     * This can be identified by the application ID, a ticket ID, a ticket hash code
     * or a person's CRSID (specified in the first argument). It returns an Application
     * object containing all information. If an error occured, or the application could
     * not be found, will return false. Use getErrno() and getError() to discover the problem.
     * @param string $idField - May be one of app_id, ticket_id, hash_code, or crsid.
     * @param mixed $fieldValue - The value of the preferred ID to search.
     * @return boolean|\Application
     */
    //TODO: I think this can be heavily optimised.
    public function getAppInfo($idField, $fieldValue)
    {
        if ($idField == "cheque_hash") {
            $sql = "SELECT * FROM applications WHERE cheque_hash = '$fieldValue'";
            $args = array(":cheque_hash" => $fieldValue);
        } else {
            if ($idField == "crsid" || $idField == "email" || $idField == "ticket_id" || $idField == "hash_code") {
                //Safe because of the matching above
                $query = $this->prepare("SELECT app_id FROM tickets WHERE " . $idField . " = :field_value");
                $query->execute(array(
                    ":field_value" => $fieldValue
                ));
                $ticket = $query->fetch();
                if (!$ticket) {
                    return null;
                }
                $appID = $ticket->app_id;
            } else if ($idField == "app_id") {
                $appID = $fieldValue;
            } else {
                return null;
            }
            $sql = "SELECT * FROM applications WHERE app_id = :app_id";
            $args = array(":app_id" => $appID);
        }
        $query = $this->prepare($sql);
        $query->execute($args);
        $appRow = $query->fetch();
        if (!$appRow) {
            return null;
        }
        $application = new Application();
        $application->app_id = $appRow->app_id;
        $application->principal_id = $appRow->principal_id;
        $application->status = $appRow->status;
        $application->nominated_pickup = $appRow->nominated_pickup;
        $application->created_at = $appRow->created_at;
        $application->updated_at = $appRow->updated_at;
        $application->cheque_hash = $appRow->cheque_hash;
        $application->printed_tickets = $appRow->printed_tickets;
        // $application->is_refunded = $appRow->is_refunded;
        //$application->name_change_block = $appRow->name_change_block;

        $query = $this->prepare("SELECT ticket_id FROM tickets WHERE app_id = :app_id");
        $query->execute(array(":app_id" => $appRow->app_id));
        $idArray = array();
        foreach ($query->fetchAll() as $ticket) {
            $idArray[] = $ticket->ticket_id;
        }

        $application->guests = array();
        $totalCharity = 0;
        $totalCost = 0;
        foreach ($idArray as $ticketID) {
            $ticket = $this->getTicketInfo("ticket_id", $ticketID);
            if ($ticketID == $application->principal_id) {
                $application->principal = $ticket;
            } else {
                $application->guests[] = $ticket;
            }
            $totalCost += $ticket->price + $ticket->charity_donation;
            $totalCharity += $ticket->charity_donation;
        }
        $application->totalCost = $totalCost;
        $application->charity = $totalCharity;

        $query = $this->prepare("SELECT payment_id FROM payments WHERE app_id = :app_id AND valid = 1");
        $query->execute(array(":app_id" => $appRow->app_id));

        $idArray = array();
        foreach ($query->fetchAll() as $idRow) {
            $idArray[] = $idRow->payment_id;
        }

        $application->payments = array();
        $totalPaid = 0;
        foreach ($idArray as $paymentID) {
            $payment = $this->getPaymentInfo($paymentID);
            $application->payments[] = $payment;
            $totalPaid += $payment->amount;
        }
        $application->totalPaid = $totalPaid;

        return $application;
    }

    /**
     * Returns an array of all the applications containing basic information
     */
    public function getAllApplications()
    {
        $query = $this->prepare("SELECT * FROM applications ORDER BY `app_id`");
        $query->execute();
        $applications = $query->fetchAll();

        $applicationsArray = array();

        foreach ($applications as $application) {
            $applicationsArray[$application->app_id] = array("status" => $application->status);
        }
        //TODO - Serialize application objects instead
        //XXX: Are you kidding me? This is so wasteful! This can be done with a single query
        foreach ($applicationsArray as $key => $value) {
            $fullApplication = $this->getAppInfo("app_id", $key);
            $principal = $fullApplication->principal;
            if ($principal !== NULL) {
                $principalName = $principal->title . " " . $principal->first_name . " " . $principal->last_name;
                $applicationsArray[$key]["app_id"] = $key;
                $applicationsArray[$key]["created_at"] = $fullApplication->created_at;
                $applicationsArray[$key]["principal_id"] = $fullApplication->principal_id;
                $applicationsArray[$key]["totalPaid"] = $fullApplication->totalPaid;
                $applicationsArray[$key]["totalCost"] = $fullApplication->totalCost;
                $applicationsArray[$key]["status"] = $fullApplication->status;
                $applicationsArray[$key]["cheque_hash"] = $fullApplication->cheque_hash;
                $applicationsArray[$key]["printed_tickets"] = $fullApplication->printed_tickets;
                $applicationsArray[$key]["principalName"] = $principalName;
                $applicationsArray[$key]["principalValid"] = $fullApplication->principal->valid;
                $applicationsArray[$key]["principalEmail"] = $fullApplication->principal->email;
                $applicationsArray[$key]["principalTicketTypeID"] = $fullApplication->principal->ticket_type_id;
                $applicationsArray[$key]["tickets"] = sizeof($fullApplication->guests) + 1;
            }
        }
        return array("applications" => $applicationsArray);
    }

    /**
     * Returns an array of all the applications containing basic information
     */
    public function getAllTickets()
    {
        $tickets = array("tickets" => array());
        $query = $this->prepare("SELECT tickets.*, apps.printed FROM tickets JOIN " .
            "(SELECT app_id, printed_tickets AS printed FROM applications) AS apps ON apps.app_id = tickets.app_id " .
            "ORDER BY `ticket_id`");
        $query->execute();
        foreach ($query->fetchAll() as $ticket) {
            $tickets["tickets"][$ticket->ticket_id] = $ticket;
        }
        return $tickets;
    }

    public function ticketSearch($field, $term)
    {
        $tickets = array("tickets" => array());

        if ($field == "email") {
            //finds record being searched for, as well as all those that share the app_id
            $sql = "SELECT DISTINCT tickets.*, apps.printed FROM `tickets` AS tickets JOIN
                    (SELECT app_id, printed_tickets AS printed FROM applications) AS apps ON apps.app_id = tickets.app_id JOIN
                    (SELECT DISTINCT `app_id` FROM `tickets` WHERE `crsid` LIKE CONCAT('%', :term, '%') OR `email` LIKE CONCAT('%', :term, '%'))
                    AS idtable ON tickets.app_id = idtable.app_id ORDER BY tickets.app_id, tickets.ticket_id ASC";
        } elseif ($field == "name") {
            //finds whole application like above
            //also allows full name search
            $sql = "SELECT DISTINCT tickets.*, apps.printed FROM `tickets` AS tickets JOIN
                    (SELECT app_id, printed_tickets AS printed FROM applications) AS apps ON apps.app_id = tickets.app_id JOIN
                    (SELECT DISTINCT `app_id` FROM `tickets` AS appIDs JOIN
                        (SELECT ticket_id, CONCAT(first_name, ' ', last_name) AS full_name FROM `tickets`)
                        AS names ON appIDs.ticket_id = names.ticket_id
                    WHERE `full_name` LIKE CONCAT('%', :term, '%')) AS idtable
                    ON tickets.app_id = idtable.app_id ORDER BY tickets.app_id, tickets.ticket_id ASC";
        } elseif ($field == "app_id" || $field == "ticket_id") {
            $sql = "SELECT tickets.*, apps.printed FROM `tickets` JOIN
            (SELECT app_id, printed_tickets AS printed FROM applications) AS apps ON apps.app_id = tickets.app_id
            WHERE tickets.$field = :term";
        } elseif ($field == "phone" || $field == "hash_code") {
            $sql = "SELECT tickets.*, apps.printed FROM `tickets` JOIN
            (SELECT app_id, printed_tickets AS printed FROM applications) AS apps ON apps.app_id = tickets.app_id
            WHERE `$field` LIKE CONCAT('%', :term, '%') ORDER BY `ticket_id`";
        } else {
            return null;
        }
        $query = $this->prepare($sql);
        $query->execute(array(
            ":term" => $term
        ));

        foreach ($query->fetchAll() as $i => $ticket) {
            $tickets["tickets"][$i] = $ticket;
        }
        return $tickets;
    }

    /**
     * Inserts a new application row into the database. This will be blank except for the
     * `status` and `created_at` fields, so these need NOT be updated when adding data
     * to the new row. `status` is initialised to 'created', and `created_at` is
     * initialised to the current date and time.
     * @return boolean True or false whether the insertion completed successfully.
     */
    public function insertBlankApp()
    {
        $this->beginTransaction();
        $query = $this->prepare("SELECT * FROM applications where `cheque_hash` = :cheque_hash");
        $query->bindParam(":cheque_hash", $chequeHash);

        $chequeHash = ApplicationUtils::generateHash();
        $query->execute();
        while ($query->fetch()) {
            $chequeHash = ApplicationUtils::generateHash();
            $query->execute();
        }

        $query = $this->prepare("INSERT INTO applications (status, created_at, cheque_hash) VALUES('created', NOW(), :cheque_hash)");
        $query->execute(array(
            ":cheque_hash" => $chequeHash
        ));

        $id = $this->lastInsertId();
        $this->commit();
        return $id;
    }

    /**
     * Given an ApplicationRecord object (NOT an Application object), updates the associated application on the database.
     * To use, first construct an ApplicationRecord object. Then fill in only the fields which
     * should be updated on the database (including the app_id field to
     * identify the application). All others will be left as they are on the system.
     * @param ApplicationRecord $appRecord - An ApplicationRecord object representing the information to be updated
     * @return boolean True if the update succeeded, false if an SQL error occured, the object
     * provided was not ApplicationRecord, or an identifier field was not provided.
     * @throws DatabaseException
     */
    public function updateApp($appRecord)
    {
        $class = "ApplicationRecord";
        if (get_class($appRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        if ($appRecord->app_id === null) {
            throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "app_id"));
        }

        $sqlChunks = array();
        $preparedVariables = array(":app_id" => $appRecord->app_id);
        foreach (get_object_vars($appRecord) as $field => $value) {
            if (($value !== null) && ($field != "app_id")) {
                if ($value === "NOW()") {
                    $setString = $field . " = NOW()";
                } else {
                    $setString = $field . " = :" . $field;
                    $preparedVariables[":" . $field] = $value;
                }
                $sqlChunks[] = $setString;
            }
        }

        $sql = "UPDATE applications SET ";
        $arrayLen = count($sqlChunks);
        for ($i = 0; $i < $arrayLen; $i++) {
            $sql .= $sqlChunks[$i];
            if ($i != $arrayLen - 1) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE app_id = :app_id";

        $query = $this->prepare($sql);
        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update application: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }
    }

    public function getAppRecord($appID)
    {
        $query = $this->prepare("SELECT * FROM `applications` WHERE `app_id` = :app_id");
        $query->execute(array(
            ":app_id" => $appID
        ));

        $appRow = $query->fetch();
        if (!$appRow) {
            return null;
        }
        $app = new ApplicationRecord();
        $app->app_id = $appRow->app_id;
        $app->principal_id = $appRow->principal_id;
        $app->status = $appRow->status;
        $app->nominated_pickup = $appRow->nominated_pickup;
        $app->created_at = $appRow->created_at;
        $app->updated_at = $appRow->updated_at;
        $app->cheque_hash = $appRow->cheque_hash;
        $app->name_change_block = $appRow->name_change_block;
        $app->printed_tickets = $appRow->printed_tickets;
        return $app;
    }

    //FOR GOD SAKE! THIS SHOULD BE USING USING DATABSE CONSTRAINTS
    public function deleteApp($appID)
    {
        $query = $this->prepare("DELETE FROM applications WHERE app_id = :app_id");
        $query->execute(array(
            ":app_id" => $appID
        ));
        $query = $this->prepare("DELETE FROM tickets WHERE app_id = :app_id");
        $query->execute(array(
            ":app_id" => $appID
        ));
    }

    /**
     * Gets all of the info associated with a particular ticket, wrapped up in a Ticket object.
     * This can be identified by a ticket ID or a ticket hash code.
     * @param string $idField - The field that you'd like to select on, so for tickets you can choose by 'ticket_id' or 'hash_code'
     * @param mixed $fieldValue - The value of the ID field specified (so the ticket ID or the ticket hash code)
     * @return boolean|\TTicket- false if there was an error, otherwise a TicketInfo object containing all the details.
     */
    public function getTicketInfo($idField, $fieldValue)
    {
        if ($idField == "ticket_id" || $idField == "email" || $idField == "hash_code") {
            $sql = "SELECT tickets.*, ticket_types.ticket_type FROM tickets INNER JOIN ticket_types ON tickets.ticket_type_id = ticket_types.ticket_type_id WHERE $idField = :field_value";
            $query = $this->prepare($sql);
            $query->execute(array(
                ":field_value" => $fieldValue
            ));

            $row = $query->fetch();
            if ($row) {
                $ticket = new Ticket();
                foreach (get_object_vars($ticket) as $field => $value) {
                    //TODO jakub - check
                    // if ($field == "valid" || $field == "deleted" || $field == "charity_donation") {
                    if ($field == "valid" || $field == "deleted") {
                        if ($row->$field) {
                            $ticket->$field = "1";
                        } else {
                            $ticket->$field = "0";
                        }
                    } else {
                        $ticket->$field = $row->$field;
                    }
                }
                return $ticket;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Inserts a new ticket into the database given a TicketRecord object
     * @param TicketRecord $ticketRecord - A TicketRecord object representing the information to be updated
     * @return string $ticketId - The ticket_id of the ticket added 
     */
    public function insertTicket($ticketRecord, $nullCounts = false)
    {
        $class = "TicketRecord";
        if (get_class($ticketRecord) != $class) {
            error_log("Wrong class type");
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }

        if ($ticketRecord->first_name) {
            $ticketRecord->first_name = trim($ticketRecord->first_name);
        }
        if ($ticketRecord->last_name) {
            $ticketRecord->last_name = trim($ticketRecord->last_name);
        }

        $this->beginTransaction();
        $query = $this->prepare("SELECT * FROM tickets where `hash_code` = :hash_code");
        $query->bindParam(":hash_code", $hashCode);

        $hashCode = ApplicationUtils::generateHash();
        $query->execute();
        while ($query->fetch()) {
            $hashCode = ApplicationUtils::generateHash();
            $query->execute();
        }

        $ticketRecord->hash_code = $hashCode;

        $fieldString = "(";
        $valuesString = "(";
        $preparedVariables = array(":hash_code" => $hashCode);
        $first = true;
        foreach (get_object_vars($ticketRecord) as $field => $value) {
            if ($value === null) {
                if ($nullCounts) {
                    $fieldString .= ($first ? "" : ", ") . $field;
                    $valuesString .= ($first ? ":" : ", :") . $field;
                    $preparedVariables[":" . $field] = "NULL";
                } else {
                    continue;
                }
            } else {
                $fieldString .= ($first ? "" : ", ") . $field;
                if ($value === "NOW()") {
                    $valuesString .= ($first ? ":" : ", NOW()");
                } else {
                    $valuesString .= ($first ? ":" : ", :") . $field;
                    $preparedVariables[":" . $field] = $value;
                }
            }
            $first = false;
        }
        $fieldString .= ")";
        $valuesString .= ")";


        $sql = "INSERT INTO tickets " . $fieldString . " VALUES " . $valuesString;
        $query = $this->prepare($sql);
        $query->execute($preparedVariables);

        $id = $this->lastInsertId();
        $this->commit();
        return $id;
    }

    /**
     * Given a TicketRecord object (NOT a Ticket object), updates the associated ticket on the database.
     * To use, first construct a TicketRecord object. Then fill in only the fields which
     * should be updated on the database (including the ticket_id or hash_code fields to
     * identify the ticket). All others will be left as they are on the system.
     * @param TicketRecord $ticketRecord - A TicketRecord object representing the information to be updated
     * @param boolean $nullCounts - If true, the null fields will be added to the record as NULL.
     * @return boolean True if the update succeeded, false if an SQL error occured, the object
     * provided was not TicketRecord, or an identifier field was not provided.
     * @throws DatabaseException
     */
    public function updateTicket($ticketRecord, $nullCounts = false)
    {
        $class = "TicketRecord";
        if (get_class($ticketRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        $preparedVariables = array();

        if ($ticketRecord->ticket_id === null && $ticketRecord->hash_code === null) {
            if ($ticketRecord->ticket_id === null) {
                throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "ticket_id"));
            } else {
                throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "hash_code"));
            }
        } else {
            if ($ticketRecord->ticket_id !== null) {
                $finalStatement = " WHERE ticket_id = :ticket_id";
                $preparedVariables = array(
                    ":ticket_id" => $ticketRecord->ticket_id,
                );
            } else {
                $finalStatement = " WHERE hash_code = :hash_code";
                $preparedVariables = array(
                    ":hash_code" => $ticketRecord->hash_code
                );
            }
        }

        if ($ticketRecord->first_name) {
            $ticketRecord->first_name = trim($ticketRecord->first_name);
        }
        if ($ticketRecord->last_name) {
            $ticketRecord->last_name = trim($ticketRecord->last_name);
        }
        if (!$ticketRecord->is_refunded) {
            $ticketRecord->is_refunded = "0";
        }
        if (!$ticketRecord->is_ballot) {
            $ticketRecord->is_ballot = "0";
        }
        $ticketRecord->updated_at = "NOW()";

        $updateString = "";
        $first = true;
        foreach (get_object_vars($ticketRecord) as $field => $value) {
            if ($field === "ticket_hash_code" || $field === "ticket_id") continue;
            if ($value === null) {
                if ($nullCounts) {
                    if (!$first) {
                        $updateString .= ", ";
                    }
                    $updateString .= $field . " = " . "NULL";
                } else {
                    continue;
                }
            } else {
                if ($value === 0 || $value === false) {
                    $value === "0";
                }
                if (!$first) {
                    $updateString .= ", ";
                }
                if ($value == "NOW()") {
                    $updateString .= $field . " = NOW()";
                } else {
                    $updateString .= $field . " = :" . $field;
                    $preparedVariables[":" . $field] = $value;
                }
            }
            $first = false;
        }

        $sql = "UPDATE tickets SET " . $updateString . $finalStatement;
        $query = $this->prepare($sql);
        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update ticket: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }
    }
    public function markTicketCheckedIn($ticketId)
    {
        $query = $this->prepare('UPDATE tickets SET entered_via = "true" WHERE ticket_id = :ticket_id');
        $query->execute(array(":ticket_id" => $ticketId));
    }

    /**
     * Deletes a specified row from the tickets table. Does not delete or update anything else,
     * so be sure to remove any dangling references from other tables.
     * @param int $ticketID - The ticket ID of the row you want to delete.
     * @return boolean True or false whether the deletion completed successfully.
     */
    public function deleteTicketSimple($ticketId)
    {
        $query = $this->prepare("DELETE FROM tickets WHERE ticket_id = :ticket_id");
        $query->execute(array(
            ":ticket_id" => $ticketId
        ));
    }

    public function getTicketByFullName($fullName, $useTitle)
    {
        $title = $useTitle ? "title, " : "";
        $sql = sprintf("SELECT * FROM (SELECT CONCAT_WS(' ', %sfirst_name, last_name) AS full_name, tickets.* FROM tickets) AS tickets_full WHERE full_name LIKE :full_name", $title);

        $query = $this->prepare($sql);
        $query->execute(array(
            ":full_name" => $fullName
        ));

        $row = $query->fetch();
        if (!$row) {
            return null;
        }
        $ticket = new Ticket();
        foreach (get_object_vars($ticket) as $field => $value) {
            $ticket->$field = $row->$field;
        }
        return $ticket;
    }

    public function setTicketsValid($appId)
    {
        $query = $this->prepare("UPDATE tickets SET valid = 1 WHERE app_id = :app_id");
        $query->execute(array(
            ":app_id" => $appId
        ));
    }

    /**
     * Gets an array of ticket types (indexed by ticket_type_id).
     * @param boolean $availableOnly - Specify whether it is required that only available ticket_types are returned.
     * @param boolean $useClasses - Specify whether ticket counts of parent classes should reflect
     * the counts of their children ticket types. Eg. if false, ticket_count of Priority might
     * give 120. If true, ticket_count of Priority would give 150, to account for the 30 fellows etc
     * who have Priority tickets.
     * @return array of TicketType - False if the query failed, otherwise an array of TicketType objects.
     */
    public function getAllTicketTypes($availableOnly = true, $useClasses = false, $includeRestricted = false, $countValidOnly = false, $countIsBallot = false)
    {
        $sql = "SELECT ticket_types.ticket_type_id, ticket_type, price, available, restricted, maximum, use_other_max, IFNULL(ticket_count, 0) AS ticket_count " .
            "FROM ticket_types LEFT JOIN (" .
            "SELECT ticket_type_id, COUNT(1) AS ticket_count " .
            "FROM tickets WHERE deleted != 1 ";
        $sql .= !$countIsBallot ? "AND is_ballot = 0 " : "";
        $sql .= $countValidOnly ? "AND valid = 1 " : "";
        $sql .= "GROUP BY ticket_type_id" .
            ") AS ticket_counts " .
            "ON ticket_types.ticket_type_id = ticket_counts.ticket_type_id " .
            "ORDER BY ticket_types.ticket_type_id";

        $query = $this->prepare($sql);
        $query->execute();

        $ticketTypesTemp = array();
        foreach ($query->fetchAll() as $row) {
            $ticketType = new TicketType();
            foreach (get_object_vars($ticketType) as $field => $value) {
                $ticketType->$field = $row->$field;
            }
            $ticketTypesTemp[$ticketType->ticket_type_id] = $ticketType;
        }

        if ($useClasses) {
            foreach ($ticketTypesTemp as $typeInfo) {
                if ($typeInfo->use_other_max != 0) {
                    $ticketTypesTemp[$typeInfo->use_other_max]->ticket_count += $typeInfo->ticket_count;
                }
            }
        }

        $ticketTypes = array();
        foreach ($ticketTypesTemp as $id => $ticketType) {
            if (($availableOnly && $ticketType->available) || !$availableOnly) {
                if ($includeRestricted || !$ticketType->restricted) {
                    $ticketTypes[$id] = $ticketType;
                }
            }
        }
        return $ticketTypes;
    }

    /**
     * Gets the information about a particular ticket type.
     * @param int $ticket_type_id
     * @param boolean $useClasses - Specify whether to return the ticket count of only this ticket type (false),
     * or whether to return the count including its child ticket types (true). See doc of getAllTicketTypes().
     * @return TicketType - null if the query failed, otherwise a TicketType object.
     */
    public function getTicketTypeInfo($ticket_type_id, $useClasses = false)
    {
        $ticket_types = $this->getAllTicketTypes(false, $useClasses, true);
        if (isset($ticket_types[$ticket_type_id])) {
            return $ticket_types[$ticket_type_id];
        } else {
            return null;
        }
    }

    /**
     * Inserts a new completely blank ticket_type row into the database.
     * @return boolean True or false whether the insertion completed successfully.
     */
    public function insertBlankTicketType()
    {
        $query = $this->prepare("INSERT INTO ticket_types VALUES ()");
        $query->execute();
        return $this->lastInsertId();
    }



    /**
     * Given a TicketTypeRecord object (NOT a TicketType object), updates the associated ticket on the database.
     * To use, first construct a TicketTypeRecord object. Then fill in only the fields which
     * should be updated on the database (including the ticket_type_id field to
     * identify the ticket type). All others will be left as they are on the system.
     * @param TicketTypeRecord $ticketTypeRecord - A TicketTypeRecord object representing the information to be updated
     * @return boolean True if the update succeeded, false if an SQL error occured, the object
     * provided was not TicketTypeRecord, or an identifier field was not provided.
     */
    public function updateTicketType($ticketTypeRecord)
    {
        $class = "TicketTypeRecord";
        if (get_class($ticketTypeRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        if ($ticketTypeRecord->ticket_type_id === null) {
            throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "ticket_type_id"));
        }

        $sqlChunks = array();
        $preparedVariables = array(":ticket_type_id" => $ticketTypeRecord->ticket_type_id);
        foreach (get_object_vars($ticketTypeRecord) as $field => $value) {
            if (($value !== null) && ($field != "ticket_type_id")) {
                $setString = $field . " = :" . $field;
                $preparedVariables[":" . $field] = $value;
                $sqlChunks[] = $setString;
            }
        }

        $sql = "UPDATE ticket_types SET ";
        $arrayLen = count($sqlChunks);
        for ($i = 0; $i < $arrayLen; $i++) {
            $sql .= $sqlChunks[$i];
            if ($i != $arrayLen - 1) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE ticket_type_id = :ticket_type_id";

        $query = $this->prepare($sql);
        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update ticket type: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }
    }

    public function insertTicketType($ticketTypeRecord)
    {
        $class = "TicketTypeRecord";
        if (get_class($ticketTypeRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }

        $preparedVariables = array();
        $fieldString = "(";
        $valuesString = "(";
        $first = true;
        foreach (get_object_vars($ticketTypeRecord) as $field => $value) {
            $fieldString .= ($first ? "" : ", ") . $field;
            $valuesString .= ($first ? ":" : ", :") . $field;
            $preparedVariables[":" . $field] = $value;
            $first = false;
        }
        $fieldString .= ")";
        $valuesString .= ")";

        $sql = "INSERT INTO ticket_types " . $fieldString . " VALUES " . $valuesString . ";";
        $query = $this->prepare($sql);

        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update ticket type: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }

        $id = $this->lastInsertId();
        return $id;
    }

    public function deleteTicketTypeSimple($ticketTypeID)
    {
        $query = $this->prepare("DELETE FROM ticket_types WHERE ticket_type_id = :ticket_type_id");
        $query->execute(array(
            ":ticket_type_id" => $ticketTypeID
        ));
    }

    public function ticketTotals()
    {
        $sql = "SELECT COUNT(1) AS total, " .
            "SUM(price) AS revenue, " .
            "SUM(charity_donation) AS charity, " .
            "SUM(printed_program) AS printed_program," .
            "SUM(printed_tickets) AS printed_tickets " .
            "FROM tickets JOIN applications ON tickets.app_id = applications.app_id " .
            "WHERE deleted != 1";

        $query = $this->prepare($sql);
        $query->execute();

        $row = $query->fetch();
        $totals["total-bought"] = $row->total;
        $totals["revenue-bought"] = $row->revenue;
        $totals["charity-bought"] = $row->charity;
        //total number of printed tickets
        $totals["printed-tickets"] = $row->printed_tickets;
        $totals["printed-programs"] = $row->printed_program;

        $sql = "SELECT COUNT(1) AS total, " .
            "SUM(price) AS revenue, " .
            "SUM(charity_donation) AS charity " .
            "FROM tickets WHERE deleted != 1 AND valid = 1";
        $query = $this->prepare($sql);
        $query->execute();

        $row = $query->fetch();
        $totals["total-paid"] = $row->total;
        $totals["revenue-paid"] = $row->revenue;
        $totals["charity-paid"] = $row->charity;

        return $totals;
    }

    /**
     * Returns an array of all the payments containing basic information
     */
    public function getAllPayments()
    {
        $payments = array("payments" => array());
        $query = $this->prepare("SELECT * FROM payments ORDER BY `payment_id`");
        $query->execute();
        foreach ($query->fetchAll() as $payment) {
            $payments["payments"][$payment->payment_id] = $payment;
        }
        return $payments;
    }

    public function paymentIsDuplicate($hash)
    {
        $query = $this->prepare("SELECT * FROM payments WHERE hash = :hash");
        $query->execute(array(
            ":hash" => $hash
        ));
        if (!$query->fetch()) return false;
        return true;
    }

    public function getPaymentInfo($paymentID)
    {
        $query = $this->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
        $query->execute(array(
            ":payment_id" => $paymentID
        ));

        $row = $query->fetch();
        if (!$row) {
            return null;
        }
        $payment = new PaymentRecord();
        foreach (get_object_vars($payment) as $field => $value) {
            $payment->$field = $row->$field;
        }
        return $payment;
    }

    public function insertPayment($app, $amount, $method, $valid, $notes, $hash = null)
    {
        $query = $this->prepare("INSERT INTO payments (app_id, amount, method, valid, notes, created_at, hash) VALUES (:app, :amount, :method, :valid, :notes, NOW(), :hash)");
        $query->execute(
            array(
                ":app" => $app,
                ":amount" => $amount,
                ":method" => $method,
                ":valid" => $valid,
                ":notes" => $notes,
                ":hash" => $hash == null ? "NULL" : $hash
            )
        );
        $id = $this->lastInsertId();

        $payment = new PaymentRecord();
        $payment->payment_id = $id;
        $payment->notes = $notes;

        $this->updatePayment($payment);
    }

    //TODO: Remove
    public function updatePayment($paymentRecord)
    {
        $class = "PaymentRecord";
        if (get_class($paymentRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        if ($paymentRecord->payment_id === null) {
            throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "payment_id"));
        }

        $sqlChunks = array();
        $preparedVariables = array(":payment_id" => $paymentRecord->payment_id);
        foreach (get_object_vars($paymentRecord) as $field => $value) {
            if (($value !== null) && ($field != "payment_id")) {
                $setString = $field . " = :" . $field;
                $preparedVariables[":" . $field] = $value;
                $sqlChunks[] = $setString;
            }
        }

        $sql = "UPDATE payments SET ";
        $arrayLen = count($sqlChunks);
        for ($i = 0; $i < $arrayLen; $i++) {
            $sql .= $sqlChunks[$i];
            if ($i != $arrayLen - 1) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE payment_id = :payment_id";
        $query = $this->prepare($sql);
        $query->execute($preparedVariables);
        //It should not be possible to throw an exception here
    }

    /**
     * Returns an array of all the waiting list entries containing basic information
     */
    public function getAllWaitingListEntries()
    {
        $result = array("waiting_list" => array());
        $query = $this->prepare("SELECT * FROM waiting_list ORDER BY `waiting_list_id`");
        $query->execute();
        foreach ($query->fetchAll() as $wl) {
            $result["waiting_list"][$wl->waiting_list_id] = $wl;
        }
        return $result;
    }

    public function getWaitingListEntry($wlid)
    {
        $query = $this->prepare("SELECT * FROM waiting_list WHERE waiting_list_id = :waiting_list_id");
        $query->execute(array(
            ":waiting_list_id" => $wlid
        ));

        $row = $query->fetch();
        if (!$row) {
            return null;
        } else {
            $waitinglist = new WaitingListRecord();
            foreach (get_object_vars($waitinglist) as $field => $value) {
                $waitinglist->$field = $row->$field;
            }
            return $waitinglist;
        }
    }

    /**
     * Inserts a blank waiting list entry.
     * @return id of new waiting list or false if failed.
     */
    public function insertBlankWaitingList()
    {
        $query = $this->prepare("INSERT INTO waiting_list (created_at) VALUES (NOW())");
        $query->execute();
        return $this->lastInsertId();
    }

    public function insertWaitingList($waitingRecord)
    {
        $class = "WaitingListRecord";
        if (get_class($waitingRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }

        $waitingRecord->created_at = "NOW()";
        $waitingRecord->honoured = false;

        $preparedVariables = array();
        $fieldString = "(";
        $valuesString = "(";
        $first = true;
        foreach (get_object_vars($waitingRecord) as $field => $value) {
            if ($value == null) continue;
            if ($value == "NOW()") {
                $fieldString .= ($first ? "" : ", ") . $field;
                $valuesString .= ($first ? "NOW()" : ", NOW()");
            } else {
                $fieldString .= ($first ? "" : ", ") . $field;
                $valuesString .= ($first ? ":" : ", :") . $field;
                $preparedVariables[":" . $field] = $value;
                $first = false;
            }
        }
        $fieldString .= ")";
        $valuesString .= ")";

        $sql = "INSERT INTO waiting_list " . $fieldString . " VALUES " . $valuesString . ";";
        $query = $this->prepare($sql);

        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update ticket type: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }

        $id = $this->lastInsertId();
        return $id;
    }

    public function updateWaitingList($waitingRecord, $nullCounts = false)
    {
        $class = "WaitingListRecord";
        if (get_class($waitingRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        if ($waitingRecord->waiting_list_id === null) {
            throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "waiting_list_id"));
        }

        $sqlChunks = array();
        $preparedVariables = array(":waiting_list_id" => $waitingRecord->waiting_list_id);
        foreach (get_object_vars($waitingRecord) as $field => $value) {
            if ($field != "waiting_list_id") {
                if ($value === null) {
                    if ($nullCounts) {
                        $setString = $field . " = NULL";
                    } else {
                        continue;
                    }
                } else {
                    $setString = $field . " = :" . $field;
                    $preparedVariables[":" . $field] = $value;
                }
                $sqlChunks[] = $setString;
            }
        }

        $sql = "UPDATE waiting_list SET ";
        $arrayLen = count($sqlChunks);
        for ($i = 0; $i < $arrayLen; $i++) {
            $sql .= $sqlChunks[$i];
            if ($i != $arrayLen - 1) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE waiting_list_id = :waiting_list_id";

        $query = $this->prepare($sql);
        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update waiting list: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }
    }

    public function deleteWaitingList($waitingListID)
    {
        $query = $this->prepare("DELETE FROM waiting_list WHERE waiting_list_id = :waiting_list_id");
        $query->execute(array(":waiting_list_id" => $waitingListID));
    }

    /**
     * Returns an array of all the payments containing basic information
     */
    public function getAllNameChanges()
    {
        $nameChanges = array("name_changes" => array());
        $query = $this->prepare("SELECT * FROM name_changes");
        $query->execute();
        foreach ($query->fetchAll() as $nameChange) {
            $nameChanges["name_changes"][$nameChange->name_change_id] = $nameChange;
        }
        return $nameChanges;
    }

    public function getNameChange($nameChangeID)
    {
        $query = $this->prepare("SELECT * FROM name_changes WHERE name_change_id = :name_change_id");
        $query->execute(array(":name_change_id" => $nameChangeID));
        $namechange = $query->fetch();
        if (!$namechange) {
            return NULL;
        }
        $change = new NameChangeRecord();
        foreach (get_object_vars($change) as $field => $value) {
            $change->$field = $namechange->$field;
        }
        return $change;
    }

    public function insertNameChange($nameChangeRecord)
    {
        $class = "NameChangeRecord";
        if (get_class($nameChangeRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }

        $nameChangeRecord->created_at = "NOW()";
        $nameChangeRecord->secret = str_shuffle(MD5(microtime()));

        $fieldString = "(";
        $valuesString = "(";
        $preparedVariables = array();
        $first = true;
        foreach (get_object_vars($nameChangeRecord) as $field => $value) {
            if ($value === null) {
                continue;
            } else {
                $fieldString .= ($first ? "" : ", ") . $field;
                if ($value === "NOW()") {
                    $valuesString .= ($first ? ":" : ", NOW()");
                } else {
                    $valuesString .= ($first ? ":" : ", :") . $field;
                    $preparedVariables[":" . $field] = $value;
                }
            }
            $first = false;
        }
        $fieldString .= ")";
        $valuesString .= ")";


        $sql = "INSERT INTO name_changes " . $fieldString . " VALUES " . $valuesString;
        $query = $this->prepare($sql);
        $query->execute($preparedVariables);
        $id = $this->lastInsertId();
        return $id;
    }

    public function insertBlankNameChange()
    {
        $this->beginTransaction();
        $query = $this->prepare("SELECT * FROM name_changes WHERE name_change_id = :id AND secret = :secret");
        $query->bindParam(":id", $id);
        $query->bindParam(":secret", $secret);

        $id = rand(1000, 9999);
        $secret = str_shuffle(MD5(microtime()));

        $query->execute();
        while ($query->fetch()) {
            $id = rand(1000, 9999);
            $secret = str_shuffle(MD5(microtime()));
            $query->execute();
        }

        $query = $this->prepare("INSERT INTO name_changes (name_change_id, secret, created_at) VALUES (:id, :secret, NOW())");
        $query->execute(array(
            ":id" => $id,
            ":secret" => $secret
        ));

        $id = $this->lastInsertId();
        $this->commit();
        return $id;
    }

    public function updateNameChange($changeRecord)
    {
        $class = "NameChangeRecord";
        if (get_class($changeRecord) != $class) {
            throw new DatabaseException(sprintf($this->CLASS_ERROR_MESSAGE_TEMPLATE, $class));
        }
        if ($changeRecord->name_change_id === null) {
            throw new DatabaseException(sprintf($this->CLASS_MISSING_FIELD_TEMPLATE, $class, "name_change_id"));
        }

        $sqlChunks = array();
        $preparedVariables = array(":name_change_id" => $changeRecord->name_change_id);
        foreach (get_object_vars($changeRecord) as $field => $value) {
            if (($value !== null) && ($field != "name_change_id")) {
                $setString = $field . " = :" . $field;
                $preparedVariables[":" . $field] = $value;
                $sqlChunks[] = $setString;
            }
        }

        $sql = "UPDATE name_changes SET ";
        $arrayLen = count($sqlChunks);
        for ($i = 0; $i < $arrayLen; $i++) {
            $sql .= $sqlChunks[$i];
            if ($i != $arrayLen - 1) {
                $sql .= ", ";
            }
        }

        $sql .= " WHERE name_change_id = :name_change_id";
        $query = $this->prepare($sql);
        try {
            $query->execute($preparedVariables);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new DatabaseException(sprintf("Unable to update name change: %s", $e->errorInfo[2]));
            } else {
                throw $e;
            }
        }
    }

    /**
     * Returns an array of all the emails.
     */
    public function getAllEmails()
    {
        $emails = array();

        $query = $this->prepare("SELECT * FROM emails ORDER BY `id`");
        $query->execute();
        //Superfluous since they're objects coming back
        foreach ($query->fetchAll() as $email) {
            $emailRecord = new Email();
            $emailRecord->id = $email->id;
            $emailRecord->name = $email->name;
            $emailRecord->appOrTicket = $email->appOrTicket;
            $emailRecord->builtIn = $email->builtIn;
            $emailRecord->subject = $email->subject;
            $emailRecord->body = $email->body;
            $emailRecord->defaultBody = $email->defaultBody;
            $emailRecord->defaultSubject = $email->defaultSubject;
            $emails[$email->id] = $emailRecord;
        }
        return $emails;
    }

    /**
     * Returns an email by name, otherwise false
     */
    public function getEmailByName($name)
    {
        $query = $this->prepare("SELECT * FROM emails WHERE `name` = :name");
        $query->execute(array(":name" => $name));
        return $query->fetch();
    }

    /**
     * Returns an email by name, otherwise false
     */
    public function getEmail($id)
    {
        $query = $this->prepare("SELECT * FROM emails WHERE `id` = :id");
        $query->execute(array(":id" => $id));
        return $query->fetch();
    }
}

class DatabaseException extends Exception
{

    public function __construct($message, Exception $e = null)
    {
        parent::__construct($message, 0, $e);
    }
}
