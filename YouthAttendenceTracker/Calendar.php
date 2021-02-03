<?php
/*******************************************************************
 * Calendar Class
 * 
 * Description:  Used to build an html calendar.  Styling is handled by
 * 		css given general style tags used by Calendar class, except width
 * 		values that need to be set by inline styling.
 *
 * 		**NOTE -- MUST OVERRIDE THIS ABSTRACT METHOD
 * 		** write_day.  This is the template:
 * 		** abstract protected function write_day ( $pos, $width, $height, $class_name );
 *******************************************************************/
abstract class Calendar {
		/* Private Variables */
		private $month;                        // holds the value of the Calendar's month
		private $year;                         // holds the value of the Calendar's year
		private $day_counter = 1;              // used to count how many days have been written
		private $weekday_counter = 1;          // used to count how many days of a week have been written
		private $first_day_of_month;           // Numeric value of what day of week first day of month falls on (Sunday = 1...Saturday=7)
		private $last_day_of_month;            // Numeric value of what day of week last day of the month falls (Calculated by getting previous month's first day of month)
		private $num_days;                     // Number of days in the month
		private $num_weeks;                    // Number of weeks
		private $first_day_timestamp;          // timestamp for first day of the month
		private $last_day_timestamp;           // timestamp for last day of the month
		private $day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		private $day_names_short = array('Sun', 'Mon', 'Tues', 'Wed', 'Thur', 'Frid', 'Sat');
		private $month_names = array("January" => 1, "February" => 2, "March" => 3, "April" => 4, "May" => 5, "June" => 6, "July" => 7, "August" => 8, "September" => 9, "October" => 10, "November" => 11, "December" => 12); 
		private $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'); 
		private $is_admin = false;             // This variable is a flag to determine whether to write edit event to day or open song list
		private $user_id;                      // To hold the user id for writing openSongList to day content
		private $class_name = "dayContent";  // class name to use to write day content to:  defaults to dayContent
		private $column_widths = array('Sunday' => 128, 'Monday' => 128, 'Tuesday' => 128, 'Wednesday' => 128, 'Thursday' => 128, 'Friday' => 128, 'Saturday' => 128 ); // column widths are used for setting width of day cells in calendar (default asumes 900 width of calendar)
		private $header_title;       // the header title for the calendar, default "Month Year"   *SET IN CONSTRUCTOR
                private $previous_nav = false; // for previous button on calendar
                private $next_nav = false;       // for next button on calendar
		
		
		/***
		 * CELL TEMPLATE: to use in formatting cells by using supplied cell template, default is div with %p for position of cell
		 * and %i for the day marker
		 ***/
		private $cell_template = '<div class="cell %l" style="position: absolute; left: %ppx; width: %wpx; height: %hpx;"><div id="day">%i</div><span class="%c" id="%i"></span></div>';                
		private $custom_attributes = array();  // to use with custom replacement attributes set in set_cell_template and used in apply_cell_template
		
		/***
		 * DB stuff to use with getting day content.
		 ***/
		private $db;    // db object reference
		private $table; // table to use in db to retrieve events from by date
		
		/************************************
		 * Constuctor
		 * 
		 * Description:  
		 ************************************/
		function __construct( $month, $year ) {
			// set private variables for month and year
			$this->month = $month;
			$this->year = $year;
			
			// Calculate variable for use in getting dates for first day of month
			$this->first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
			
			/* Calculate first day of the month with date and mktime */
			$first_day = date('N', $this->first_day_timestamp );
			// Adjust first day of month since 'N' returns Monday as 1...Sunday 7
			$this->first_day_of_month = $first_day + 1;  
			if ( $this->first_day_of_month == 8 )
				$this->first_day_of_month = 1;
			
			/* Calucate the number of days in month with date and mktime */
			$this->num_days = date('t', $this->first_day_timestamp );  // 't' in date returns the number of days

			// Calculate variable for use in getting dates at last day of the month
			$this->last_day_timestamp = mktime(0, 0, 0, $month, $this->num_days, $year);
			
			/* Calculate the last day of the month with date and mktime and num_days */
			$last_day = date('N', $this->last_day_timestamp );
			// Adjust last day of month since 'N' returns Monday as 1...Sunday 7
			$this->last_day_of_month = $last_day + 1;  
			if ( $this->last_day_of_month == 8 )
				$this->last_day_of_month = 1;
			
			/* 
			 * Calculate the number of weeks with date 'W' using first and last day of the month
			 * Use strtotime to quickly convert first and last day of month into timestamp
			 */
			// When setting firstWeek, make sure to test for January since firstWeek will always be 1 in January
			// **firstWeek is getting set to 52 in January and breaking January on Calendar
			if ( $this->month == 1 )
				$firstWeek = 1;				
			else 
				$firstWeek = date('W', $this->first_day_timestamp );
			// UPDATE -- FIX BUG FOR DECEMBER BREAKING -- LastWeek is getting set to 1 in December
			//   *Set to 53 if 1
			$lastWeek = date('W', $this->last_day_timestamp );
			if ( $lastWeek == 1 )
				$lastWeek = 53;
			/** UPDATE -- FIX BUG FOR NUM WEEKS -- Months can have more weeks
			 *    than expected ... Actually bug is Calendar not lining up with PHP
			*     value returned.  Weeks start on Monday so value of W is acurate but
			*     I am starting on Sunday in Calendar.  Last day may occur on Sunday
			*     and start a new row in table but not return a next week in PHP so I
			*     lose that row... Check if last day is 7, if so increment last week.
			*     ****ALSO NEED TO ADJUST FIRSTWEEK IF First day is a 7...it would be
			*     ****considered in previous week by PHP but not this Calendar.
			**************************************************************************/
			if ( date('N', $this->last_day_timestamp) == 7 )
				$lastWeek++;
			if ( date('N', $this->first_day_timestamp) == 7 )
				$firstWeek++;
			$this->num_weeks = ($lastWeek - $firstWeek) + 1;
    
			// set the default header_title -- defaults to "Month Year"
			$this->header_title = $this->months[$month-1]. ' ' . $year;
		}
		
		function printVariables () {
			printf('First day:  %d<br />', $this->first_day_of_month);
			printf('Last day:  %d<br />', $this->last_day_of_month);
			printf('Num days:  %d<br />', $this->num_days);
			printf('Num weeks:  %d<br />', $this->num_weeks);
		}
		
		/***************************************************************
		 * functin print_html
		 * 
		 * Description:  Prints the Calendar in HTML.  Takes a width and 
		 * 		height also takes an optional header height which defaults to 20px. This function
		 * 		prints a Calendar with the specified height and width with
		 * 		each row having a "row" class and each cell having a "cell"
		 * 		class for styling.  Note:  the header height must also be
		 * 		set with CSS and it will have the class "header".  **Set calendar
		 * 		default width to 900 and height to 600.
		 * 
		 * Input variables:  int (width), int (height), int (header height) - optional
		 * 
		 * Return type:  n/a
		 ***************************************************************/
		function print_html ( $width = 900, $height = 600, $header = 40 ) {
			//*** UPDATE -- NEED TO CHECK FOR MIN WIDTH AND HEIGHT??  ***//
			
			/* First, calculate variables for cell heights and width. */
			$head = $header + 20;  // head is the total header plus day header ('Sunday...Saturday')
			$row_height = ($height-$head) / $this->num_weeks;  // auto-sized for height
			$cell_width = $width / 7;                                               // auto-sized for width
			// set the days width (the column width of the days) by setting the values of column_widths
			foreach ( $this->column_widths as $cw ) {
				$cw = $cell_width;
			}
			unset($cw);  // unset the cw value since it points at last value of column_widths
			
			// Now, start the Calendar
			printf('<div id="calendar" style="width: %dpx; height: %dpx;">', $width, $height);
			
			// Get variables to use in previous and next month links
			// -->Check the months and years to make sure not going into new year, if so adjust accordingly
			if ( $this->month == 1 ) { // current month is January so set next to 12 and set deincrement year
				$previous_month = 12;
				$previous_year = $this->year - 1;
			} else {
				$previous_month = $this->month - 1;
				$previous_year = $this->year;
			}
			if ( $this->month == 12 ) {
				$next_month = 1;
				$next_year = $this->year + 1;
			} else { 
				$next_month = $this->month + 1;
				$next_year = $this->year;
			}
			
			// variable to use to set the pos from left of each cell	
			$pos = 0;
			
			// Output the header section -- Month and previous/next navigation plus Day headers
                        // set a custom navigation button for previous or next if set
                        $header_nav = '<div id="header">';   // start the header
                        if ( $this->previous_nav )
                            $header_nav .= $this->previous_nav;
                        else  // not so set to default
                            $header_nav .= '<a href="%s?month=%s&year=%s" class="">&lt;&lt;</a>';
                            
                        $header_nav .= '&nbsp;&nbsp;%s&nbsp;&nbsp;';  // the title section
                        
                        if ( $this->next_nav )
                            $header_nav .= $this->next_nav;
                        else  // not, so set the next nav default
                            $header_nav .= '<a href="%s?month=%s&year=%s" class="">&gt;&gt;</a>';
                            
                        // finish the nav and print, must print variables according to which parts are being used
                        $header_nav .= '</div>';
                        if ( !$this->previous_nav && !$this->next_nav ) // default
                            printf($header_nav, $_SERVER['PHP_SELF'], $previous_month, $previous_year, $this->get_header_title(), $_SERVER['PHP_SELF'], $next_month, $next_year );
                        else if ( $this->previous_nav && !$this->next_nav )  // only previous set to custom
                            printf($header_nav, $this->get_header_title(), $_SERVER['PHP_SELF'], $next_month, $next_year );
                        else  // only next nav set to custom
                            printf($header_nav, $_SERVER['PHP_SELF'], $previous_month, $previous_year, $this->get_header_title());

			printf('<div id="dayHeader" style="position: absolute; top: %dpx;">', $header);
			for ( $i = 0; $i < 7; $i++ ) {
				printf('<div class="cell %s" style="position: absolute; left: %dpx; width: %dpx; height: %dpx;"><span class="">%s</span></div>', $this->day_names[$i], $pos, $this->column_widths[$this->day_names[$i]], 20, $this->day_names_short[$i] );
				$pos += $this->column_widths[$this->day_names[$i]];
			}
			echo '</div>';
			
			// reset pos
			$pos = 0;
			
			/***
			 *  Ouput the Calendar:
			 * 1. First, output the first row writing the blanks first.
			 * 2. Output the rest of the Calendar.
			 * -->As each cell is written, call write_day_content to 
			 * output any data retrieved from the DB.
			 * -->Abstract all of the day writing functionality into 
			 * the function write_day
			 ***/
			
			$pos = 0;            // position of cell from the left
			$blank_counter = 1;  // to keep track of how many blank cells to output
			
			// WEEK 1, write the blanks and then start writing Day cells
			printf('<div id="row" class="row1" style="position: absolute; left: 0px; height: %dpx; top: %dpx;">', $row_height, $head); // start the row
			for ( $i = 0; $i < 7; $i++ ) {
				if ( $blank_counter != $this->first_day_of_month ) {
					// for blank cells, don't pass the day name
					echo $this->apply_cell_template($pos, $this->column_widths[$this->day_names[$i]], $row_height);  // output the return from function with pos passed
					$blank_counter++;  // increment the blank counter
				} else {  // if no more blanks, start writing day cells
					// use prepare_day to get the day and day content: prepare_day returns the day; echo the return
					echo $this->prepare_day($pos, $this->column_widths[$this->day_names[$i]], $row_height);
					
				}
				$pos += $this->column_widths[$this->day_names[$i]];      // increment the position counter
			}
			echo '</div>';  // end the row
								
			// Finish the Calendar; write days until day counter equals number of days then finish the days with blanks
			for ( $j = 1; $j < $this->num_weeks; $j++ ) {
				printf('<div id="row" class="row%d" style="position: absolute; left: 0px; top: %dpx;">', $j+1, ($head + ($j*$row_height)));  // start the row
				
				/* Reset row variables */
				$pos = 0;
				for ( $i = 0; $i < 7; $i++ ) {
					// first check if all days have been written
					if ( $this->day_counter > $this->num_days ) {
						echo $this->apply_cell_template($pos, $this->column_widths[$this->day_names[$i]], $row_height);  // write a blank cell
					} else {
						// use prepare_day to get the day and day content: prepare_day returns the day; echo the return
						echo $this->prepare_day($pos, $this->column_widths[$this->day_names[$i]], $row_height);
					}
					$pos += $this->column_widths[$this->day_names[$i]];   // increment the position
				}
					
				echo '</div>';  // end the row
			}
			
			echo '</div>';  // end the Calendar
		}
                
                /***************************************************************
		 * functin print_month_event_list 
		 * 
		 * Description:  Prints the Calendar in a list of days with events
		 *      of those days listed.  Takes an array of strings containing
		 *      the days which events are to be printed.
		 * 
		 * Input variables:  array(days of month as string)
		 * 
		 * Return type:  n/a
		 ***************************************************************/
		function print_month_event_list ( $day_of_month ) {
                    /***
                     * Printing methodology:
                     *
                     * 1. Output a containing div with id "cal-events" to help
                     *     compartmentalize output -- calling code just needs
                     *     to style the cal-events id and cal-event-listing class
                     *     under that id to get a custom look.
                     *     
                     * 2. Output each day of month as a div element with a
                     *     class of "cal-event-listing" and the day as a class.
                     ***/
                    
                    // variables containing id and class stings to use in output
                    $container_id = "cal-events";
                    $day_listing_class = "cal-event-listing";
                    
                    // start the containing div
                    printf('<div id="%s">', $container_id);
                    
                    /***
                     * Functionality
                     *
                     * 1. Loop over the Calendar days
                     * 2. At each matching day to "day_of_month" items
                     *     query the events and user tables to get the event information.
                     * 3. Print the data into a div with the above print metodology.
                     ***/
                    
                    // (1) Loop over Calendar days
                    while ( $this->day_counter <= $this->num_days ) {  // loop until day_counter is greater than num_days
                        // check for matching day, if match query events and user tables and output day
                         if ( array_search($this->get_day_of_month(), $day_of_month) !== false )  {
                            ///???USE WRITE_DAY??? AND THEN JUST ADJUST CELL_TEMPLATE ON CALLING PAGE....???///
                         }
                    }
                }
		
		/* Extra functions to add */
		function set_cell_template ( $c_template, $custom = null ) {
			// sets the private cell template variable to use in formatting cells
			$this->cell_template = $c_template;
			
			// check for custom attributes and set if not null
			if ( $custom ) { 
				// split by first delimeter '|'
				$attrs = explode('|', $custom);
				
				// iterate and split each attribute pair setting custom_attributes array
				foreach ( $attrs as $a ) {
					$pair = explode(':', $a);
					$this->custom_attributes[] = array($pair[0], $pair[1]);
				}
			}
		}
		
		function get_cell_template ( ) {
			return $this->cell_template;
		}
		
		/*  Work in progress.  ?? How to input the variables to supply...need preset, how to input?? */
		function apply_cell_template ( $pos, $width, $height, $day="" ) {
			// takes a series of input variables to use in substituting
			// in the cell template
			$cell = $this->cell_template;
			$cell = str_replace('%p', $pos, $cell);      // supply the position
			$cell = str_replace('%w', $width, $cell);   // supply the width
			$cell = str_replace('%h', $height, $cell);  // supply the height
			$cell = str_replace('%i', $day, $cell);       // supply the day
			
			$cell = str_replace('%c', $this->class_name, $cell);       // supply the class_name from private member variable
			
			// optional replacements--Always check for %m for month and %y for year, also %x for id,
			//   and %l for textual representation of day
			$cell = str_replace('%m', $this->month, $cell);                               // supply the month
                        $cell = str_replace('%F', $this->months[$this->month - 1], $cell); // supply the month as text
			$cell = str_replace('%y', $this->year, $cell);                                   // supply the year
			$cell = str_replace('%l', $this->get_day_of_month(), $cell);           // supply the day as text
			
			// Check for custom attributes stored in array custom_attributes
			if ( count($this->custom_attributes) != 0 ) { // there are some custom attributes
				foreach ( $this->custom_attributes as $ca ) {
					$cell = str_replace($ca[0], $ca[1], $cell);
				}
			}
			
			return $cell; 
		}
		
		/***************************************************************
		 * function prepare_day
		 * 
		 * Description:  This function takes care of generic functionality
		 * 	that needs to be done every time write_day is called but allows
		 * 	this functionality to not have to be done in the write_day
		 * 	overridden function.  To make the class more user friendly
		 * 	and easily implemented.
		 *
		 * Input type:  string (class name), string (content to write), string (cell to write content to)
		 *
		 * Return type:  none
		 ***************************************************************/
		private function prepare_day ( $pos, $width, $height ) {
			// Get new day after applying input variables
			$day = $this->apply_cell_template($pos, $width, $height, $this->day_counter);
			
			// Get the day content (custom content to insert into day template) from write_day
			$day_content = $this->write_day();
			
			// Get the new complete day by passing day and day_content to write_day content
			$day = $this->write_day_content($this->class_name, $day_content, $day);
			
			// increment the day counter
			$this->day_counter++;
			
			return $day;
		}
		
		/***************************************************************
		 * function write_day
		 * 
		 * Description:  The main driver for writing the day's content.  This is
		 * 		called from Calendar's print_html function to write the day
		 * 		content.  THIS IS ABSTRACT.  This functionality will need to
		 * 		be overridden by each extending class and will need to use
		 * 		the input variables and output a day as html, generally doing
		 * 		so by using write_day_content to insert into the day
		 * 		template.
		 * 
		 * Input type:  string (the day to write new content to), string (class name), string (content to write), string (cell to write content to)
		 * 
		 * Return type:  string (the altered cell)
		 ***************************************************************/
		abstract protected function write_day ( );
		
		/***************************************************************
		 * function write_day_content
		 * 
		 * Description:  Takes a class name and a string of content 
		 * 		and writes the content string in the cell inside the element 
		 * 		references by class name.
		 * 
		 * Input type:  string (class name), string (content to write), string (cell to write content to)
		 * 
		 * Return type:  string (the altered cell)
		 ***************************************************************/
		function write_day_content ( $class_name, $content, $cell = null ) {
			/***
			 * Functionatlity:
			 * 
			 * 1. Use id_name to find the HTML element by searching for
			 * 	  'id=$id_name'.  Record location and then find the next
			 * 	  occurrence of '>'.  Record location and use this to 
			 *    insert content.
			 * 2. Write the content at position found in 1.
			 * 3. Return cell.
			 ***/
			
			/* IF CELL IS NULL, USE CELL_TEMPLATE, ELSE USE INPUT CELL */
			if ( !$cell ) 
				$cell = $this->cell_template;
			
			// (1)  Find position to write content
			$cl = 'class="' . $class_name . '"';              // id to search for
			$pos = strpos($cell, $cl);        // return position of id in cell
			$pos = strpos($cell, '>', $pos);  // now search from pos to find the next '>' and record back in pos 
		
			// (2)  Write the content
			$new_cell = substr($cell, 0, $pos+1);  // go to pos+1 to include the '>' character
			$new_cell .= $content;
			$new_cell .= substr($cell, $pos+1);
			
			// (3)  Return cell
			return $new_cell;
		}
		
		
		
		
		function set_db ( $db, $table_name ) {
			$this->db = $db;
			$this->table = $table_name;
		}
		
		// just returns the full textual representation of the day of the month based on current value of day counter
		public function get_day_of_month ( ) {
			return date('l', mktime(0, 0, 0, $this->month, $this->day_counter, $this->year) );
		}
                
                // returns the date in YYYY-MM-DD format as a string
                public function get_current_date ( ) {
                    // need to check month and day and change to DD/MM format if a single digit
                    $month = $this->month;
                    if ( $month < 10 )
                        $month = "0" . $month;
                        
                    $day = $this->day_counter;
                    if ( $day < 10 )
                        $day = "0" . $day;
                    
                    return $this->year . '-' . $month . '-' . $day;
                }
		
		// set_priviledges -- sets the is_admin variable to use for checking administrative priveledges
		public function set_priviledges ( $set ) {
			$this->is_admin = $set;
		}
		
		// set_user -- sets the user_id variable for use in writing day content
		public function set_user ( $set ) {
			$this->user_id = $set;
		}
		
		// setter for class_name
		public function set_class_name ( $set ) {
			$this->class_name = $set;
		}
		
		// setter for column_widths, or rather the days width
		// pass the values of whichever days to change as a string pair
		// separated by '|'.  Pass all the day values in an array as string pairs
		public function set_column_widths ( $values ) {
			foreach ( $values as $val ) {
				// separate the string value pairs
				$pair = explode('|', $val);
				
				// set the column_width value with the pair values
				$this->column_widths[$pair[0]] = $pair[1];
			}
		}
		
		// getter for header_title
		public function get_header_title () {
			return $this->header_title;
		}
		
		// setter for header_title
		// **The second optional parameter allows user to
		// specify that the default header should be used
		// with the first input text.  If this is specifiied the
		// third parameter is used to set before/after.
		// Defaults to not use default header, and after.
		public function set_header_title ( $new_title, $use_default = false, $before = true) {
			if ( $use_default ) {
				if ( $before ) {
					$this->header_title = $new_title . ' ' . $this->header_title;
				} else {
					$this->header_title = $this->header_title . ' ' . $new_title;
				}
			} else {
				$this->header_title = $new_title;
			}
		}
                
                // setter for changing the previous and/or next navigation buttons
                // can set one or both
                public function set_navigation_buttons ( $prev = false, $next = false ) {
                    if ( $prev ) 
                        $this->previous_nav = $prev;
                        
                    if ( $next ) 
                        $this->next_nav = $next;
                }
                
                // getter for Calendar's month variable
                public function get_month () {
                    return $this->month;
                }
                
                // getter for Calendar's day_counter variable
                public function get_current_day () {
                    return $this->day_counter;
                }
}

?>
