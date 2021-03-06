<?php
	/************************************
	 * Class DBTable
	 *
	 * Description:  An abstract class that defines a 
	 * 	class for handling data from a table.  It provides
	 *	member variables to hold structure and data about
	 *	and from the table it corresponds to and provides member
	 *	functions to get, manipulate, clean, and perform database
	 *	operations on data retrieved from a form or a db table.
	 *
	 ************************************/
	abstract class DBTable 
	{
		/************************************
		 * Private member variables
		 ************************************/
		protected $fields;                // array to hold column names of table corresponding to form elements
		protected $field_type;            // array to hold data type of corresponding fields to use in binding parameters 
		protected $values;                // array to hold the values corresponding to the fields of the table
		protected $valid_values = false;  // bool to test if all required values are correct 
		protected $table_name;            // the name of the db table the data corresponds to 
		protected $error_message;	  // an errors that occur are stored in this string
		protected $db;			  // an object reference to a database object
		protected $id;                    // the ID, or index, of the object in the Database
		
		/**************************************
		 * Constructor
		 *
		 * Description:  Takes two arguments to set the 
		 *	db object reference and the name of the table
		 *	the object corresponds to in the db.
		 *
		 * Input variables:  db object, string name of table
		 *
		 * Return type:  n/a
		 **************************************/
		function __construct ( $db, $name ) 
		{
			// assign db object reference and table name to member variables
			$this->db = $db;
			$this->table_name = $name;
		}
		
		/**************************************
		 * function initialize_fields    ***ABSTRACT***
		 *
		 * Description:  Abstract function that requires
		 *	inheriting classes to define a function that
		 *	initializes the fields of the parent class
		 *	upon instantiating parent and child.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  n/a
		 **************************************/
		protected abstract function initialize_fields ( );
		
		/**************************************
		 * function initialize_field_types    ***ABSTRACT***
		 *
		 * Description:  Abstract function that requires
		 *	inheriting classes to define a function that
		 *	initializes the field types of the parent class
		 *	upon instantiating parent and child.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  n/a
		 **************************************/
		protected abstract function initialize_field_types ( );
		
		/**************************************
		 * function get_field
		 *
		 * Description:  Returns the field corresponding
		 *	to the integer input.  Provides bounds checking.
		 *
		 * Input variables:  int
		 *
		 * Return type:  string (the field)
		 **************************************/
		public function get_field ( $counter )
		{
			// check that counter is not out-of-bounds:  less than 0
			// or more than the number of fields 
			if ( $counter < 0 || $counter >= count($this->fields) )
				return NULL;  // if out-of-bounds, return NULL
			
			// return the field
			return $this->fields[$counter];
		}
		
		/**************************************
		 * function get_field_type
		 *
		 * Description:  Returns the field type corresponding
		 *	to the integer input.  Provides bounds checking.
		 *
		 * Input variables:  int
		 *
		 * Return type:  string (the field type)
		 **************************************/
		public function get_field_type ( $counter )
		{
			// check that counter is not out-of-bounds: less than 0
			// or more than the number of fields
			if ( $counter < 0 || $counter >= count($this->field_type) )
				return NULL;  // return NULL if out-of-bounds
			
			// return the field type
			return $this->field_type[$counter];
		}
		
		/**************************************
		 * function get_values_from_form
		 *
		 * Description:  Takes an array ($_POST or $_GET)
		 * 	and checks for data corresponding to the fields
		 *	of this objects table columns.  It performs 
		 *	escaping on the data and stores the cleaned
		 *	data in the values array.  It also sets any missing
		 *	fields or empty fields to NULL and sets checked checkboxes
		 *	from "on" to "yes" for inserting into db.
		 *
		 * Input variables:  array
		 *
		 * Return type:  n/a
		 **************************************/
		public function get_values_from_form ( $elements ) 
		{		
			// create array to hold the cleaned and checked extracted data
			$arr = array();
		
			// loop over $elements, cleaning and assigning
			// each form element to the new array
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				// first, test if element exists, then check if it is empty
				if ( !isset($elements[$this->fields[$i]]) && $this->fields[$i] == 'Facebook' )  //**First check for the checkboxes that if not set need to be 'no' instead of NULL
					$elements[$this->fields[$i]] = 'no';
				else if ( !isset($elements[$this->fields[$i]]) && $this->fields[$i] == 'CanReceiveTxt' )
					$elements[$this->fields[$i]] = 'no';
				else if ( !isset($elements[$this->fields[$i]]) && $this->fields[$i] == 'ReleaseForm' )
					$elements[$this->fields[$i]] = 'no';
				else if ( !isset($elements[$this->fields[$i]]) && $this->fields[$i] == 'Myspace' )
					$elements[$this->fields[$i]] = 'no';
				else if ( !isset($elements[$this->fields[$i]]) )
					$elements[$this->fields[$i]] = 'NULL';     // create element if not set and set to NULL
				else if ( $elements[$this->fields[$i]] == "" )     // if element is empty
					$elements[$this->fields[$i]] = 'NULL';     // set to NULL
				else if ( $elements[$this->fields[$i]] == "on" )   // A selected checkbox is "on"
					$elements[$this->fields[$i]] = "yes";      // set to "yes" instead
				else				
					// use real_escape_string to escape the form element
					$elements[$this->fields[$i]] = $this->db->real_escape_string(trim($elements[$this->fields[$i]]));
					
				// assign the new checked and escaped value to arr
				$arr[$i] = $elements[$this->fields[$i]];
			}
			
			// set values to new array
			$this->values = $arr;
		}
		
		/**************************************
		 * function get_values_from_db
		 *
		 * Description:  Takes an ID and gets the values
		 *	for values array from the table row 
		 *	corresponding to the input ID.
		 *
		 * Input variables:  ID
		 *
		 * Return type:  n/a
		 **************************************/
		public function get_values_from_db ( $id ) 
		{
			$val = "";
			
			// First, set the ID field of the object
			$this->set_id($id);
			
			/**
			   Create a query and query the db getting row from table with ID of $id.
			   Loop over the values and set values array corresponding to fields.
			 **/
			$query = "SELECT * FROM " . $this->table_name . " WHERE ID = " . $id . ";" ;    // construct the query
    	  		if ( $result = $this->db->query($query) )  // query the db and get the result object (mysqli_result)
    	  		{  
    	  			// get the row
    	  			while ( $data = $result->fetch_object() )   // get row as object
    	  			{  
    	  				// loop over fields and use fields as column names to get values from result object
    	  				for ( $i = 0; $i < count($this->fields); $i++ ) 
    	  				{
    	  					$val = $this->fields[$i];
    	  					$this->values[$i] = $data->$val;
    	  				}
    	  			}
    	  		}
		}
		
		/**************************************
		 * function set_id
		 *
		 * Description:  Setter for id field.
		 *
		 * Input variables:  int
		 *
		 * Return type:  n/a
		 **************************************/
		public function set_id ( $value )
		{
			// validate input and set id
			if ( $value < 0 ) 
				$value = 0;
			
			$this->id = $value;
		}
		
		/**************************************
		 * function get_id
		 *
		 * Description:  Getter for id field.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  int
		 **************************************/
		public function get_id ( )
		{
			// if id is not set return NULL, else the id field
			if ( !isset($this->id) )
				return NULL;
			
			return $this->id;  
		}
		
		/**************************************
		 * function get_value
		 *
		 * Description:  Returns the values corresponding
		 *	to the input int.
		 *
		 * Input variables:  int
		 *
		 * Return type:  string (the value)
		 **************************************/
		public function get_value ( $counter )
		{
			// first, check that counter is not out-of-bounds: less than 0
			// and more than the number of values in the table
			if ( $counter < 0 || $counter >= count($this->values) )
				return NULL;  // return NULL if out-of-bounds
			
			// return the value
			return $this->values[$counter];
		}
		
		/**************************************
		 * function get_value_by_field
		 *
		 * Description:  Returns the value corresponding
		 *	to the input field name.
		 *
		 * Input variables:  string (field name)
		 *
		 * Return type:  string (the value)
		 **************************************/
		public function get_value_by_field ( $field_name )
		{
			// use get_index_by_field_name to get the index of 
			// the field represented by $field_name or -1 if not a valid field
			$index = $this->get_index_by_field_name($field_name);
			
			// check that field is valid and then return value corresponding to
			// the index in the values array
			if ( $index != -1 )   // if $index is not -1 (index not found)
				return $this->values[$index];
			else                  // if not valid, 
				return NULL;  // return NULL
		}
		
		/**************************************
		 * function retrieve_value
		 *
		 * Description:  Returns the value corresponding
		 *	to the input field name and checks for NULL.
		 *	If NULL, returns empty string.
		 *
		 * Input variables:  string (field name)
		 *
		 * Return type:  string (the value)
		 **************************************/
		public function retrieve_value( $field_name )
		{
			// use get_index_by_field_name to get the index of 
			// the field represented by $field_name or -1 if not a valid field
			$index = $this->get_index_by_field_name($field_name);
			
			// check that field is valid and then return value corresponding to
			// the index in the values array; if NULL then return empty string
			if ( $index != -1 )   // if $index is not -1 (index not found)
			{
				if ( $this->values[$index] == 'NULL' ) 
					return "";
				else
				{
					// return the value and use htmlentities to make sure safe for html content
					return htmlentities(stripslashes($this->values[$index]));
				}
			}
			else                  // if not valid, 
				return "";  // return empty string
		}
		
		/**************************************
		 * function get_index_by_field_name
		 *
		 * Description:  Returns a field's index in the fields
		 *	array by iterating over fields and returning the
		 *	first match.
		 *
		 * Input variables:  string (field name)
		 *
		 * Return type:  int (the field's index)
		 **************************************/
		private function get_index_by_field_name ( $field_name )
		{
			// loop over fields looking for a match to $field_name
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				// if there is a match, return the index
				if ( $this->fields[$i] == $field_name ) 
					return $i;
			}
			
			// If no match found this return -1
			return -1;
		}
		
		/**************************************
		 * function is_valid    ***ABSTRACT***
		 *
		 * Description:  Abstract function that requires
		 *	inheriting classes to define how data for the table
		 *	the child class represents is considered valid.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  n/a
		 **************************************/
		protected abstract function is_valid ( );
		
		/**************************************
		 * function exists    ***ABSTRACT***
		 *
		 * Description:  Abstract function that requires
		 *	inheriting classes to define how it will 
		 *	check if it already exists in the table.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  n/a
		 **************************************/
		protected abstract function exists ( );
		
		/**************************************
		 * function insert_sql_query
		 *
		 * Description:  Constructs an insert SQL query
		 *	based on the fields and values stored
		 *	in private member variables.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  string (the query)
		 **************************************/
		function insert_sql_query ( ) 
		{
			// Create variable to hold query and start the statement
			$query = "INSERT INTO $this->table_name (";
			
		 	// Add columns
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				if ( $i == count($this->fields) - 1 )                       // last column
					$query .= "'" . $this->fields[$i] . "') VALUES (";  // end columns and prepare for values
				else
					$query .= "'" . $this->fields[$i] . "', ";          // insert , between each column
			}
			
			// Add values
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				if ( $i == count($this->fields) - 1 )               // if last value
					$query .= "'" . $this->values[$i] . "');";  // end statement
				else
					$query .= "'" . $this->values[$i] . "', ";  // insert , between each column
			}
			
			// return the query string
			return $query;
		}
		
		/**************************************
		 * function insert_sql_query_bound
		 *
		 * Description:  Constructs a SQL query based
		 *	on the fields and values of the class.
		 *	This query is a prepared query so values are
		 *	represented by ?.  Returns the query as a string.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  string (the query)
		 **************************************/
		function insert_sql_query_bound ( ) 
		{
			// Create variable to hold query and start the statement
			$query = "INSERT INTO $this->table_name (";
			
		 	// Add columns
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				if ( $i == count($this->fields) - 1 )                // last column
					$query .= $this->fields[$i] . ") VALUES (";  // end columns and prepare for values
				else
					$query .= $this->fields[$i] . ", ";          // insert , between each column
			}
			
			// Add value markers '?' to statement
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				if ( $i == count($this->fields) - 1 )  // last value
					$query .= "?);";               // end statement
				else
					$query .= "?, ";               // insert , between each ?
			}
			
			// return the query string
			return $query;
		}
		
		/**************************************
		 * function create_bound_query
		 *
		 * Description:  Returns a string representation
		 *	of a bind_param function executed on a Mysqli_stmt
		 *	object.  This function constructs the statment
		 *	based on the fields and values of the class.  Removes
		 *	the need to have to manually code the bind_param statement
		 *	thus reducing typing errors.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  string (bind_param statement as a string ready for eval())
		 **************************************/
		function create_bound_query ( )
		{			
			// define $bound_statment and add the first part of the statement
			$bound_statement='$stmt->bind_param(\'';
			
			/**  FIRST, create first parameter to bind_param, types, and add to query **/
			$types = "";  // to hold the first parameter to bind_param, the field types:  Ex:  'ssss' (4 strings)
			// loop over field types adding to $types string
			foreach ( $this->field_type as $val)   
				$types .= $val; // build $types string from values in $field_types
			
			// add types to bound_statement
			$bound_statement .= $types . '\', ';
			
			/** 
			     NEXT, Add the field values to the bound statement by adding '$this->values[0]' ect to query 
				Cannot add the actual values, has to be a variable that holds the value.                
			 **/
			for ( $i = 0; $i < count($this->fields); $i++ )    // iterate the number of times as fields and values
			{			
				// first check for last item to finish the statement differently 
				if ( $i == count($this->fields) - 1 )   // last item
					$bound_statement .= '$this->values[' . $i . "]);";  // add value variable and end statement
				else 
					$bound_statement .= '$this->values[' . $i . "], ";  // add value variable and a , in between each
			}
			
			// return the bound statement query
			return $bound_statement;
		}
		
		/**************************************
		 * function execute_bound_query
		 *
		 * Description:  Checks that values are valid for inserting
		 *	and checks that this object has not already been added to 
		 *	the table by using FirstName, LastName, and DOB as unique
		 *	Secondary key, or Natural Key.  If valid and unique, execute
		 *	the bound query that was created using insert_sql_query_bound
		 *	and create_bound_query.  Write errors to $error_message with error()
		 *	and return true or false for success or failure.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  bool
		 **************************************/
		public function execute_bound_query ( )
		{
			// First, check that values are valid by is_valid, 
			// Then, check that there is not already a duplicate record in the table
			// with the same FirstName, LastName, and DOB with exists()
			if ( !$this->is_valid() )   // not valid
				return false;       // so return false and exit function
			
			if ( $this->exists() )      // already exists
				return false;       // so return false and exit
					
			// get the query string from insert_sql_query_bound
			$query = $this->insert_sql_query_bound();
			
			// get the statement object from prepare function of mysqli
			// pass the query created above to prepare; use db in parent that
			// references the db object to query against
			if ( !$stmt = $this->db->prepare($query) )
			{
				// set the error alerting that query was unsuccessful
				$this->error('Alert', 'Query was unsucessful; Please try again.');
				return false;   // if preparing the query failed return false
			}
			
			// get the bound->param statement from create_bound_query and then
			// use eval to execute it.
			$bound_stmt = $this->create_bound_query();
			eval($bound_stmt);
			
			// execute the statement returning true if sucessful; use if statement
			// to be able to set the error message if query fails
			if ( $this->is_valid() )
				return $stmt->execute();
			else 
			{
				// set the error alerting that query was unsuccessful
				$this->error('Alert', 'Query was unsucessful; Please try again.');
				return false;
			}
		}
		
		/*******************************************
		 * function get_error_message
		 *
		 * Description:  Getter for $error_message; simply returns $error_message variable.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  string (the error message)
		 ******************************************/
		public function get_error_message ( )
		{
			return $this->error_message;
		}
		
		/*******************************************
		 * function error
		 *
		 * Description:  Setter for error message that actually
		 *	just concatenates error strings to $error_message.
		 *
		 * Input variables:  string (field name or descriptive tag like 'Alert'), string (concise error description)
		 *
		 * Return type:  n/a
		 ******************************************/
		protected function error ( $field, $message )
		{
			// First, check if error message is empty, if so add
			// a ; (the delineator) after the first string added
			if ( $this->error_message == "" ) 
				$this->error_message .= $field . ":" . $message . ";";
			else  // if not empty, add the new field and message delinated by a :
				$this->error_message .= $field . ":" . $message;
		}
		
		/*******************************************
		 * function print_values
		 *
		 * Description:  Loops over values and prints each value on a 
		 *	line with the field as the label.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  n/a
		 ******************************************/
		public function print_values ( )
		{
			// loop over fields and print fields and values
			for ( $i = 0; $i < count($this->fields); $i++ )
				printf("%s:  %s<br />", $this->fields[$i], $this->values[$i]);  
		}
		
		/*******************************************
		 * function compare_to_update
		 *
		 * Description:  Loops over values and compares
		 *	with input object.  Creates an update query
		 *	and performs an update against the input db.
		 *	Return true or false on success or failure.
		 *
		 * Input variables:  database object, Attendee object
		 *
		 * Return type:  bool
		 ******************************************/
		public function compare_to_update ( $db, $attendee )
		{
			// variable to hold true or false for success or failure of update query
			$success = false;
			
			// associative array to be used in comparison
			$changes = array();
			
			// First, compare all the values of the two Attendee objects
			// and store in an associative array with the field name and new
			// value stored as key/value pairs
			for ( $i = 0; $i < count($this->fields); $i++ )
			{
				if ( $this->values[$i] != $attendee->get_value($i) )
				{
					$changes[$this->fields[$i]] = $attendee->get_value($i);	
				}
			}
					
			// Check if there were any changes and if so call update()
			if ( count($changes) > 0 )
				$success = $this->update($db, $changes);
			else  // if no changes set success to true since it succeeded there were just no changes to make
				$success = true;
		
			return $success;   
		}
		
		/*******************************************
		 * function update
		 *
		 * Description:  Loops over keys and values of 
		 *	input array and creates an UPDATE query
		 *	and executes it, then updates objects values
		 *	with those new changes.
		 *
		 * Input variables:  database object, Attendee object
		 *
		 * Return type:  bool (success or failure of query)
		 ******************************************/
		private function update ( $db, $changes )
		{
			// start query
			$query = 'UPDATE ' . $this->table_name . ' SET ';
			
			// get keys as an array
			$keys = array_keys($changes);
			
			// loop over keys and values of changes and build query
			// print comma before any value that is not the first
			$counter = 0;  // to keep track of when first loop occurs
			foreach ( $keys as $val )
			{
				if ( $counter == 0 )
					$query .= $val . '="' . $changes[$val] . '"';
				else
					$query .= ', ' . $val . '="' . $changes[$val] . '"';
					
				$counter++;  // increment the counter
			}
			
			// finish the query
			$query .= ' WHERE ID=' . $this->get_id() . ';';

			// execute the query 
			$success = $db->query($query);  
			
			// update the Attendee object if success
			if ( $success )
			{
				foreach ( $keys as $val )
					$this->values[$this->get_index_by_field_name($val)] = $changes[$val];
			}
			else
			{
				printf('Error:  %s<br /><hr />', $db->error);
			}
			
			// return success bool
			return $success;
		}
	}
?>
