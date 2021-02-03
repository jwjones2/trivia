<?php
	/***********************************************************************
	 * Class ListView
	 *
	 * Description:  A list view control that allows for easily getting data
	 *	from a DB table and outputing the data formatted according to a 
	 *	template.
	 *
	 ***********************************************************************/
	class ListView 
	{
		/***************************************************************
		 * Private member variables
		 ***************************************************************/
		private $error;            // to hold any errors that may occur
		private $err = false;      // boolean value, default to false, that is true if there is an error 
		private $db;               // the database the table to use is in
		private $table_name;       // the name of the table to perform selection on 
		private $query_columns;    // the select query against the table
		private $template;         // the template string to use in output
		private $results = array();// holds the query results
		private $additional_clauses;// holds any additional sql syntax to add to the query
		
		/***************************************************************
		 * Constructor
		 *
		 * Description:  Takes four (or five) arguments that set the db object reference
		 *	variable, the table name, the query columns, and the template.  Optionally
		 *	set additional_clauses variable.
		 *
		 * Input variables:  db object, table name (string), query columns (string), template (string)
		 *
		 * Return type:  n/a
		 ***************************************************************/
		function __construct ( $db, $table, $columns, $template, $add_clauses = NULL ) 
		{
			// assign parameters to private variables
			$this->db = $db;
			$this->table_name = $table;
			$this->query_columns = $columns;
			$this->template = $template;
			$this->additional_clauses = $add_clauses; 
			
			// call get_results to query the db and populate the results array
			$this->get_results();  
		}
		
		/***************************************************************
		 * member function get_results
		 * 
		 * Description:  Queries the db and collects the resulting rows
		 *	and columns into results as a multidementional array.
		 *	Query columns must be parsed first and the query string built.
		 *	Query columns are parsed by parse_query_columns method.
		 *	
		 * Input variables:  n/a
		 *
		 * Return variables:  n/a
		 ***************************************************************/
		private function get_results ( )
		{
			// first get the columns by splitting query_string, 
			// to use when getting columns from object returned from query result
			$cols = explode('|', $this->query_columns);  // FUTURE TODO UPDATE -- INSTEAD PASS AN ARRAY TO CONSTRUCTOR WITH COLUMNS
			
			// variable to hold the array from result to add to $results
			$row;
			
			// get the query from  parse_query_columns
			$query = $this->parse_query_columns($this->query_columns);
			
			/* Query the db */
			if ( $result = $this->db->query($query) )
			{
				// get an object from result and put the columns in results array
				while ( $obj = $result->fetch_object() ) 
				{
					// assign row to new array
					$row = array();  
										
					// loop over cols, getting the obj elements
					foreach ( $cols as $c ) 
						$row[] = $obj->$c;  // each column name will reference the value in object returned to obj
					
					// add the array to results
					$this->results[] = $row;
				}
			}
			else
			{
				$this->error = $this->db->error;
				// set the err boolean to indicate an error
				$this->err = true;
			}
		}
		
		/***************************************************************
		 * member function print_results
		 * 
		 * Description:  Prints the formatted output according to the template.
		 *	
		 * Input variables:  n/a
		 *
		 * Return variables:  n/a
		 ***************************************************************/
		public function print_results ( )
		{
			foreach ( $this->results as $row ) 
				vprintf($this->template, $row);
		}
		
		/***************************************************************
		 * member function return_results
		 * 
		 * Description:  Same as print_results except it returns rather
		 * 	than outputs the results.  Uses vsprintf instead of vprintf.
		 *	
		 * Input variables:  n/a
		 *
		 * Return variables:  String (the results)
		 ***************************************************************/
		public function return_results ( )
		{
			$results = "";  // to store the results to return
			
			foreach ( $this->results as $row ) 
				$results .= vsprintf($this->template, $row);
				
			return $results;
		}
		
		/***************************************************************
		 * member function pop_results
		 * 
		 * Description:  Prints a row of $results by popping it off of 
		 *	front of array.  Ouput is formatted according to $template.
		 *	Returns NULL if no more rows to print.
		 *	
		 * Input variables:  n/a
		 *
		 * Return variables:  n/a
		 ***************************************************************/
		public function pop_results ( )
		{
			/***
			     Use array_shift to pop the first element of $results
			     and return it to print with vprintf using the template
			     for formatting; if $row is null return it, or print the
			     row and return true.
			 ***/
			$row = array_shift($this->results);
			
			if ( $row )  // $row is not null
			{
				vprintf($this->template, $row);  // print the row formatted
				return true;                     // return true for success
			}
			else
				return $row;                     // return row, which = NULL, for failure
		}
		
		/***************************************************************
		 * member function parse_query_columns
		 *
		 * Description:  Builds a query from a list (delimited by |) of 
		 * 	columns.
		 *
		 * Input variables:  query columns (string)
		 *
		 * Return variables:  query string (string)
		 ***************************************************************/
		private function parse_query_columns ( $query_columns )
		{
			// build the query string
			$query = 'SELECT ';   // start with select
			
			// split the columns
			$cols = explode('|', $query_columns); 
			
			$counter = true;  // boolean to see if the first column is being processed
			
			// loop over columns and build query string
			foreach ( $cols as $c )
			{
				if ( $counter ) // check if first element, counter is true
				{
					$query .= $c;     // if first element don't need a preceeding , 
					$counter = false; // set boolean to false so this code doesn't run again
				}
				else
				{
					$query .= ', ' . $c;  // add the proceeding ,
				}
			}
			
			// build the from part of query using table_name
			$query .= ' FROM ' . $this->table_name;
			
			// build the additional clauses if set
			if ( $this->additional_clauses ) 
			{				
				$query .= ' WHERE ' . $this->additional_clauses;
			}
			
			// finish the query
			$query .= ';';
						
			// return the query
			return $query;
		}
		
		/***************************************************************
		 * member function get_error
		 * 
		 * Description:  Returns the value of the error private field.
		 *	Getter for error.
		 *
		 * Input variables:  n/a
		 *
		 * Return variables:  error string
		 ***************************************************************/
		public function get_error ( )
		{
			return $this->error;	
		}
		
		/***************************************************************
		 * member function is_error
		 * 
		 * Description:  Returns the value of the err private field.
		 *	Getter for err.
		 *
		 * Input variables:  n/a
		 *
		 * Return variables:  err (boolean)
		 ***************************************************************/
		public function is_error ( )
		{
			return $this->err;
		}
	}
?>
