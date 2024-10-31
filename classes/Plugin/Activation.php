<?php

namespace Orwokki\PlaceReservations\Plugin;

use Orwokki\PlaceReservations\Places\LocationDirectory;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Places\PlaceTypeDirectory;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;
use Orwokki\PlaceReservations\Reservations\ReservationDirectory;

class Activation
{
	public function doActivation() {
		$this->createDbTables();
	}

	public function doDeactivation() {
		$roles = new RolesAndCapabilities;
		$roles->removeRoles();
	}

	private function createDbTables() {
		$this->createPlaceTypesTable();
		$this->createPeriodTypesTable();
		$this->createLocationsTable();
		$this->createPlacesTable();
		$this->createReservationsTable();
	}

	private function createPlacesTable() {
		global $wpdb;

		$dir = new PlaceDirectory;

		$sql  = "CREATE TABLE ".$dir->getTableName()." (\n";
		$sql .= "  id int NOT NULL AUTO_INCREMENT,\n";
		$sql .= "  generalType varchar(32) NOT NULL,\n";
		$sql .= "  placeTypeId int,\n";
		$sql .= "  periodTypeId int,\n";
		$sql .= "  name varchar(255) NOT NULL,\n";
		$sql .= "  locationId int,\n";
		$sql .= "  length decimal(5,2),\n";
		$sql .= "  width decimal(5,2),\n";
		$sql .= "  depth decimal(5,2),\n";
		$sql .= "  description text,\n";
		$sql .= "  active int NOT NULL,\n";
		$sql .= "  deleted int NOT NULL DEFAULT '0',\n";
		$sql .= "  deleteTime datetime,\n";
		$sql .= "  PRIMARY KEY  (id),\n";
		$sql .= "  KEY generalType (generalType),\n";
		$sql .= "  KEY placeTypeId (placeTypeId),\n";
		$sql .= "  KEY periodTypeId (periodTypeId),\n";
		$sql .= "  KEY active (active),\n";
		$sql .= "  KEY deleted (deleted)\n";
		$sql .= ") ".$wpdb->get_charset_collate().";";

		dbDelta($sql);
	}

	private function createReservationsTable() {
		global $wpdb;

		$dir = new ReservationDirectory;

		$sql  = "CREATE TABLE ".$dir->getTableName()." (\n";
		$sql .= "  id int NOT NULL AUTO_INCREMENT,\n";
		$sql .= "  placeId int NOT NULL,\n";
		$sql .= "  name varchar(255) NOT NULL,\n";
		$sql .= "  email varchar(255),\n";
		$sql .= "  phoneNumber varchar(255),\n";
		$sql .= "  periodTypeId int,\n";
		$sql .= "  periodStartTime datetime NOT NULL,\n";
		$sql .= "  periodEndTime datetime,\n";
		$sql .= "  additionalInfo text,\n";
		$sql .= "  active int(1) NOT NULL,\n";
		$sql .= "  approved int(1) NOT NULL,\n";
		$sql .= "  approveTime datetime,\n";
		$sql .= "  deleted int NOT NULL DEFAULT '0',\n";
		$sql .= "  deleteTime datetime,\n";
		$sql .= "  PRIMARY KEY  (id),\n";
		$sql .= "  KEY active (active),\n";
		$sql .= "  KEY deleted (deleted),\n";
		$sql .= "  KEY approved (approved),\n";
		$sql .= "  KEY periodStartTime (periodStartTime),\n";
		$sql .= "  KEY periodTypeId (periodTypeId)\n";
		$sql .= ") ".$wpdb->get_charset_collate().";";

		dbDelta($sql);
	}

	private function createPlaceTypesTable() {
		global $wpdb;

		$dir = new PlaceTypeDirectory;

		$sql  = "CREATE TABLE ".$dir->getTableName()." (\n";
		$sql .= "  id int NOT NULL AUTO_INCREMENT,\n";
		$sql .= "  generalType varchar(32),\n";
		$sql .= "  name varchar(255) NOT NULL,\n";
		$sql .= "  active int(1) NOT NULL,\n";
		$sql .= "  deleted int NOT NULL DEFAULT '0',\n";
		$sql .= "  deleteTime datetime,\n";
		$sql .= "  PRIMARY KEY  (id),\n";
		$sql .= "  KEY active (active),\n";
		$sql .= "  KEY deleted (deleted),\n";
		$sql .= "  KEY generalType (generalType)\n";
		$sql .= ") ".$wpdb->get_charset_collate().";";

		dbDelta($sql);
	}

	private function createLocationsTable() {
		global $wpdb;

		$dir = new LocationDirectory;

		$sql  = "CREATE TABLE ".$dir->getTableName()." (\n";
		$sql .= "  id int NOT NULL AUTO_INCREMENT,\n";
		$sql .= "  generalType varchar(32),\n";
		$sql .= "  name varchar(255) NOT NULL,\n";
		$sql .= "  active int(1) NOT NULL,\n";
		$sql .= "  deleted int NOT NULL DEFAULT '0',\n";
		$sql .= "  deleteTime datetime,\n";
		$sql .= "  PRIMARY KEY  (id),\n";
		$sql .= "  KEY active (active),\n";
		$sql .= "  KEY deleted (deleted)\n";
		$sql .= ") ".$wpdb->get_charset_collate().";";

		dbDelta($sql);
	}

	private function createPeriodTypesTable() {
		global $wpdb;

		$dir = new PeriodTypeDirectory;

		$sql  = "CREATE TABLE ".$dir->getTableName()." (\n";
		$sql .= "  id int NOT NULL AUTO_INCREMENT,\n";
		$sql .= "  name varchar(255) NOT NULL,\n";
		$sql .= "  active int(1) NOT NULL,\n";
		$sql .= "  deleted int NOT NULL DEFAULT '0',\n";
		$sql .= "  deleteTime datetime,\n";
		$sql .= "  PRIMARY KEY  (id),\n";
		$sql .= "  KEY active (active),\n";
		$sql .= "  KEY deleted (deleted)\n";
		$sql .= ") ".$wpdb->get_charset_collate().";";

		dbDelta($sql);
	}
}