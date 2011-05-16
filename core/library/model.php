<?php
/*
| ---------------------------------------------------------------
| Class: Model()
| ---------------------------------------------------------------
|
| This is the Base model class. Doesnt do anything other then
| load the loader so the Database's can be loaded upon request.
|
*/

class Model
{
	function __construct() 
	{
		$this->load = new Loader;
	}
}
