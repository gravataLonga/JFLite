#SQLite Management
  

## Who to use?
  
    require "JFLite.php";
    
    $lite = new gravataLonga/JFLite();
    
    $lite->open('myConnection'); // This will create database if not exists.
    
    $qry = $lite->query("SELECT * FROM gestbook"); // Query a table


## Methods

Method `open` will try to open database if can't, will attemp created. Return connection resource.

    open ( $dbname )
  
Method `query` 1 string of argument, and return resource. 

    query ( $sql )


Method `create_table` 3 arguments. Name of Table, Second table field and third set to true to create table if exists

    create_table( $name_of_table, $fields, $exist_if_exist = FALSE )


## Propriedades
  

* * *

###LICENSE

(c) Copyright 2012 Jonathan Fontes.

Licensed under the MIT license:

    http://www.opensource.org/licenses/mit-license.php

Permission is hereby granted, free of charge, to any person obtaining a copy  
of this software and associated documentation files (the "Software"), to deal  
in the Software without restriction, including without limitation the rights  
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell   
copies of the Software, and to permit persons to whom the Software is furnished  
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all  
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,  
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR  
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE  
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR   
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER   
DEALINGS IN THE SOFTWARE.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/gravataLonga/jflite/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

