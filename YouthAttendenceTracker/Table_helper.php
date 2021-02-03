<?php
	/*****************************************************
	 * Class Attendee
	 *
	 * Description:  Extends the DBTable class and defines a
	 *	table attendee in the attendencetracker db.
	 *	The constructor sets the fields and gets the class'
	 *	values.
	 *
	 ****************************************************/
	class Attendee extends DBTable
	{
		/*********************************************
		 * Constructor
		 *
		 * Descriptio: Sets the fields of table and stores
		 *	in inherited array.  Sets the field types.
		 *	Then uses function to get values from form or
		 *	db table.  Has an optional parameter $id that
		 *	allows an id to be input to change the way 
		 *	attendee gets the values...gets values from db
		 *	instead of form.
		 *
		 * Input variables: db object to pass to parent constructor,
		 *	array that holds the values of fields, optional id
		 *
		 * Return type: n/a
		 *********************************************/
		function __construct( $db, $elements, $id='NULL' )
		{
			// first call the parent constructor passing the name of the table for this child class
			parent::__construct($db, 'attendee');
			
			// call initialize_fields to initialize the attendee fields
			$this->initialize_fields();
			
			// call initialize_field_types to initialize the attendee field types
			$this->initialize_field_types();			
						
			/**
			   Check for $id, if NULL then get values from form
			   If not NULL then get values from db row corresponding
			   to $id input.
			 **/
			if ( $id == 'NULL' ) // default, get values from form
				$this->get_values_from_form($elements);
			else
				$this->get_values_from_db($id);
		}
		
		/*********************************************
		 * function initialize_felds
		 *
		 * Description: Creates the fields array and initializes
		 *	the fields corresponding to db columns.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_fields ( )
		{
			$attendee_fields = array();
			
			$attendee_fields[0] = "FirstName";
			$attendee_fields[1] = "LastName";
			$attendee_fields[2] = "MiddleName";
			$attendee_fields[3] = "NickName";
			$attendee_fields[4] = "StreetAddress";
			$attendee_fields[5] = "Zip";
			$attendee_fields[6] = "HomePhone";
			$attendee_fields[7] = "CellPhone";
			$attendee_fields[8] = "CanReceiveTxt";
			$attendee_fields[9] = "Facebook";
			$attendee_fields[10] = "Myspace";
			$attendee_fields[11] = "Occupation";
			$attendee_fields[12] = "WorkPhone";
			$attendee_fields[13] = "PictureURL";
			$attendee_fields[14] = "Email";
			$attendee_fields[15] = "FavoriteThings";
			$attendee_fields[16] = "PicturePolicy";
			$attendee_fields[17] = "ReleaseForm";
			$attendee_fields[18] = "ReleaseFormUpdate";
			$attendee_fields[19] = "TShirtSize";
			$attendee_fields[20] = "BroughtBy";
			$attendee_fields[21] = "Sex";
			$attendee_fields[22] = "Ethnicity";
			$attendee_fields[23] = "DOB";
			$attendee_fields[24] = "SubDiscriminator";
			$attendee_fields[25] = "DateSaved";
			$attendee_fields[26] = "DateBaptized";
			$attendee_fields[27] = "DateHolyGhost";
			$attendee_fields[28] = "Notes";
			$attendee_fields[29] = "Active";
			
			$this->fields = $attendee_fields;
		}
		
		/*********************************************
		 * function initialize_feld_types
		 *
		 * Description: Creates the field_types array and initializes
		 *	the field types corresponding to fields.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_field_types ( )
		{
			$fields_type = array();
			
			$fields_type[0] = "s";
			$fields_type[1] = "s";
			$fields_type[2] = "s";
			$fields_type[3] = "s";
			$fields_type[4] = "s";
			$fields_type[5] = "s";
			$fields_type[6] = "s";
			$fields_type[7] = "s";
			$fields_type[8] = "s";
			$fields_type[9] = "s";
			$fields_type[10] = "s";
			$fields_type[11] = "s";
			$fields_type[12] = "s";
			$fields_type[13] = "s";
			$fields_type[14] = "s";
			$fields_type[15] = "s";
			$fields_type[16] = "s";
			$fields_type[17] = "s";
			$fields_type[18] = "s";
			$fields_type[19] = "s";
			$fields_type[20] = "s";
			$fields_type[21] = "s";
			$fields_type[22] = "s";
			$fields_type[23] = "s";
			$fields_type[24] = "s";
			$fields_type[25] = "s";
			$fields_type[26] = "s";
			$fields_type[27] = "s";
			$fields_type[28] = "s";
			$fields_type[29] = "s";
			
			$this->field_type = $fields_type;
		}
		
		/*********************************************
		 * function is_valid
		 *
		 * Description: Overridden abstract function from parent
		 *	that describes which data must be valid and is called
		 *	before submitting any data to a db.  FirstName, LastName,
		 *	Set, and DOB must be set and not NULL or empty.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function is_valid ( )
		{
			// variable to hold whether the tests for validity have passed
			$valid = true;  // start at true since no test have failed yet
			
			// Test that necessary values are set and do not equal NULL; if so, set
			// $valid_values to true.  Necessary values are:  FirstName, LastName,
			// Sex, and DOB.  Use get_value_by_field to get the value to test.
			// Add any errors to the $error_message variable
			if ( $this->get_value_by_field('FirstName') == 'NULL' ) 
			{
				$valid = false;
				$this->error("FirstName", "First name cannot be blank.");
			}
			
			if ( $this->get_value_by_field('LastName') == 'NULL' )
			{
				$this->error("LastName", "Last name cannot be blank.");
				$valid = false;
			}
			
			if ( $this->get_value_by_field('Sex') == 'NULL' )
			{
				$this->error("Sex", "Sex cannot be blank.");
				$valid = false;
			}
			
			if ( $this->get_value_by_field('DOB') == 'NULL' )
			{
				$this->error("DOB", "DOB cannot be blank.");
				$valid = false;
			}
			
			if ( $valid )  // all tests passed so set valid_values to true
				$this->valid_values = true;	
			
			// return value of valid_values to calling function; default for valid values 
			// is false, so unless changed by successful testing in function it will be false
			return $this->valid_values;
		}
			
		/*********************************************
		 * function exists
		 *
		 * Description: Overridden abstract function from parent
		 *	that checks the db to make sure that the current
		 *	object does not already exist in the table.  
		 *	This is deteremined by whether FirstName, LastName,
		 *	and DOB are identical to a record in the table.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function exists ( )
		{
			// Build the SQL query to check for existing records that match FirstName, LastName, and DOB
			$select_query = "SELECT FirstName, LastName, DOB FROM attendee WHERE DOB = '" . $this->get_value_by_field('DOB') . "' AND FirstName = '" . $this->get_value_by_field('FirstName') . "' AND LastName = '" . $this->get_value_by_field('LastName') . "';";
			// execute the query against the db and check that it was successful and that there was no matching rows returned 
			if ( $result = $this->db->query($select_query) )  
			{
				if ( $result->num_rows != 0 )  // there was a result to the query
				{
					// first add to the error message
					$str .= "Record already exists with fields:  " . $this->get_value_by_field('FirstName') . "  " . $this->get_value_by_field('LastName') . "  " . $this->get_value_by_field('DOB');   
					$this->error('Alert', $str);  // Use Alert as field name to create unique error message
					return true;          // return true since a record already exists
				}
				else                     // no rows return from query
					return false;    // so return false, record does not exist
			}	
			
			// if query failed, return an error and true as default since it is possible that the record does exist in the table
			$this->error('Alert', 'Record might already exist in table.');  // Use Alert as field name to create unique error message
			return true;                                                    // return true since a record possibly exists
		}
	}
	
	/*****************************************************
	 * Class Student
	 *
	 * Description:  Extends the DBTable class and defines a
	 *	table student in the attendencetracker db.
	 *	The constructor sets the fields and gets the class'
	 *	values.
	 *
	 ****************************************************/
	class Student extends DBTable
	{
		/*********************************************
		 * Constructor
		 *
		 * Description: Sets the fields of table and stores
		 *	in inherited array.  Sets the field types.
		 *	Then uses function to get values from form or
		 *	db table.
		 *
		 * Input variables: db object to pass to parent constructor,
		 *	array that holds the values of fields
		 *
		 * Return type: n/a
		 *********************************************/
		function __construct( $db, $elements, $id='NULL' )
		{
			// first call the parent constructor passing the name of the table for this child class
			parent::__construct($db, 'student');
			
			// call initialize_fields to initialize the student fields
			$this->initialize_fields();
			
			// call initialize_field_types to initialize the student field types
			$this->initialize_field_types();			
						
			/**
			   Check for $id, if NULL then get values from form
			   If not NULL then get values from db row corresponding
			   to $id input.
			 **/
			if ( $id == 'NULL' ) // default, get values from form
				$this->get_values_from_form($elements);
			else
				$this->get_values_from_db($id);
		}
		
		/*********************************************
		 * function initialize_felds
		 *
		 * Description: Creates the fields array and initializes
		 *	the fields corresponding to db columns.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_fields ( )
		{
			$student_fields = array();
			
			$student_fields[0] = "ID";
			$student_fields[1] = "OtherChurch";
			$student_fields[2] = "PreviousChurch";
			$student_fields[3] = "SchoolName";
			$student_fields[4] = "GraduationDate";
			$student_fields[5] = "StartDate";
			$student_fields[6] = "LeaveDate";
			$student_fields[7] = "LeaveReason";
			
			$this->fields = $student_fields;
		}
		
		/*********************************************
		 * function initialize_feld_types
		 *
		 * Description: Creates the field_types array and initializes
		 *	the field types corresponding to fields.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_field_types ( )
		{
			$fields_type = array();
			
			$fields_type[0] = "i";
			$fields_type[1] = "s";
			$fields_type[2] = "s";
			$fields_type[3] = "s";
			$fields_type[4] = "s";
			$fields_type[5] = "s";
			$fields_type[6] = "s";
			$fields_type[7] = "s";
			
			$this->field_type = $fields_type;
		}
		
		/*********************************************
		 * function is_valid
		 *
		 * Description: Overridden abstract function from parent
		 *	that describes which data must be valid and is called
		 *	before submitting any data to a db.  No fields are 
		 *	required and no special checking needed so set valid_values
		 *	to true and return.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		// overridden abstract function
		public function is_valid ( )
		{
			// Since all fields in Student can be NULL this $valid_values
			// is true; no checking required.
			$this->valid_values = true;
			
			return $this->valid_values;	
		}
		
		/*********************************************
		 * function exists
		 *
		 * Description: Overridden abstract function from parent
		 *	that checks the db to make sure that the current
		 *	object does not already exist in the table.  
		 *	Student is dependent on attendee table and therefore
		 *	will already be verified by attendee table's exists 
		 *	function.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function exists ( )
		{
			return false;   // return false as default
		}
		
		/*********************************************
		 * function set_id_value
		 *
		 * Description: Sets the value of id in first 
		 *	element of values array to ID of student.
		 *	If $id is not an int return false, else true.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function set_id_value ( $id )
		{
			if ( is_int($id) )
			{
				$this->values[0] = $id;
				return true;
			}
			else 
				return false;
		}
	}
	
	
	/*****************************************************
	 * Class Sponsor
	 *
	 * Description:  Extends the DBTable class and defines a
	 *	table sponsor in the attendencetracker db.
	 *	The constructor sets the fields and gets the class'
	 *	values.
	 *
	 ****************************************************/
	class Sponsor extends DBTable
	{
		/*********************************************
		 * Constructor
		 *
		 * Description: Sets the fields of table and stores
		 *	in inherited array.  Sets the field types.
		 *	Then uses function to get values from form or
		 *	db table.
		 *
		 * Input variables: db object to pass to parent constructor,
		 *	array that holds the values of fields
		 *
		 * Return type: n/a
		 *********************************************/
		function __construct( $db, $elements, $id='NULL' )
		{
			// first call the parent constructor passing the name of the table for this child class
			parent::__construct($db, 'student');
			
			// call initialize_fields to initialize the sponsor fields
			$this->initialize_fields();
			
			// call initialize_field_types to initialize the sponsor field types
			$this->initialize_field_types();			
						
			/**
			   Check for $id, if NULL then get values from form
			   If not NULL then get values from db row corresponding
			   to $id input.
			 **/
			if ( $id == 'NULL' ) // default, get values from form
				$this->get_values_from_form($elements);
			else
				$this->get_values_from_db($id);
		}
		
		/*********************************************
		 * function initialize_felds
		 *
		 * Description: Creates the fields array and initializes
		 *	the fields corresponding to db columns.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_fields ( )
		{
			$sponsor_fields = array();
			
			$sponsor_fields[0] = "ID";
			$sponsor_fields[1] = "BackgroundCheck";
			$sponsor_fields[2] = "VanEligible";
			$sponsor_fields[3] = "MaritalStatus";
			$sponsor_fields[4] = "Anniversary";
			
			$this->fields = $sponsor_fields;
		}
		
		/*********************************************
		 * function initialize_field_types
		 *
		 * Description: Creates the field_types array and initializes
		 *	the field types corresponding to fields.
		 *
		 * Input variables: n/a
		 *
		 * Return type: n/a
		 *********************************************/
		public function initialize_field_types ( )
		{
			$fields_type = array();
			
			$fields_type[0] = "i";
			$fields_type[1] = "s";
			$fields_type[2] = "s";
			$fields_type[3] = "s";
			$fields_type[4] = "s";
			
			$this->field_type = $fields_type;
		}
		
		/*********************************************
		 * function is_valid
		 *
		 * Description: Overridden abstract function from parent
		 *	that describes which data must be valid and is called
		 *	before submitting any data to a db.  No fields are 
		 *	required and no special checking needed so set valid_values
		 *	to true and return.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		// overridden abstract function
		public function is_valid ( )
		{
			// Sponsor requires no values to be checked
			// as the values are enum and will be populated
			// by default if not set.  No empty values or
			// out-of-range checks to perform
			$this->valid_values = true;	

			return $this->valid_values;
		}
		
		/*********************************************
		 * function exists
		 *
		 * Description: Overridden abstract function from parent
		 *	that checks the db to make sure that the current
		 *	object does not already exist in the table.  
		 *	Sponsor is dependent on attendee table and therefore
		 *	will already be verified by attendee table's exists 
		 *	function.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function exists ( )
		{
			return false;   // return false as default
		}
		
		/*********************************************
		 * function set_id_value
		 *
		 * Description: Sets the value of id in first 
		 *	element of values array to ID of sponsor.
		 *	If $id is not an int return false, else true.
		 *
		 * Input variables: n/a
		 *
		 * Return type: bool
		 *********************************************/
		public function set_id_value ( $id )
		{
			if ( is_int($id) )
			{
				$this->values[0] = $id;
				return true;
			}
			else 
				return false;
		}
	}
?>
