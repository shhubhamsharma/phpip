<?php
class Application_Model_DbTable_Actor extends Zend_Db_Table_Abstract
{
    protected $_name = 'actor';
    protected $_primary = 'ID';
    
    /**
	 * retrieves all actors from actor table filtered by name
	 * $term --search term
	 * $phy_person --whether physical actor or other
	 * *
	 */
	public function getAllActors($term = null, $phy_person = null) {
		$phy_query = "";
		if (isset ( $phy_person )) {
			$phy_query = "AND a.phy_person = " . $phy_person;
		}
		$db = $this->getAdapter ();
		$db->query ( 'SET NAMES utf8' );
		$selectQuery = $db->select ()->from ( array (
				'a' => 'actor' 
		), array (
				'a.id',
				'a.name',
				'a.first_name',
				'a.display_name',
				'a.login' 
		) )->joinLeft ( array (
				'aa' => 'actor' 
		), 'aa.ID = a.company_ID', array (
				'company_name' => 'aa.name' 
		) )->where ( "a.name like '" . $term . "%' " . $phy_query )->order ( 'a.name asc' );
		$result = $db->fetchAll ( $selectQuery );
		foreach ( $result as $key => $actor ) {
			$actor_display = $actor ['name'] . (($actor ['first_name'] == '') ? "" : (", " . $actor ['first_name'])) . (($actor ['display_name']) ? (" (" . $actor ['display_name'] . ")") : "");
			$result [$key] ['value'] = htmlentities ( $actor_display );
		}
		return $result;
	}
	
	/**
	 * retrieves all users from actor table filterted by name
	 * $term --search term
	 */
	public function getAllUsers($term = null) {
		$db = $this->getAdapter ();
		$db->query ( 'SET NAMES utf8' );
		$dbSelect = $db->select ();
		$selectQuery = $dbSelect->from ( 'actor', array (
				'id',
				'name',
				'first_name',
				'display_name',
				'default_role',
				'login',
				'last_login' 
		) )->where ( "name like '" . $term . "%'  AND login is not null" )->order ( 'name asc' );
		$result = $this->getAdapter ()->fetchAll ( $selectQuery );
		foreach ( $result as $key => $actor ) {
			$actor_display = $actor ['name'] . (($actor ['first_name'] == '') ? "" : (", " . $actor ['first_name'])) . (($actor ['display_name']) ? (" (" . $actor ['display_name'] . ")") : "");
			$result [$key] ['value'] = htmlentities ( $actor_display );
		}
		return $result;
	}
	
	/**
	 * retrieves all actors from actor table filtered by company
	 */
	public function getAllActorsByCo($term = null) {
		$db = $this->getAdapter ();
		$db->query ( 'SET NAMES utf8' );
		$selectQuery = $db->select ()->from ( array (
				'a' => 'actor' 
		), array (
				'a.id',
				'a.name',
				'a.first_name',
				'a.display_name',
				'a.login' 
		) )->joinLeft ( array (
				'aa' => 'actor' 
		), 'aa.ID = a.company_ID', array (
				'company_name' => 'aa.name' 
		) )->where ( "aa.name like '" . $term . "%'" )->order ( 'a.name asc' );
		$result = $db->fetchAll ( $selectQuery );
		foreach ( $result as $key => $actor ) {
			$actor_display = $actor ['name'] . (($actor ['first_name'] == '') ? "" : (", " . $actor ['first_name'])) . (($actor ['display_name']) ? (" (" . $actor ['display_name'] . ")") : "");
			$result [$key] ['value'] = htmlentities ( $actor_display );
		}
		return $result;
	}
	
	/**
	 * retrieves all logins from actor table
	 * *
	 */
	public function getAllLogins($term = null) {
		$db = $this->getAdapter ();
		$db->query ( 'SET NAMES utf8' );
		$selectQuery = $db->select ()->from ( array (
				'a' => 'actor' 
		), array (
				'a.id',
				'a.login as value',
				'a.first_name',
				'a.display_name' 
		) )->where ( 'a.login like ? ', $term . '%' )->order ( 'a.login asc' );
		return $db->fetchAll ( $selectQuery );
	}
	
	/**
	 * returns actor_role.name for given actor_role.code
	 * *
	 */
	public function getRoleName($role = 0) {
		if (! $role)
			return false;
		
		$db = $this->getAdapter ();
		$selectQuery = $db->select ()->from ( array (
				'ar' => 'actor_role' 
		), array (
				'ar.code',
				'ar.name' 
		) )->where ( 'ar.code = ? ', $role )->order ( 'ar.name asc' );
		return $db->fetchAll ( $selectQuery );
	}
	
	/**
	 * determines whether a role is shareable
	 *
	 * @return actor_role.shareable
	 *
	 */
	public function isRoleShareable($role = 0) {
		if (! $role)
			return null;
		
		$db = $this->getAdapter ();
		$selectQuery = $db->select ()->from ( array (
				'ar' => 'actor_role' 
		), array (
				'ar.shareable' 
		) )->where ( 'ar.code = ? ', $role );
		$result = $db->fetchRow ( $selectQuery );
		return $result ['shareable'];
	}
	
	/**
	 * retrieves all records from actor_role sorted by name asc
	 * *
	 */
	public function getAllRoles() {
		$db = $this->getAdapter ();
		$selectQuery = $db->select ()->from ( array (
				'ar' => 'actor_role' 
		), array (
				'ar.code',
				'ar.name',
				'ar.shareable' 
		) )->order ( 'ar.name asc' );
		return $db->fetchAll ( $selectQuery );
	}
	
	/**
	 * retrieves all records from actor_role filtered by search term
	 * *
	 */
	public function getRoles($term = '') {
		$db = $this->getAdapter ();
		$selectQuery = $db->select ()->from ( array (
				'ar' => 'actor_role' 
		), array (
				'id' => 'ar.code',
				'value' => 'ar.name',
				'ar.shareable' 
		) )->order ( 'ar.name asc' )->where ( "ar.name like '" . $term . "%'" );
		return $db->fetchAll ( $selectQuery );
	}
	
	/**
	 * retrives a record from actor_role for a given actor_role.code
	 * *
	 */
	public function getActorRoleInfo($role = null) {
		if ($role) {
			$db = $this->getAdapter ();
			
			$selectQuery = $db->select ()->from ( array (
					'ar' => 'actor_role' 
			) )->where ( "ar.code = ?", $role );
			
			return $db->fetchRow ( $selectQuery );
		}
	}
	
	/**
	 * updates a record of actor_role
	 * *
	 */
	public function saveRole($role_code = null, $data = array()) {
		if (! $role_code)
			return false;
		return $this->update ( $data, array (
				'code = ?' => $role_code 
		) );
	}
	
	/**
	 * retrieves full details of an actor
	 * *
	 */
	public function getActorInfo($actor_id = 0) {
		if (! $actor_id)
			return null;
		
		$db = $this->getAdapter ();
		$db->query ( 'SET NAMES utf8' );
		$selectQuery = $db->select ()->from ( array (
				'a' => 'actor' 
		) )->joinLeft ( array (
				'ac' => 'actor' 
		), 'a.company_ID = ac.ID', array (
				'company_name' => 'ac.name' 
		) )->joinLeft ( array (
				'ap' => 'actor' 
		), 'a.parent_ID = ap.ID', array (
				'parent_name' => 'ap.name' 
		) )->joinLeft ( array (
				'as' => 'actor' 
		), 'a.site_ID = as.ID', array (
				'site_name' => 'as.name' 
		) )->joinLeft ( array (
				'ar' => 'actor_role' 
		), 'a.default_role = ar.code', array (
				'drole_name' => 'ar.name' 
		) )->joinLeft ( array (
				'c' => 'country' 
		), 'a.country = c.iso', array (
				'country_name' => 'c.name' 
		) )->joinLeft ( array (
				'cm' => 'country' 
		), 'a.country_mailing = cm.iso', array (
				'country_mailing' => 'cm.name' 
		) )->joinLeft ( array (
				'cb' => 'country' 
		), 'a.country_billing = cb.iso', array (
				'country_billing' => 'cb.name' 
		) )->joinLeft ( array (
				'na' => 'country' 
		), 'a.nationality = na.iso', array (
				'nationality_name' => 'na.name' 
		) )->where ( 'a.ID = ?', $actor_id );
		return $db->fetchRow ( $selectQuery );
	}
	
	/**
	 * updates a field of an actor
	 * updated through in-place edit feature
	 * *
	 */
	public function updateActor($actor_id = null, $field_name = "", $field_value = "") {
		if (! isset ( $actor_id ))
			return false;
		
		$data = array ();
		$data ["$field_name"] = $field_value != "" ? $field_value : NULL;
		
		try {
			$this->update ( $data, array (
					'ID = ?' => $actor_id 
			) );
			return true;
		} catch ( Exception $e ) {
			return $e->getMessage ();
		}
	}
	
	/**
	 * add a new actor
	 * *
	 */
	public function addActor($actor = array()) {
		try {
			$this->insert ( $actor );
			return $this->getAdapter ()->lastInsertID ();
		} catch ( Exception $e ) {
			return $e->getMessage ();
		}
	}
	
	/**
	 * deletes an actor
	 * *
	 */
	public function deleteActor($actor_id = null) {
		if (! $actor_id)
			return;
		
		try {
			$this->delete ( 'ID ='. $actor_id );
			return true;
		} catch ( Exception $e ) {
			return $e->getMessage ();
		}
	}
	
	/**
	 * give permission to a user
	 */
	public function permitUser($actor_id = null) {
		if (! $actor_id)
			return;
		//$this->setDbTable ( 'Application_Model_DbTable_Actor' );
		$dbSelect = $this->getAdapter ()->select ();
		
		$selectQuery = $dbSelect->from ( array (
				'u' => 'actor' 
		) )->where ( 'u.id = ?', $actor_id );
		$row = $this->getAdapter ()->fetchRow ( $selectQuery );
		$passwd = 'changeme';
		$login = $row ['login'];
		$config = Zend_Registry::get ( 'config' );
		$siteInfoNamespace = new Zend_Session_Namespace ( 'siteInfoNamespace' );
		$username = $siteInfoNamespace->username;
		$password = $siteInfoNamespace->password;
		$db_detail = $this->getAdapter ()->getConfig ();
		$host = $db_detail ['host'];
		try {
			$dbm = new PDO ( "mysql:host=$host;dbname=mysql", $username, $password );
		} catch ( Exception $e ) {
			return 'Access denied';
		}
		try {
			$sql1 = "CREATE USER '" . $login . "'@'%' IDENTIFIED BY '" . $passwd . "'";
			$sql2 = "GRANT ALL ON phpip.* TO '" . $login . "'@'%'";
			$dbm->exec ( $sql1 );
			$dbm->exec ( $sql2 );
		} catch ( Exception $e ) {
			return "Unable to insert the user. ";
		}
		//$this->setDbTable ( 'Application_Model_DbTable_Actor' );
		// $data['ipuser']=1;
		$data ['password'] = md5 ( $passwd . 'salt' );
		$data ['password_salt'] = 'salt';
		$this->update ( $data, array (
				'ID = ?' => $actor_id 
		) );
	}
	
	/**
	 * unpermit a user
	 */
	public function banUser($actor_id = null) {
		if (! $actor_id)
			return;
		//$this->setDbTable ( 'Application_Model_DbTable_Actor' );
		$dbSelect = $this->getAdapter ()->select ();
		
		$selectQuery = $dbSelect->from ( array (
				'u' => 'actor' 
		) )->where ( 'u.id = ?', $actor_id );
		$row = $this->getAdapter ()->fetchRow ( $selectQuery );
		$login = $row ['login'];
		$config = Zend_Registry::get ( 'config' );
		$siteInfoNamespace = new Zend_Session_Namespace ( 'siteInfoNamespace' );
		$username = $siteInfoNamespace->username;
		$password = $siteInfoNamespace->password;
		$db_detail = $this->getAdapter ()->getConfig ();
		$host = $db_detail ['host'];
		try {
			$dbm = new PDO ( "mysql:host=$host;dbname=mysql", $username, $password );
		} catch ( Exception $e ) {
			return 'Echec de la connexion à la base de données ' . $e;
		}
		try {
			$sql1 = "DROP USER '" . $login . "'@'%'";
			$dbm->exec ( $sql1 );
		} catch ( Exception $e ) {
			return "Echec de la suppression : " . $e->getMessage ();
		}
		$data ['login'] = null;
		$this->update ( $data, array (
				'ID = ?' => $actor_id 
		) );
	}
	
	/**
	 * actor's Matter Dependencies
	 * *
	 */
	public function getActorMatterDependencies($actor_id = null) {
		if (! $actor_id)
			return;
		
		//$this->setDbTable ( 'Application_Model_DbTable_Actor' );
		$matter_dependencies_stmt = $this->getAdapter ()->query ( "select matter.id,
          CONCAT_WS('', CONCAT_WS('-', CONCAT_WS('/', CONCAT(matter.caseref, matter.country), matter.origin), matter.type_code), matter.idx) AS UID,
          actor_role.name as role
          from  matter, matter_actor_lnk, actor_role
          where matter_actor_lnk.matter_id=matter.id
          and matter_actor_lnk.role=actor_role.code
          and matter_actor_lnk.actor_id=$actor_id" );
		return $matter_dependencies_stmt->fetchAll ();
	}
	
	/**
	 * actor's other Dependencies
	 * *
	 */
	public function getActorOtherActorDependencies($actor_id = null) {
		if (! $actor_id)
			return;
		
		//$this->setDbTable ( 'Application_Model_DbTable_Actor' );
		$other_actor_dependencies_stmt = $this->getAdapter ()->query ( "select id, concat_ws(' ', name, first_name) as Actor,
         (case $actor_id
           when parent_id then 'Parent'
           when company_id then 'Company'
           when site_id then 'Site'
         end) as Dependency
         from actor
         where $actor_id in (parent_id, company_id, site_id)" );
		return $other_actor_dependencies_stmt->fetchAll ();
	}
	
	/**
	 * retrieves column comments defined for a table
	 * *
	 */
	public function getTableComments($table_name = null) {
		if (! isset ( $table_name )) {
			return false;
		}
		
		$infoDb = $this->getInfoDb ();
		$db_detail = $this->getAdapter ()->getConfig ();
		$query = "select column_name, column_comment from columns where table_schema='" . $db_detail ["dbname"] . "' AND table_name='" . $table_name . "'";
		$result = $infoDb->fetchAll ( $query );
		$comments = array ();
		foreach ( $result as $row ) {
			$col_name = $row ['column_name'];
			$comments ["$col_name"] = $row ['column_comment'];
		}
		return $comments;
	}
	
	/**
	 * retrieves enum set defined for a column in a table
	 * *
	 */
	public function getEnumSet($table_name = null, $column_name = null) {
		if (! isset ( $table_name ) && ! isset ( $column_name ))
			return false;
		
		$infoDb = $this->getInfoDb ();
		$db_detail = $this->getAdapter ()->getConfig ();
		$query = "select substring(column_type, 5) as enumset from columns where table_schema='" . $db_detail ["dbname"] . "' AND table_name='" . $table_name . "' and column_name='" . $column_name . "'";
		$result = $infoDb->fetchRow ( $query );
		$enumSet = substr ( $result ['enumset'], 1, - 1 );
		$enumArr = explode ( ",", $enumSet );
		$enums = array ();
		foreach ( $enumArr as $key => $value ) {
			$value = substr ( $value, 1, - 1 );
			$enums ["$value"] = $value;
		}
		return $enums;
	}
	
	/**
	 * Pdo_Mysql connection to database information_schema
	 * *
	 */
	public function getInfoDb() {
		//$this->setDbTable ( 'Application_Model_DbTable_Matter' );
		$db_detail = $this->getAdapter ()->getConfig ();
		
		$db = new Zend_Db_Adapter_Pdo_Mysql ( array (
				'host' => $db_detail ["host"],
				'username' => $db_detail ["username"],
				'password' => $db_detail ["password"],
				'dbname' => 'information_schema' 
		) );
		return $db;
	}
}
