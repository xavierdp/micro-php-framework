<?php

/*
Test class for testing
*/

class c_Zest
{
  /* zest of lemon */
  static public function deCitron($a_in)
  {
    h(__FUNCTION__);
    pr($a_in);

    return [
      "foo" => "bar",
    ];
  }

  static public function rawHTML($a_in)
  {
    h(__FUNCTION__);
    pr($a_in);

    return "<b>Hello</b>";
  }
  
}