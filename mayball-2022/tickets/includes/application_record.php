<?php

/**
 * Description of application_record
 *
 * @author James
 */
class ApplicationRecord {
    public $app_id = null;
    public $principal_id = null;
    public $status = null;
    public $nominated_pickup = null;
    public $created_at = null;
    public $updated_at = null;
    public $cheque_hash = null;
    public $name_change_block = null;
    public $printed_tickets = 0;

    public static function buildFromApplication($app) {
    	if (get_class($app) != "Application") {
    		return null;
    	}
    	$applicationRecord = new ApplicationRecord();
    	$applicationRecord->app_id = $app->app_id;
    	$applicationRecord->principal_id = $app->principal_id;
    	$applicationRecord->status = $app->status;
    	$applicationRecord->nominated_pickup = $app->nominated_pickup;
    	$applicationRecord->created_at = $app->created_at;
    	$applicationRecord->updated_at = $app->updated_at;
    	$applicationRecord->cheque_hash = $app->cheque_hash;
        $applicationRecord->name_change_block = $app->name_change_block;
        $applicationRecord->printed_tickets = $app->printed_tickets;
    	return $applicationRecord;
    }
}
