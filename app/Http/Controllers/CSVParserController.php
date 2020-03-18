<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CSVParserController extends Controller
{

    private $expected_columns = [];
    private $line_columns = [];
    private $required_values = [];
    private $line_split_regex = '/ and | & /';
    private $output = [];
    private $titles = [];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->expected_columns = [
                'homeowner'
            ];
            $this->line_columns = [
                'Title',
                'First Name',
                'Initial',
                'Last Name'
            ];
            $this->required_values = [
                'Title',
                'Last Name'
            ];
            return $next($request);
        });
    }

    public function index()
    {
        return view('index',[
            'output' => $this->output,
            'line_columns' => $this->line_columns,
        ]);
    }

    public function read_csv(Request $request){
        $file = $request->file('homeowner_csv');

        $data = array_map('str_getcsv', file($file));

        return $data;
    }

    /**
     * Parse the contents of a CSV to obtain address information.
     *
     * On success this will take the data from the CSV and loop through line by line generating the address as follows:
     *
     * Title (Required)
     * First Name
     * Initial
     * Last Name (Required)
     *
     * @param  Request $request
     * @return Response
     */
    public function parseCsv(Request $request)
    {

        $data = $this->read_csv($request);

        foreach ($data as $row) {
            foreach ($row as $column) {
                if($column){ // Filter out blanks
                    if(!in_array($column, $this->expected_columns)){ // Filter out headers if they exist (Which they do in the example)
                        // Explode the column string to convert it to an Array we can use later
                        $column_values = explode(' ', $column);
                        // Check if the column value contains at least the number of required values - if not discard it and move on
                        if(count($column_values) < count($this->required_values))
                            continue;
                        // Do a preg_split on the column value to determine if there is 1 or more people
                        $people = preg_split($this->line_split_regex, $column);
                        // Loop through the people to parse the data for each Person
                        foreach($people as $person){
                            // Explode the Person string to convert it to an Array we can use later
                            $values = explode(' ', $person);

                            // Create an array for each person's values
                            $lines = [];
                            foreach ($this->line_columns as $line_column) {
                                $lines[$line_column] = null;
                            }
                            // Title is required so the first item in any Person Value array is Title
                            $lines['Title'] = reset($values);
                            if( !in_array($lines['Title'], $this->titles))
                                array_push($this->titles, $lines['Title']);
                            // Now Title is resolved we can remove it to get just the name values
                            $name = preg_split("/". $lines['Title'] . "|\s/", $person, 0, PREG_SPLIT_NO_EMPTY);
                            if($name){
                                // Last Name is required so the last item in the name array is Last Name
                                $lines['Last Name'] = end($name);
                                // Remove Title and Last name to reveal First Name or initial
                                $first_name = preg_split("/". $lines['Title'] . " |" .$lines['Last Name'] . "/", $person, 0, PREG_SPLIT_NO_EMPTY);
                            } else {
                                $column_values = explode(' ', $column);
                                // Last Name is required so the last item in the column values array is Last Name
                                $lines['Last Name'] = end($column_values);
                                $titles_regex = "";
                                foreach($this->titles as $title){
                                    $titles_regex .= $title . " |";
                                }
                                // Remove any Titles and Last name to reveal First Name or initial
                                $first_name = preg_split('/' . $titles_regex . 'and |& |' . $lines['Last Name'] .'/', $column, 0, PREG_SPLIT_NO_EMPTY);
                            }
                            if($first_name){
                                // Check the pattern of the first name and determine if it is an initial or first name (Data suggests it's one or the other so that's how I've built it to parse)
                                if(preg_match('/[A-Z] | [A-Z]. /', $person)){
                                    $lines['Initial'] = implode($first_name);
                                } else {
                                    $lines['First Name'] = implode($first_name);
                                }
                            }
                            // Push the resulting line to the output
                            array_push($this->output, $lines);

                        }

                    }

                }

            }
        }

        // return the output partial to display the result in a table
        return view('partials.csv_output',[
            'output' => $this->output,
            'line_columns' => $this->line_columns,
        ]);

    }


}
